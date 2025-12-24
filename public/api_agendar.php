<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_auth();

header("Content-Type: application/json; charset=utf-8");

$tz = new DateTimeZone("America/Sao_Paulo");

$body = json_decode(file_get_contents("php://input"), true);
$date = $body["date"] ?? "";
$time = $body["time"] ?? "";
$service = trim($body["service"] ?? "Corte");

if (!$date || !$time) {
  echo json_encode(["ok"=>false,"message"=>"Data e horário obrigatórios."]); exit;
}

// Valida formato básico
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
  echo json_encode(["ok"=>false,"message"=>"Formato de data inválido."]); exit;
}
if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
  echo json_encode(["ok"=>false,"message"=>"Formato de horário inválido."]); exit;
}

// Não permitir datas passadas
$today = new DateTime("today", $tz);
$chosenDate = DateTime::createFromFormat("Y-m-d", $date, $tz);
if (!$chosenDate) {
  echo json_encode(["ok"=>false,"message"=>"Data inválida."]); exit;
}
if ($chosenDate < $today) {
  echo json_encode(["ok"=>false,"message"=>"Não é possível agendar em datas passadas."]); exit;
}

// Se for hoje, não permitir horário no passado (tolerância opcional de 0 min)
$now = new DateTime("now", $tz);
if ($chosenDate->format("Y-m-d") === $today->format("Y-m-d")) {
  $chosenDateTime = DateTime::createFromFormat("Y-m-d H:i", $date . " " . $time, $tz);
  if ($chosenDateTime && $chosenDateTime < $now) {
    echo json_encode(["ok"=>false,"message"=>"Não é possível agendar em um horário que já passou."]); exit;
  }
}

// Verifica se esse dia existe na disponibilidade do admin
$availStmt = $pdo->prepare("
  SELECT start_time, end_time, slot_minutes
  FROM availability
  WHERE available_date = ?
  ORDER BY start_time ASC
");
$availStmt->execute([$date]);
$windows = $availStmt->fetchAll();

if (!$windows) {
  echo json_encode(["ok"=>false,"message"=>"Essa data não está disponível para agendamento."]); exit;
}

// Verifica bloqueio (admin)
$blkStmt = $pdo->prepare("SELECT 1 FROM blocks WHERE block_date = ? AND block_time = ?");
$blkStmt->execute([$date, $time . ":00"]);
if ($blkStmt->fetchColumn()) {
  echo json_encode(["ok"=>false,"message"=>"Esse horário está bloqueado pelo administrador."]); exit;
}

// Verifica se o horário está dentro de alguma janela e respeita o step (slot_minutes)
$validSlot = false;
foreach ($windows as $w) {
  $start = DateTime::createFromFormat("Y-m-d H:i:s", $date . " " . $w["start_time"], $tz);
  $end   = DateTime::createFromFormat("Y-m-d H:i:s", $date . " " . $w["end_time"], $tz);
  $pick  = DateTime::createFromFormat("Y-m-d H:i", $date . " " . $time, $tz);

  if (!$start || !$end || !$pick) continue;

  // deve ser >= start e < end
  if ($pick < $start || $pick >= $end) continue;

  $step = (int)$w["slot_minutes"];
  $diffMin = (int)(($pick->getTimestamp() - $start->getTimestamp()) / 60);

  if ($step > 0 && $diffMin % $step === 0) {
    $validSlot = true;
    break;
  }
}

if (!$validSlot) {
  echo json_encode(["ok"=>false,"message"=>"Horário inválido para essa disponibilidade."]); exit;
}

// Inserção com trava de concorrência (UNIQUE)
try {
  $stmt = $pdo->prepare("
    INSERT INTO bookings (user_id, booking_date, booking_time, service, status)
    VALUES (?,?,?,?, 'active')
  ");
  $stmt->execute([$_SESSION["user"]["id"], $date, $time . ":00", $service]);

  echo json_encode(["ok"=>true]);
} catch (Exception $e) {
  echo json_encode(["ok"=>false,"message"=>"Esse horário já foi agendado. Atualize e escolha outro."]);
}
