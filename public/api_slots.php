<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_auth();

header("Content-Type: application/json; charset=utf-8");

$date = $_GET["date"] ?? "";
if (!$date) {
  echo json_encode(["ok"=>false,"message"=>"Data obrigatória."]); exit;
}

/* ===== BLOQUEIO DE DATAS PASSADAS ===== */
$tz = new DateTimeZone("America/Sao_Paulo");
$today = new DateTime("today", $tz);
$chosen = DateTime::createFromFormat("Y-m-d", $date, $tz);

if ($chosen && $chosen < $today) {
  // Data passada → não retorna slots
  echo json_encode(["ok"=>true,"slots"=>[]]);
  exit;
}

$availStmt = $pdo->prepare("SELECT start_time,end_time,slot_minutes
                            FROM availability WHERE available_date = ?
                            ORDER BY start_time ASC");
$availStmt->execute([$date]);
$windows = $availStmt->fetchAll();

if (!$windows) {
  echo json_encode(["ok"=>true,"slots"=>[]]); exit;
}

/* Reservas ativas do dia */
$bookStmt = $pdo->prepare("SELECT booking_time FROM bookings
                           WHERE booking_date = ? AND status='active'");
$bookStmt->execute([$date]);
$booked = array_map(fn($r) => substr($r["booking_time"],0,5), $bookStmt->fetchAll());
$bookedSet = array_flip($booked);

/* Bloqueios do admin do dia */
$blkStmt = $pdo->prepare("SELECT block_time FROM blocks WHERE block_date = ?");
$blkStmt->execute([$date]);
$blocked = array_map(fn($r) => substr($r["block_time"],0,5), $blkStmt->fetchAll());
$blockedSet = array_flip($blocked);

$slots = [];

foreach ($windows as $w) {
  $start = new DateTime("$date " . $w["start_time"]);
  $end   = new DateTime("$date " . $w["end_time"]);
  $step  = (int)$w["slot_minutes"];

  while ($start < $end) {
    $t = $start->format("H:i");
    $isBooked = isset($bookedSet[$t]);
    $isBlocked = isset($blockedSet[$t]);

    $slots[] = [
      "time" => $t,
      "disabled" => ($isBooked || $isBlocked),
      "tag" => $isBooked ? "Reservado" : ($isBlocked ? "Bloqueado" : "")
    ];

    $start->modify("+{$step} minutes");
  }
}

echo json_encode(["ok"=>true,"slots"=>$slots]);
