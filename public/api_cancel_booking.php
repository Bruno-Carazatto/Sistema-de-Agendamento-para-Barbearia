<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_auth();

header("Content-Type: application/json; charset=utf-8");

$body = json_decode(file_get_contents("php://input"), true);
$id = (int)($body["id"] ?? 0);

if ($id <= 0) {
  echo json_encode(["ok"=>false,"message"=>"ID inválido."]); exit;
}

/* Só cancela se for do próprio usuário e estiver ativo */
$stmt = $pdo->prepare("
  UPDATE bookings
  SET status='cancelled', cancelled_at=NOW(), cancelled_by=?
  WHERE id=? AND user_id=? AND status='active'
");
$stmt->execute([$_SESSION["user"]["id"], $id, $_SESSION["user"]["id"]]);

if ($stmt->rowCount() === 0) {
  echo json_encode(["ok"=>false,"message"=>"Não foi possível cancelar."]); exit;
}

echo json_encode(["ok"=>true]);
