<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_auth();

/* ===== Formatação de data (BR) ===== */
function formatDateBR($date) {
  // YYYY-MM-DD -> DD/MM/YYYY
  $d = DateTime::createFromFormat("Y-m-d", $date);
  return $d ? $d->format("d/m/Y") : $date;
}

$stmt = $pdo->prepare("
  SELECT id, booking_date, booking_time, service, status
  FROM bookings
  WHERE user_id = ?
  ORDER BY booking_date DESC, booking_time DESC
");
$stmt->execute([$_SESSION['user']['id']]);
$list = $stmt->fetchAll();
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <title>Usuário • Minhas reservas</title>
</head>
<body>
  <div class="container">
    <div class="nav">
      <div class="brand">💈 Minhas reservas</div>
      <div style="display:flex; gap:10px;">
        <a class="btn" href="user_agendar.php">Agendar</a>
        <a class="btn" href="logout.php">Sair</a>
      </div>
    </div>

    <div class="card">
      <h1>Seus agendamentos</h1>
      <p>Veja e cancele seus horários ativos.</p>

      <div id="msg" class="alert" style="display:none;"></div>

      <?php if (!$list): ?>
        <p>Nenhum agendamento encontrado.</p>
      <?php else: ?>
        <div style="display:grid; gap:10px;">
          <?php foreach ($list as $b): ?>
            <div class="card" style="box-shadow:none;">
              <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
                <div>
                  <div style="font-weight:700;">
                    <?= htmlspecialchars(formatDateBR($b['booking_date'])) ?>
                    <span class="badge"><?= htmlspecialchars(substr($b['booking_time'], 0, 5)) ?></span>

                    <?php if ($b['status'] === 'cancelled'): ?>
                      <span class="badge" style="border-color: rgba(255,59,59,.45);">Cancelado</span>
                    <?php else: ?>
                      <span class="badge" style="border-color: rgba(31,111,235,.45);">Ativo</span>
                    <?php endif; ?>
                  </div>

                  <div style="font-size:13px; color:var(--muted);">
                    Serviço: <?= htmlspecialchars($b['service']) ?>
                  </div>
                </div>

                <?php if ($b['status'] === 'active'): ?>
                  <button class="btn btn-danger" onclick="cancelar(<?= (int)$b['id'] ?>)">
                    Cancelar
                  </button>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

<script>
const elMsg = document.querySelector("#msg");

function showMsg(text, type="") {
  elMsg.style.display = "block";
  elMsg.className = "alert " + (type === "error" ? "error" : type === "ok" ? "ok" : "");
  elMsg.textContent = text;
}

async function cancelar(id) {
  if (!confirm("Tem certeza que deseja cancelar este agendamento?")) return;

  try {
    const res = await fetch("api_cancel_booking.php", {
      method: "POST",
      headers: {"Content-Type": "application/json"},
      body: JSON.stringify({ id })
    });

    const data = await res.json();

    if (!data.ok) {
      showMsg(data.message || "Erro ao cancelar.", "error");
      return;
    }

    showMsg("Agendamento cancelado com sucesso.", "ok");
    setTimeout(() => location.reload(), 700);

  } catch (e) {
    showMsg("Falha de conexão ao cancelar.", "error");
  }
}
</script>
</body>
</html>
