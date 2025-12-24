<?php
// includes/auth.php
session_start();

function require_auth() {
  if (empty($_SESSION['user'])) {
    header("Location: index.php");
    exit;
  }
}

function require_role($role) {
  require_auth();
  if ($_SESSION['user']['role'] !== $role) {
    header("Location: dashboard.php");
    exit;
  }
}
