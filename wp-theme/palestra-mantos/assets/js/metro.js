/* Entrega no metrô/trem: linha → estação → data (calendário) → horário, na página do produto. */
(function () {
	'use strict';
	var box = document.querySelector('[data-pm-metro]');
	if (!box || !window.pmMetro) { return; }
	var D = window.pmMetro;
	var form = box.closest('form');
	var panel = box.querySelector('.pm-metro__panel');
	var linesBox = box.querySelector('.pm-metro__lines');
	var sel = box.querySelector('.pm-metro__station');
	var cal = box.querySelector('.pm-metro__cal');
	var slotsBox = box.querySelector('.pm-metro__slots');
	var sum = box.querySelector('.pm-metro__sum');
	var inp = {
		line: box.querySelector('[name="pm_metro_line"]'),
		station: box.querySelector('[name="pm_metro_station"]'),
		date: box.querySelector('[name="pm_metro_date"]'),
		slot: box.querySelector('[name="pm_metro_slot"]')
	};
	var available = {};
	D.dates.forEach(function (d) { available[d] = true; });
	var full = {};
	D.full.forEach(function (k) { full[k] = true; });
	var MONTHS = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
	var WK = ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'];
	var WKL = ['dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sáb'];

	function lineById(id) { for (var i = 0; i < D.lines.length; i++) { if (D.lines[i].id === id) { return D.lines[i]; } } return null; }
	function pad(n) { return (n < 10 ? '0' : '') + n; }
	function ymd(dt) { return dt.getFullYear() + '-' + pad(dt.getMonth() + 1) + '-' + pad(dt.getDate()); }
	function parse(s) { var p = s.split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); }
	function contrast(hex) {
		var c = hex.replace('#', ''); var r = parseInt(c.substr(0, 2), 16), g = parseInt(c.substr(2, 2), 16), b = parseInt(c.substr(4, 2), 16);
		return (r * 299 + g * 587 + b * 114) / 1000 > 160 ? '#111' : '#fff';
	}

	// ---- Modo de entrega ----
	function mode() { var r = box.querySelector('[name="pm_entrega"]:checked'); return r ? r.value : 'casa'; }
	function syncMode() {
		var m = mode() === 'metro';
		panel.hidden = !m;
		box.classList.toggle('is-metro', m);
		document.querySelectorAll('.pm-ship').forEach(function (f) { f.style.display = m ? 'none' : ''; });
	}
	box.querySelectorAll('[name="pm_entrega"]').forEach(function (r) { r.addEventListener('change', syncMode); });

	// ---- Linhas ----
	linesBox.innerHTML = D.lines.map(function (l) {
		return '<button type="button" class="pm-metro__line" data-id="' + l.id + '" aria-pressed="false" title="Linha ' + l.id + ' ' + l.name + '" style="--c:' + l.color + ';--t:' + contrast(l.color) + '"><i>' + l.id + '</i><span>' + l.name + '</span></button>';
	}).join('');
	function pickLine(id, keepStation) {
		var l = lineById(id); if (!l) { return; }
		inp.line.value = id;
		linesBox.querySelectorAll('.pm-metro__line').forEach(function (b) { b.setAttribute('aria-pressed', b.dataset.id === id ? 'true' : 'false'); });
		sel.disabled = false;
		sel.innerHTML = '<option value="">Escolha a estação</option>' + l.stations.map(function (s) { return '<option>' + s.replace(/</g, '&lt;') + '</option>'; }).join('');
		sel.style.setProperty('--c', l.color);
		if (keepStation && l.stations.indexOf(inp.station.value) > -1) { sel.value = inp.station.value; } else { inp.station.value = ''; }
		update();
	}
	linesBox.addEventListener('click', function (e) { var b = e.target.closest('.pm-metro__line'); if (b) { pickLine(b.dataset.id, false); } });
	sel.addEventListener('change', function () { inp.station.value = sel.value; update(); });

	// ---- Calendário ----
	var first = D.dates.length ? parse(D.dates[0]) : new Date();
	var last = D.dates.length ? parse(D.dates[D.dates.length - 1]) : new Date();
	var view = new Date(first.getFullYear(), first.getMonth(), 1);
	function drawCal() {
		var y = view.getFullYear(), m = view.getMonth();
		var start = new Date(y, m, 1).getDay(), days = new Date(y, m + 1, 0).getDate();
		var canPrev = new Date(y, m, 1) > new Date(first.getFullYear(), first.getMonth(), 1);
		var canNext = new Date(y, m + 1, 1) <= last;
		var h = '<div class="pm-cal__head"><button type="button" class="pm-cal__nav" data-nav="-1" aria-label="Mês anterior"' + (canPrev ? '' : ' disabled') + '>‹</button><b>' + MONTHS[m] + ' ' + y + '</b><button type="button" class="pm-cal__nav" data-nav="1" aria-label="Próximo mês"' + (canNext ? '' : ' disabled') + '>›</button></div>';
		h += '<div class="pm-cal__grid">' + WK.map(function (w) { return '<span class="pm-cal__wk">' + w + '</span>'; }).join('');
		for (var i = 0; i < start; i++) { h += '<span></span>'; }
		for (var d = 1; d <= days; d++) {
			var key = ymd(new Date(y, m, d));
			var ok = !!available[key];
			if (ok && D.slots.every(function (s) { return full[key + '|' + s]; })) { ok = false; }
			h += '<button type="button" class="pm-cal__day" data-d="' + key + '"' + (ok ? '' : ' disabled') + ' aria-pressed="' + (inp.date.value === key) + '" aria-label="' + d + ' de ' + MONTHS[m] + (ok ? '' : ', indisponível') + '">' + d + '</button>';
		}
		cal.innerHTML = h + '</div>';
	}
	cal.addEventListener('click', function (e) {
		var n = e.target.closest('[data-nav]');
		if (n && !n.disabled) { view = new Date(view.getFullYear(), view.getMonth() + (+n.dataset.nav), 1); drawCal(); return; }
		var b = e.target.closest('.pm-cal__day');
		if (b && !b.disabled) { inp.date.value = b.dataset.d; inp.slot.value = ''; drawCal(); drawSlots(); update(); }
	});

	// ---- Horários ----
	function drawSlots() {
		if (!inp.date.value) { slotsBox.innerHTML = '<p class="pm-metro__hint">Escolha a data primeiro.</p>'; return; }
		slotsBox.innerHTML = D.slots.map(function (s) {
			var f = !!full[inp.date.value + '|' + s];
			return '<button type="button" class="pm-metro__slot" data-s="' + s.replace(/"/g, '&quot;') + '" aria-pressed="' + (inp.slot.value === s) + '"' + (f ? ' disabled' : '') + '>' + s + (f ? ' <small>' + D.msgFull + '</small>' : '') + '</button>';
		}).join('');
	}
	slotsBox.addEventListener('click', function (e) {
		var b = e.target.closest('.pm-metro__slot');
		if (!b || b.disabled) { return; }
		inp.slot.value = b.dataset.s;
		slotsBox.querySelectorAll('.pm-metro__slot').forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
		update();
	});

	// ---- Resumo ----
	function complete() { return inp.line.value && inp.station.value && inp.date.value && inp.slot.value; }
	function update() {
		if (complete()) {
			var l = lineById(inp.line.value), d = parse(inp.date.value);
			sum.innerHTML = '<span class="pm-metro__dot" style="background:' + l.color + '"></span>' + D.msgSum.replace('%s', '<b>Estação ' + inp.station.value + '</b> (Linha ' + l.id + ' ' + l.name + ') · ' + WKL[d.getDay()] + ', ' + pad(d.getDate()) + '/' + pad(d.getMonth() + 1) + ' · ' + inp.slot.value);
			sum.classList.add('is-ok');
		} else { sum.innerHTML = ''; sum.classList.remove('is-ok'); }
		box.classList.remove('is-error');
	}

	// ---- Não deixa comprar com a retirada incompleta ----
	if (form) {
		form.addEventListener('submit', function (e) {
			if (mode() !== 'metro' || complete()) { return; }
			e.preventDefault(); e.stopImmediatePropagation();
			box.classList.add('is-error');
			sum.innerHTML = D.msgFill; sum.classList.remove('is-ok');
			box.scrollIntoView({ behavior: 'smooth', block: 'center' });
		}, true);
	}

	// ---- Estado inicial (pedido já agendado) ----
	if (inp.line.value) { pickLine(inp.line.value, true); }
	if (inp.date.value && !available[inp.date.value]) { inp.date.value = ''; inp.slot.value = ''; }
	if (inp.date.value) { view = new Date(parse(inp.date.value).getFullYear(), parse(inp.date.value).getMonth(), 1); }
	drawCal(); drawSlots(); update(); syncMode();
})();
