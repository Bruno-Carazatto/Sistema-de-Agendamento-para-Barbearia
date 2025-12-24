<?php
require_once __DIR__ . "/../config/db.php";
session_start();

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST["email"] ?? "");
  $pass  = $_POST["password"] ?? "";

  $stmt = $pdo->prepare("SELECT id,name,email,password_hash,role FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if ($user && password_verify($pass, $user["password_hash"])) {
    $_SESSION["user"] = [
      "id" => $user["id"],
      "name" => $user["name"],
      "email" => $user["email"],
      "role" => $user["role"]
    ];
    header("Location: dashboard.php");
    exit;
  } else {
    $msg = "Email ou senha inválidos.";
  }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <title>Barbearia • Login</title>
</head>
<body>
  <div class="container">
    <div class="nav">
      <div class="brand">💈 Barbearia <span class="badge">Agendamentos</span></div>
      <a class="btn" href="register.php">Criar conta</a>
    </div>

    <div class="grid">
      <div class="card">
        <h1>Entrar</h1>
        <p>Acesse para agendar seu horário.</p>

        <?php if ($msg): ?>
          <div class="alert error"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="post">
          <label>Email</label>
          <input type="email" name="email" required placeholder="seu-Email"/>

          <label>Senha</label>
          <input type="password" name="password" required placeholder="••••••••"/>

          <div style="margin-top:14px; display:flex; gap:10px;">
            <button class="btn btn-primary" type="submit">Entrar</button>
            <a class="btn" href="register.php">Cadastrar</a>
          </div>
        </form>
      </div>

      <div class="card">
        <h2>Como funciona</h2>
        <p>1) Faça login</p>
        <p>2) Escolha o dia e horário disponível</p>
        <p>3) Confirme o agendamento</p>
        <p style="margin-top:10px;color:var(--muted);font-size:13px;">
          Admin cria a agenda (dias/horários). Usuário agenda em 1 clique.
        </p>
      </div>
    </div>
  </div>
</body>
</html>
