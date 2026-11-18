/* Retirada no metrô/trem: linha → estação → data → horário → nome/WhatsApp → reserva e abre o WhatsApp. */
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
	var nameIn = box.querySelector('[name="pm_metro_name"]');
	var phoneIn = box.querySelector('[name="pm_metro_phone"]');
	var btn = form ? form.querySelector('.single_add_to_cart_button') : null;
	var btnText = btn ? btn.innerHTML : '';
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
	var busy = false;

	function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
	function lineById(id) { for (var i = 0; i < D.lines.length; i++) { if (D.lines[i].id === id) { return D.lines[i]; } } return null; }
	function pad(n) { return (n < 10 ? '0' : '') + n; }
	function ymd(dt) { return dt.getFullYear() + '-' + pad(dt.getMonth() + 1) + '-' + pad(dt.getDate()); }
	function parse(s) { var p = s.split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); }
	function contrast(hex) {
		var c = hex.replace('#', ''); var r = parseInt(c.substr(0, 2), 16), g = parseInt(c.substr(2, 2), 16), b = parseInt(c.substr(4, 2), 16);
		return (r * 299 + g * 587 + b * 114) / 1000 > 160 ? '#111' : '#fff';
	}
	function phoneDigits() { var d = (phoneIn.value || '').replace(/\D+/g, ''); if (d.length === 13 && d.indexOf('55') === 0) { d = d.slice(2); } return d; }

	// ---- Modo de entrega ----
	function mode() { var r = box.querySelector('[name="pm_entrega"]:checked'); return r ? r.value : 'casa'; }
	function syncMode() {
		var m = mode() === 'metro';
		panel.hidden = !m;
		box.classList.toggle('is-metro', m);
		document.querySelectorAll('.pm-ship').forEach(function (f) { f.style.display = m ? 'none' : ''; });
		if (btn) { btn.innerHTML = m ? D.btn : btnText; btn.classList.toggle('pm-btn-wa', m); }
	}
	box.querySelectorAll('[name="pm_entrega"]').forEach(function (r) { r.addEventListener('change', syncMode); });

	// ---- Linhas e estações ----
	linesBox.innerHTML = D.lines.map(function (l) {
		return '<button type="button" class="pm-metro__line" data-id="' + esc(l.id) + '" aria-pressed="false" title="Linha ' + esc(l.id + ' ' + l.name) + '" style="--c:' + l.color + ';--t:' + contrast(l.color) + '"><i>' + esc(l.id) + '</i><span>' + esc(l.name) + '</span></button>';
	}).join('');
	function pickLine(id) {
		var l = lineById(id); if (!l) { return; }
		inp.line.value = id; inp.station.value = '';
		linesBox.querySelectorAll('.pm-metro__line').forEach(function (b) { b.setAttribute('aria-pressed', b.dataset.id === id ? 'true' : 'false'); });
		sel.disabled = false;
		sel.innerHTML = '<option value="">Escolha a estação</option>' + l.stations.map(function (s) { return '<option>' + esc(s) + '</option>'; }).join('');
		sel.style.setProperty('--c', l.color);
		update();
	}
	linesBox.addEventListener('click', function (e) { var b = e.target.closest('.pm-metro__line'); if (b) { pickLine(b.dataset.id); } });
	sel.addEventListener('change', function () { inp.station.value = sel.value; update(); });

	// ---- Calendário ----
	var first = D.dates.length ? parse(D.dates[0]) : new Date();
	var last = D.dates.length ? parse(D.dates[D.dates.length - 1]) : new Date();
	var view = new Date(first.getFullYear(), first.getMonth(), 1);
	function dayOpen(key) { return !!available[key] && D.slots.some(function (s) { return !full[key + '|' + s]; }); }
	function drawCal() {
		var y = view.getFullYear(), m = view.getMonth();
		var start = new Date(y, m, 1).getDay(), days = new Date(y, m + 1, 0).getDate();
		var canPrev = new Date(y, m, 1) > new Date(first.getFullYear(), first.getMonth(), 1);
		var canNext = new Date(y, m + 1, 1) <= last;
		var h = '<div class="pm-cal__head"><button type="button" class="pm-cal__nav" data-nav="-1" aria-label="Mês anterior"' + (canPrev ? '' : ' disabled') + '>‹</button><b>' + MONTHS[m] + ' ' + y + '</b><button type="button" class="pm-cal__nav" data-nav="1" aria-label="Próximo mês"' + (canNext ? '' : ' disabled') + '>›</button></div>';
		h += '<div class="pm-cal__grid">' + WK.map(function (w) { return '<span class="pm-cal__wk">' + w + '</span>'; }).join('');
		for (var i = 0; i < start; i++) { h += '<span></span>'; }
		for (var d = 1; d <= days; d++) {
			var key = ymd(new Date(y, m, d)), ok = dayOpen(key);
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

	// ---- Horários (um por horário: reservado = indisponível) ----
	function drawSlots() {
		if (!inp.date.value) { slotsBox.innerHTML = '<p class="pm-metro__hint">Escolha a data primeiro.</p>'; return; }
		slotsBox.innerHTML = D.slots.map(function (s) {
			var f = !!full[inp.date.value + '|' + s];
			return '<button type="button" class="pm-metro__slot" data-s="' + esc(s) + '" aria-pressed="' + (inp.slot.value === s) + '"' + (f ? ' disabled' : '') + '>' + esc(s) + (f ? ' <small>' + D.msgFull + '</small>' : '') + '</button>';
		}).join('');
	}
	slotsBox.addEventListener('click', function (e) {
		var b = e.target.closest('.pm-metro__slot');
		if (!b || b.disabled) { return; }
		inp.slot.value = b.dataset.s;
		slotsBox.querySelectorAll('.pm-metro__slot').forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
		update();
	});
	nameIn.addEventListener('input', update);
	phoneIn.addEventListener('input', function () {
		var d = phoneIn.value.replace(/\D+/g, '').slice(0, 11);
		phoneIn.value = d.length > 6 ? '(' + d.slice(0, 2) + ') ' + d.slice(2, d.length - 4) + '-' + d.slice(-4) : (d.length > 2 ? '(' + d.slice(0, 2) + ') ' + d.slice(2) : d);
		update();
	});

	// ---- Resumo ----
	function pickupDone() { return inp.line.value && inp.station.value && inp.date.value && inp.slot.value; }
	function update() {
		if (pickupDone()) {
			var l = lineById(inp.line.value), d = parse(inp.date.value);
			sum.innerHTML = '<span class="pm-metro__dot" style="background:' + l.color + '"></span>' + D.msgSum.replace('%s', '<b>Estação ' + esc(inp.station.value) + '</b> (Linha ' + esc(l.id + ' ' + l.name) + ') · ' + WKL[d.getDay()] + ', ' + pad(d.getDate()) + '/' + pad(d.getMonth() + 1) + ' · ' + esc(inp.slot.value));
			sum.className = 'pm-metro__sum is-ok';
		} else { sum.innerHTML = ''; sum.className = 'pm-metro__sum'; }
		box.classList.remove('is-error');
	}
	function showError(msg) {
		box.classList.add('is-error');
		sum.textContent = msg; sum.className = 'pm-metro__sum';
		box.scrollIntoView({ behavior: 'smooth', block: 'center' });
	}

	// ---- Reservar e abrir o WhatsApp ----
	if (form) {
		form.addEventListener('submit', function (e) {
			if (mode() !== 'metro') { return; }
			e.preventDefault(); e.stopImmediatePropagation();
			if (busy) { return; }
			var vid = form.querySelector('input[name="variation_id"]');
			if (vid && (!vid.value || vid.value === '0')) { askSize(); return; }
			if (!pickupDone() || nameIn.value.trim().length < 2) { showError(D.msgFill); return; }
			if (!/^[1-9]{2}9?\d{8}$/.test(phoneDigits())) { showError(D.msgPhone); phoneIn.focus(); return; }
			var body = new FormData(form);
			if (!body.get('add-to-cart')) { var b2 = form.querySelector('[name="add-to-cart"]'); if (b2) { body.set('add-to-cart', b2.value); } }
			busy = true;
			if (btn) { btn.classList.add('loading'); btn.disabled = true; btn.innerHTML = D.msgWait; }
			fetch(D.url, { method: 'POST', body: body, credentials: 'same-origin' })
				.then(function (r) { return r.text(); })
				.then(function (t) { var i = t.indexOf('{"ok"'); return JSON.parse(i > -1 ? t.slice(i) : t); })
				.then(function (res) {
					if (res && res.taken) { full[res.taken] = true; }
					if (!res || !res.ok) {
						if (res && res.taken) { inp.slot.value = ''; drawCal(); drawSlots(); }
						showError((res && res.message) || D.msgError);
						if (res && res.whatsapp) { sum.innerHTML += ' <a href="' + esc(res.whatsapp) + '" target="_blank" rel="noopener">' + D.msgOpen + '</a>'; }
						return;
					}
					inp.slot.value = ''; drawCal(); drawSlots();
					box.classList.add('is-done');
					sum.className = 'pm-metro__sum is-ok';
					sum.innerHTML = '<b>' + esc(D.msgOk.replace('%s', res.order)) + '</b><br><a class="pm-metro__wa" href="' + esc(res.whatsapp) + '" target="_blank" rel="noopener">' + D.msgOpen + '</a>';
					try { if (window.fbq) { window.fbq('track', 'Schedule'); } } catch (err) {}
					setTimeout(function () { window.location.href = res.whatsapp; }, 900);
				})
				.catch(function () { showError(D.msgError); })
				.finally(function () { busy = false; if (btn) { btn.classList.remove('loading'); btn.disabled = false; btn.innerHTML = mode() === 'metro' ? D.btn : btnText; } });
		}, true);
	}

	// ---- Tamanho dentro da retirada no metrô (o cliente pode esquecer lá em cima) ----
	var sizeSelects = form ? [].slice.call(form.querySelectorAll('table.variations select, .variations select')) : [];
	var sizeBlock = null;
	function sizeChosen() { var v = form && form.querySelector('input[name="variation_id"]'); return !v || (v.value && v.value !== '0'); }
	function syncSize() {
		if (!sizeBlock) { return; }
		sizeBlock.querySelectorAll('.pm-metro__size-btn').forEach(function (b) {
			var s = sizeSelects[+b.dataset.i];
			b.setAttribute('aria-pressed', s && s.value === b.dataset.v ? 'true' : 'false');
		});
		var ok = sizeSelects.every(function (s) { return !!s.value; });
		sizeBlock.classList.toggle('is-ok', ok);
		if (ok) { sizeBlock.classList.remove('is-missing'); }
	}
	if (sizeSelects.length) {
		sizeBlock = document.createElement('div');
		sizeBlock.className = 'pm-metro__step pm-metro__size';
		sizeBlock.innerHTML = sizeSelects.map(function (s, i) {
			var row = form.querySelector('label[for="' + s.id + '"]');
			var label = row ? row.textContent.trim() : 'Tamanho';
			var opts = [].slice.call(s.options).filter(function (o) { return o.value; });
			return '<span class="pm-metro__label"><b></b>' + esc(label) + '</span><div class="pm-metro__sizes" role="group" aria-label="' + esc(label) + '">' +
				opts.map(function (o) { return '<button type="button" class="pm-metro__size-btn" data-i="' + i + '" data-v="' + esc(o.value) + '" aria-pressed="false">' + esc(o.textContent.trim()) + '</button>'; }).join('') + '</div>';
		}).join('') + '<p class="pm-metro__size-warn" role="alert">' + esc(D.msgSize) + '</p>';
		panel.insertBefore(sizeBlock, panel.firstChild);
		sizeBlock.addEventListener('click', function (e) {
			var b = e.target.closest('.pm-metro__size-btn'); if (!b) { return; }
			var s = sizeSelects[+b.dataset.i];
			s.value = b.dataset.v;
			if (window.jQuery) { window.jQuery(s).trigger('change'); } else { s.dispatchEvent(new Event('change', { bubbles: true })); }
			syncSize(); update();
		});
		sizeSelects.forEach(function (s) { s.addEventListener('change', function () { setTimeout(syncSize, 30); }); });
		if (window.jQuery) { window.jQuery(form).on('found_variation reset_data woocommerce_variation_has_changed', function () { setTimeout(syncSize, 30); }); }
		syncSize();
	}
	function askSize() {
		if (sizeBlock) {
			sizeBlock.classList.remove('is-missing'); void sizeBlock.offsetWidth; sizeBlock.classList.add('is-missing');
			sizeBlock.scrollIntoView({ behavior: 'smooth', block: 'center' });
		} else { showError(D.msgSize); }
	}
	if (form && btn) {
		// Captura antes do WooCommerce (que só mostraria um alerta genérico ou nada).
		form.addEventListener('click', function (e) {
			if (mode() !== 'metro' || !e.target.closest('.single_add_to_cart_button') || sizeChosen()) { return; }
			e.preventDefault(); e.stopImmediatePropagation();
			askSize();
		}, true);
	}

	drawCal(); drawSlots(); update(); syncMode();
})();
