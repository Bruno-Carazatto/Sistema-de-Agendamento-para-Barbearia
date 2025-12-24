<?php
require_once __DIR__ . "/../includes/auth.php";
require_auth();

$role = $_SESSION["user"]["role"];
if ($role === "admin") {
  header("Location: admin_disponibilidade.php");
} else {
  header("Location: user_agendar.php");
}
exit;
