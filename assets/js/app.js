// assets/js/app.js
// Barbearia - Agendamento (Usuário)
// Atualizado: datas vindas do admin (select) + formato DD/MM/AAAA (+ dia da semana opcional)

let selectedTime = null;

const elDateSelect = document.querySelector("#dateSelect");
const elSlots = document.querySelector("#slots");
const elMsg = document.querySelector("#msg");
const elService = document.querySelector("#service");
const btnLoad = document.querySelector("#btnLoad");
const btnBook = document.querySelector("#btnBook");

/* ===== Helpers UI ===== */
function showMsg(text, type = "") {
  if (!elMsg) return;
  elMsg.style.display = "block";
  elMsg.className =
    "alert " + (type === "error" ? "error" : type === "ok" ? "ok" : "");
  elMsg.textContent = text;
}

function clearMsg() {
  if (!elMsg) return;
  elMsg.style.display = "none";
  elMsg.textContent = "";
}

/* ===== Formatação de data ===== */
// DD/MM/AAAA
function formatBR(dateStr) {
  // dateStr: YYYY-MM-DD
  const [year, month, day] = dateStr.split("-");
  return `${day}/${month}/${year}`;
}

// (Recomendado) Dia da semana + DD/MM/AAAA: "Sáb, 03/01/2026"
function formatBRFull(dateStr) {
  const [year, month, day] = dateStr.split("-");
  const date = new Date(Number(year), Number(month) - 1, Number(day));

  const weekdays = ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"];
  return `${weekdays[date.getDay()]}, ${day}/${month}/${year}`;
}

/* ===== Slots ===== */
function renderSlots(slots) {
  if (!elSlots) return;

  elSlots.innerHTML = "";
  selectedTime = null;
  if (btnBook) btnBook.disabled = true;

  if (!slots || !slots.length) {
    elSlots.innerHTML = `<p style="grid-column:1/-1;color:var(--muted);">Nenhum horário disponível.</p>`;
    return;
  }

  slots.forEach((s) => {
    const div = document.createElement("div");
    div.className = "slot" + (s.disabled ? " disabled" : "");
    div.textContent = s.time;

    // Se vier tag do backend (Reservado / Bloqueado), aparece no tooltip
    if (s.tag) div.title = s.tag;

    if (!s.disabled) {
      div.addEventListener("click", () => {
        document.querySelectorAll(".slot").forEach((x) => x.classList.remove("selected"));
        div.classList.add("selected");
        selectedTime = s.time;
        if (btnBook) btnBook.disabled = false;
        clearMsg();
      });
    }

    elSlots.appendChild(div);
  });
}

/* ===== Datas disponíveis (admin) ===== */
async function loadAvailableDates() {
  if (!elDateSelect) return;

  showMsg("Carregando datas...");
  elDateSelect.innerHTML = `<option value="">Carregando datas...</option>`;
  if (btnLoad) btnLoad.disabled = true;

  try {
    const res = await fetch("api_available_dates.php");
    const data = await res.json();

    if (!data.ok) {
      showMsg("Erro ao carregar datas.", "error");
      elDateSelect.innerHTML = `<option value="">Erro ao carregar</option>`;
      return;
    }

    elDateSelect.innerHTML = "";

    if (!data.dates || data.dates.length === 0) {
      elDateSelect.innerHTML = `<option value="">Nenhuma data liberada</option>`;
      showMsg("Nenhuma data disponível no momento. Volte mais tarde.", "error");
      if (btnLoad) btnLoad.disabled = true;
      return;
    }

    elDateSelect.innerHTML = `<option value="">Selecione uma data</option>`;

    data.dates.forEach((d) => {
      const opt = document.createElement("option");
      opt.value = d;                 // mantém YYYY-MM-DD pro backend
      opt.textContent = formatBRFull(d); // exibe "DiaSemana, DD/MM/AAAA"
      // se quiser só DD/MM/AAAA, troque para: formatBR(d)
      elDateSelect.appendChild(opt);
    });

    clearMsg();
    if (btnLoad) btnLoad.disabled = false;
  } catch (e) {
    showMsg("Falha de rede ao carregar datas.", "error");
    elDateSelect.innerHTML = `<option value="">Erro de conexão</option>`;
    if (btnLoad) btnLoad.disabled = true;
  }
}

/* ===== Eventos ===== */

// Carregar datas ao abrir a tela
if (elDateSelect) loadAvailableDates();

// (Opcional premium) ao selecionar uma data, já carrega os horários automaticamente
elDateSelect?.addEventListener("change", () => {
  if (elDateSelect.value && btnLoad) btnLoad.click();
});

btnLoad?.addEventListener("click", async () => {
  const date = elDateSelect?.value;

  if (!date) return showMsg("Selecione uma data disponível.", "error");

  if (btnBook) btnBook.disabled = true;
  showMsg("Carregando horários...");

  try {
    const res = await fetch(`api_slots.php?date=${encodeURIComponent(date)}`);
    const data = await res.json();

    if (!data.ok) return showMsg(data.message || "Erro ao carregar.", "error");

    clearMsg();
    renderSlots(data.slots);
  } catch (e) {
    showMsg("Falha de rede ao carregar horários.", "error");
  }
});

btnBook?.addEventListener("click", async () => {
  const date = elDateSelect?.value;
  const service = elService?.value || "Corte";

  if (!date || !selectedTime) return showMsg("Selecione data e horário.", "error");

  try {
    const res = await fetch("api_agendar.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ date, time: selectedTime, service }),
    });

    const data = await res.json();
    if (!data.ok) return showMsg(data.message || "Não foi possível agendar.", "error");

    showMsg("Agendado com sucesso! 🎉", "ok");
    if (btnBook) btnBook.disabled = true;

    // Recarrega horários pra atualizar (mostrar o horário recém-ocupado)
    if (btnLoad) btnLoad.click();
  } catch (e) {
    showMsg("Falha de rede ao agendar.", "error");
  }
});
