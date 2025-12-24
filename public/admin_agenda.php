<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_role("admin");

$date = $_GET["date"] ?? date("Y-m-d");

$tz = new DateTimeZone("America/Sao_Paulo");
$now = new DateTime("now", $tz);

$isToday = ($date === $now->format("Y-m-d"));

// Totais do dia
$countStmt = $pdo->prepare("
  SELECT
    SUM(status='active')    AS active_count,
    SUM(status='cancelled') AS cancelled_count
  FROM bookings
  WHERE booking_date = ?
");
$countStmt->execute([$date]);
$counts = $countStmt->fetch() ?: ["active_count"=>0, "cancelled_count"=>0];

$activeCount = (int)($counts["active_count"] ?? 0);
$cancelledCount = (int)($counts["cancelled_count"] ?? 0);

// Próximo horário (somente se for hoje)
$nextTime = null;
if ($isToday) {
  $nextStmt = $pdo->prepare("
    SELECT booking_time
    FROM bookings
    WHERE booking_date = ? AND status='active' AND booking_time >= ?
    ORDER BY booking_time ASC
    LIMIT 1
  ");
  $nextStmt->execute([$date, $now->format("H:i:s")]);
  $row = $nextStmt->fetch();
  if ($row) $nextTime = substr($row["booking_time"], 0, 5);
}

/* lista agenda do dia */
$stmt = $pdo->prepare("
  SELECT b.id, b.booking_date, b.booking_time, b.service, b.status,
         u.name AS customer_name, u.email AS customer_email
  FROM bookings b
  JOIN users u ON u.id = b.user_id
  WHERE b.booking_date = ?
  ORDER BY b.booking_time ASC
");
$stmt->execute([$date]);
$bookings = $stmt->fetchAll();

/* bloqueios do dia */
$blk = $pdo->prepare("
  SELECT id, block_time, reason
  FROM blocks
  WHERE block_date = ?
  ORDER BY block_time ASC
");
$blk->execute([$date]);
$blocks = $blk->fetchAll();
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <link rel="stylesheet" href="../assets/css/style.css"/>
  <title>Admin • Agenda do Dia</title>
</head>
<body>
  <div class="container">
    <div class="nav">
      <div class="brand">💈 Admin <span class="badge">Agenda</span></div>
      <div style="display:flex; gap:10px;">
        <a class="btn" href="admin_disponibilidade.php">Disponibilidade</a>
        <a class="btn" href="logout.php">Sair</a>
      </div>
    </div>

    <div class="grid">
      <div class="card">
        <h1>Agenda do Dia</h1>
        <p>Veja reservas e cancele quando necessário.</p>
        
        <div class="card" style="box-shadow:none; margin: 12px 0;">
          <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <span class="badge" style="border-color: rgba(31,111,235,.45);">
              Hoje: <?= $isToday ? "Sim" : "Não" ?>
            </span>

            <span class="badge" style="border-color: rgba(31,111,235,.45);">
              Ativos: <?= (int)$activeCount ?>
            </span>

            <span class="badge" style="border-color: rgba(255,59,59,.45);">
              Cancelados: <?= (int)$cancelledCount ?>
            </span>

            <span class="badge">
              Próximo: <?= $isToday ? ($nextTime ? htmlspecialchars($nextTime) : "—") : "—" ?>
            </span>
          </div>

          <?php if ($isToday && !$nextTime && $activeCount > 0): ?>
            <p style="margin-top:10px;">Não há mais horários ativos para hoje.</p>
          <?php endif; ?>
        </div>

        <label>Dia</label>
        <input type="date" value="<?= htmlspecialchars($date) ?>" onchange="location.href='admin_agenda.php?date='+this.value">

        <div id="msg" class="alert" style="display:none;"></div>

        <h2 style="margin-top:14px;">Agendamentos</h2>

        <?php if(!$bookings): ?>
          <p>Nenhum agendamento nesse dia.</p>
        <?php else: ?>
          <div style="display:grid; gap:10px;">
            <?php foreach($bookings as $b): ?>
              <div class="card" style="box-shadow:none;">
                <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;">
                  <div>
                    <div style="font-weight:800;">
                      <span class="badge"><?= htmlspecialchars(substr($b["booking_time"],0,5)) ?></span>
                      <?= htmlspecialchars($b["customer_name"]) ?>
                      <?php if ($b["status"] === "cancelled"): ?>
                        <span class="badge" style="border-color: rgba(255,59,59,.45);">Cancelado</span>
                      <?php else: ?>
                        <span class="badge" style="border-color: rgba(31,111,235,.45);">Ativo</span>
                      <?php endif; ?>
                    </div>
                    <div style="font-size:13px;color:var(--muted);">
                      Serviço: <?= htmlspecialchars($b["service"]) ?> • <?= htmlspecialchars($b["customer_email"]) ?>
                    </div>
                  </div>

                  <?php if ($b["status"] === "active"): ?>
                    <button class="btn btn-danger" onclick="cancelar(<?= (int)$b['id'] ?>)">Cancelar</button>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="card">
        <h2>Bloquear horário</h2>
        <p>Bloqueios deixam o horário indisponível para agendamentos.</p>

        <label>Hora</label>
        <input id="block_time" type="time">

        <label>Motivo</label>
        <input id="block_reason" placeholder="Ex: almoço / manutenção / folga">

        <div style="margin-top:12px; display:flex; gap:10px;">
          <button class="btn btn-primary" onclick="bloquear()">Bloquear</button>
        </div>

        <h2 style="margin-top:16px;">Bloqueios do dia</h2>
        <?php if(!$blocks): ?>
          <p>Nenhum bloqueio.</p>
        <?php else: ?>
          <div style="display:grid; gap:10px;">
            <?php foreach($blocks as $x): ?>
              <div class="card" style="box-shadow:none;">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
                  <div>
                    <div style="font-weight:800;">
                      <span class="badge" style="border-color: rgba(255,59,59,.45);">Bloqueado</span>
                      <?= htmlspecialchars(substr($x["block_time"],0,5)) ?>
                    </div>
                    <div style="font-size:13px;color:var(--muted);"><?= htmlspecialchars($x["reason"]) ?></div>
                  </div>
                  <button class="btn btn-danger" onclick="desbloquear(<?= (int)$x['id'] ?>)">Remover</button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>

<script>
const DATE = "<?= htmlspecialchars($date) ?>";
const elMsg = document.querySelector("#msg");

function showMsg(text, type="") {
  elMsg.style.display = "block";
  elMsg.className = "alert " + (type === "error" ? "error" : type === "ok" ? "ok" : "");
  elMsg.textContent = text;
}

async function cancelar(id) {
  if (!confirm("Cancelar esse agendamento?")) return;

  const res = await fetch("api_admin_cancel.php", {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify({id})
  });
  const data = await res.json();
  if (!data.ok) return showMsg(data.message || "Erro.", "error");

  showMsg("Agendamento cancelado.", "ok");
  setTimeout(()=> location.reload(), 600);
}

async function bloquear() {
  const time = document.querySelector("#block_time").value;
  const reason = document.querySelector("#block_reason").value || "Horário indisponível";
  if (!time) return showMsg("Selecione um horário para bloquear.", "error");

  const res = await fetch("api_block.php", {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify({date: DATE, time, reason})
  });
  const data = await res.json();
  if (!data.ok) return showMsg(data.message || "Erro ao bloquear.", "error");

  showMsg("Horário bloqueado.", "ok");
  setTimeout(()=> location.reload(), 600);
}

async function desbloquear(id) {
  if (!confirm("Remover bloqueio?")) return;

  const res = await fetch("api_unblock.php", {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify({id})
  });
  const data = await res.json();
  if (!data.ok) return showMsg(data.message || "Erro ao remover.", "error");

  showMsg("Bloqueio removido.", "ok");
  setTimeout(()=> location.reload(), 600);
}
</script>
</body>
</html>
