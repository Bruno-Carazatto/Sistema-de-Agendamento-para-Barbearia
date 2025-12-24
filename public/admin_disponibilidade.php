<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_role("admin");

/* ===== Função de formatação de data ===== */
function formatDateBR($date) {
  // YYYY-MM-DD -> DD/MM/YYYY
  $d = DateTime::createFromFormat("Y-m-d", $date);
  return $d ? $d->format("d/m/Y") : $date;
}

$msg=""; $ok="";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $date = $_POST["date"] ?? "";
  $start = $_POST["start_time"] ?? "";
  $end = $_POST["end_time"] ?? "";
  $slot = (int)($_POST["slot_minutes"] ?? 30);

  if (!$date || !$start || !$end || $slot < 10) {
    $msg = "Preencha corretamente.";
  } else {
    $stmt = $pdo->prepare("INSERT INTO availability (available_date,start_time,end_time,slot_minutes,created_by)
                           VALUES (?,?,?,?,?)");
    $stmt->execute([$date,$start,$end,$slot,$_SESSION["user"]["id"]]);
    $ok = "Disponibilidade cadastrada!";
  }
}

$list = $pdo->query("SELECT id, available_date, start_time, end_time, slot_minutes
                     FROM availability ORDER BY available_date DESC, start_time DESC")->fetchAll();
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <title>Admin • Disponibilidade</title>
</head>
<body>
  <div class="container">
    <div class="nav">
      <div class="brand">💈 Admin <span class="badge"><?= htmlspecialchars($_SESSION["user"]["name"]) ?></span></div>
      <div style="display:flex; gap:10px;">
        <a class="btn" href="admin_agenda.php">Agenda do Dia</a>
        <a class="btn" href="logout.php">Sair</a>
      </div>
    </div>

    <div class="grid">
      <div class="card">
        <h1>Definir horários disponíveis</h1>
        <p>Crie janelas de atendimento (o sistema gera os slots).</p>

        <?php if ($msg): ?><div class="alert error"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <?php if ($ok): ?><div class="alert ok"><?= htmlspecialchars($ok) ?></div><?php endif; ?>

        <form method="post">
          <div class="row">
            <div>
              <label>Dia</label>
              <input type="date" name="date" required>
            </div>
            <div>
              <label>Duração do slot (min)</label>
              <input type="number" name="slot_minutes" value="30" min="10" step="5" required>
            </div>
          </div>

          <div class="row">
            <div>
              <label>Início</label>
              <input type="time" name="start_time" required>
            </div>
            <div>
              <label>Fim</label>
              <input type="time" name="end_time" required>
            </div>
          </div>

          <div style="margin-top:14px;">
            <button class="btn btn-primary" type="submit">Salvar</button>
          </div>
        </form>
      </div>

      <div class="card">
        <h2>Janelas cadastradas</h2>
        <p style="font-size:13px;">(Slots serão calculados automaticamente pro usuário.)</p>

        <?php if (!$list): ?>
          <p>Nenhuma disponibilidade cadastrada ainda.</p>
        <?php else: ?>
          <div style="display:grid; gap:10px;">
            <?php foreach($list as $a): ?>
              <div class="card" style="box-shadow:none;">
                <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;">
                  <div>
                    <div style="font-weight:700;">
                      <?= htmlspecialchars(formatDateBR($a["available_date"])) ?>
                      <span class="badge">
                        <?= htmlspecialchars(substr($a["start_time"],0,5)) ?>–<?= htmlspecialchars(substr($a["end_time"],0,5)) ?>
                      </span>
                    </div>
                    <div style="font-size:13px;color:var(--muted);">Slots: <?= (int)$a["slot_minutes"] ?> min</div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</body>
</html>
