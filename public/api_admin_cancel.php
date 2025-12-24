<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_role("admin");

header("Content-Type: application/json; charset=utf-8");

$body = json_decode(file_get_contents("php://input"), true);
$id = (int)($body["id"] ?? 0);

if ($id <= 0) {
  echo json_encode(["ok"=>false,"message"=>"ID inválido."]); exit;
}

$stmt = $pdo->prepare("
  UPDATE bookings
  SET status='cancelled', cancelled_at=NOW(), cancelled_by=?
  WHERE id=? AND status='active'
");
$stmt->execute([$_SESSION["user"]["id"], $id]);

if ($stmt->rowCount() === 0) {
  echo json_encode(["ok"=>false,"message"=>"Não foi possível cancelar."]); exit;
}

echo json_encode(["ok"=>true]);
