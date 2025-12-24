<?php
require_once __DIR__ . "/../config/db.php";
session_start();

$msg = ""; $ok = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name  = trim($_POST["name"] ?? "");
  $email = trim($_POST["email"] ?? "");
  $pass  = $_POST["password"] ?? "";

  if (strlen($pass) < 6) {
    $msg = "Senha precisa ter pelo menos 6 caracteres.";
  } else {
    try {
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?, 'user')");
      $stmt->execute([$name, $email, $hash]);
      $ok = "Conta criada! Agora faça login.";
    } catch (Exception $e) {
      $msg = "Não foi possível cadastrar. Email pode já existir.";
    }
  }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <title>Barbearia • Cadastro</title>
</head>
<body>
  <div class="container">
    <div class="nav">
      <div class="brand">💈 Barbearia <span class="badge">Cadastro</span></div>
      <a class="btn" href="index.php">Voltar</a>
    </div>

    <div class="card">
      <h1>Criar conta</h1>
      <p>Rápido e direto.</p>

      <?php if ($msg): ?><div class="alert error"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
      <?php if ($ok): ?><div class="alert ok"><?= htmlspecialchars($ok) ?></div><?php endif; ?>

      <form method="post">
        <div class="row">
          <div>
            <label>Nome</label>
            <input name="name" required placeholder="Seu nome"/>
          </div>
          <div>
            <label>Email</label>
            <input type="email" name="email" required placeholder="seuemail@gmail.com"/>
          </div>
        </div>

        <label>Senha</label>
        <input type="password" name="password" required placeholder="mínimo 6 caracteres"/>

        <div style="margin-top:14px; display:flex; gap:10px;">
          <button class="btn btn-primary" type="submit">Cadastrar</button>
          <a class="btn" href="index.php">Já tenho conta</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
