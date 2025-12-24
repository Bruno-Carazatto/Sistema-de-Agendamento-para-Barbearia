<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_auth();

header("Content-Type: application/json; charset=utf-8");

// pega só datas de hoje pra frente (evita passado)
$today = date("Y-m-d");

$stmt = $pdo->prepare("
  SELECT DISTINCT available_date
  FROM availability
  WHERE available_date >= ?
  ORDER BY available_date ASC
");
$stmt->execute([$today]);

$dates = array_map(fn($r) => $r["available_date"], $stmt->fetchAll());

echo json_encode(["ok" => true, "dates" => $dates]);
