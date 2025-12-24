<?php
require_once __DIR__ . "/../includes/auth.php";
require_auth();
if ($_SESSION["user"]["role"] === "admin") {
  header("Location: admin_disponibilidade.php");
  exit;
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <title>Usuário • Agendar</title>
</head>
<body>
  <div class="container">
    <div class="nav">
      <div class="brand">💈 Agendar <span class="badge"><?= htmlspecialchars($_SESSION["user"]["name"]) ?></span></div>
      <div style="display:flex; gap:10px;">
        <a class="btn" href="minhas_reservas.php">Minhas reservas</a>
        <a class="btn" href="logout.php">Sair</a>
      </div>
    </div>

    <div class="grid">
      <div class="card">
        <h1>Escolha o dia</h1>
        <p>Depois selecione um horário disponível.</p>

        <label>Dia disponível</label>
          <select id="dateSelect">
            <option value="">Carregando datas...</option>
          </select>

        <label style="margin-top:12px;">Serviço</label>
        <select id="service">
          <option value="Corte">Corte</option>
          <option value="Barba">Barba</option>
          <option value="Corte + Barba">Corte + Barba</option>
        </select>

        <div id="msg" class="alert" style="display:none;"></div>

        <div style="margin-top:14px;">
          <button class="btn btn-primary" id="btnLoad">Ver horários</button>
        </div>
      </div>

      <div class="card">
        <h2>Horários</h2>
        <p style="font-size:13px;">Toque para selecionar e confirmar.</p>

        <div id="slots" class="slot-grid"></div>

        <div style="margin-top:14px; display:flex; gap:10px;">
          <button class="btn btn-primary" id="btnBook" disabled>Confirmar agendamento</button>
        </div>
      </div>
    </div>
  </div>

  <script src="../assets/js/app.js"></script>
</body>
</html>
