<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_role("admin");

header("Content-Type: application/json; charset=utf-8");

$body = json_decode(file_get_contents("php://input"), true);
$date = $body["date"] ?? "";
$time = $body["time"] ?? "";
$reason = trim($body["reason"] ?? "Horário indisponível");

if (!$date || !$time) {
  echo json_encode(["ok"=>false,"message"=>"Data e hora obrigatórias."]); exit;
}

try {
  $stmt = $pdo->prepare("INSERT INTO blocks (block_date, block_time, reason, created_by)
                         VALUES (?,?,?,?)");
  $stmt->execute([$date, $time . ":00", $reason, $_SESSION["user"]["id"]]);
  echo json_encode(["ok"=>true]);
} catch (Exception $e) {
  echo json_encode(["ok"=>false,"message"=>"Esse horário já está bloqueado."]);
}
