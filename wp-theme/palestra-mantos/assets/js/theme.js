/* Palestra Mantos — interações do tema. */
(function () {
	'use strict';

	// Menu lateral no celular.
	var nav = document.getElementById('pm-nav');
	var toggle = document.querySelector('.pm-menu-toggle');
	var closeBtn = document.querySelector('.pm-menu-close');
	var overlay = document.querySelector('.pm-overlay');

	function setMenu(open) {
		if (!nav) { return; }
		nav.classList.toggle('is-open', open);
		if (overlay) {
			overlay.hidden = false;
			overlay.classList.toggle('is-open', open);
		}
		if (toggle) { toggle.setAttribute('aria-expanded', open ? 'true' : 'false'); }
		document.body.style.overflow = open ? 'hidden' : '';
		document.body.classList.toggle('pm-menu-open', open);
	}
	if (toggle) { toggle.addEventListener('click', function () { setMenu(true); }); }
	if (closeBtn) { closeBtn.addEventListener('click', function () { setMenu(false); }); }
	if (overlay) { overlay.addEventListener('click', function () { setMenu(false); }); }
	document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { setMenu(false); } });

	// Botões de tamanho no lugar do <select> de variações.
	function buildSwatches(form) {
		var selects = form.querySelectorAll('.variations select');
		selects.forEach(function (select) {
			if (select.dataset.pmSwatches) { return; }
			select.dataset.pmSwatches = '1';
			var cell = select.closest('td') || select.parentNode;
			cell.classList.add('pm-has-swatches');
			var wrap = document.createElement('div');
			wrap.className = 'pm-swatches';
			wrap.setAttribute('role', 'radiogroup');

			function render() {
				wrap.innerHTML = '';
				Array.prototype.forEach.call(select.options, function (opt) {
					if (!opt.value) { return; }
					var b = document.createElement('button');
					b.type = 'button';
					b.className = 'pm-swatch' + (select.value === opt.value ? ' is-active' : '');
					b.textContent = opt.textContent;
					b.setAttribute('role', 'radio');
					b.setAttribute('aria-checked', select.value === opt.value ? 'true' : 'false');
					b.disabled = opt.disabled;
					b.addEventListener('click', function () {
						select.value = select.value === opt.value ? '' : opt.value;
						select.dispatchEvent(new Event('change', { bubbles: true }));
						if (window.jQuery) { window.jQuery(select).trigger('change'); }
						render();
					});
					wrap.appendChild(b);
				});
			}
			render();
			select.parentNode.insertBefore(wrap, select);
			if (window.jQuery) {
				window.jQuery(form).on('woocommerce_update_variation_values reset_data', function () { setTimeout(render, 0); });
			}
		});
	}
	document.querySelectorAll('form.variations_form').forEach(buildSwatches);

	// Provador "Descubra seu tamanho".
	function initFit(root) {
		var table;
		try { table = JSON.parse(root.getAttribute('data-table')); } catch (e) { return; }
		var h = root.querySelector('input[name="height"]');
		var w = root.querySelector('input[name="weight"]');
		var body = root.querySelector('.pm-fit__body');
		var top = root.querySelector('.pm-fit__top');
		var pants = root.querySelector('.pm-fit__pants');
		var out = function (k) { return root.querySelector('[data-out="' + k + '"]'); };
		var STORE = 'pm_fit';
		var current = 'M';

		try {
			var saved = JSON.parse(localStorage.getItem(STORE) || 'null');
			if (saved) { h.value = saved.h; w.value = saved.w; }
		} catch (e) {}

		function idx(value, limits) {
			var i = 0;
			while (i < limits.length && value > limits[i]) { i++; }
			return i;
		}
		function pref() {
			var r = root.querySelector('.pm-fit__pref input:checked');
			return r ? parseInt(r.value, 10) : 0;
		}
		function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }

		function update() {
			var hv = parseInt(h.value, 10);
			var wv = parseInt(w.value, 10);
			var last = table.sizes.length - 1;
			var score = 0.65 * idx(wv, table.weight) + 0.35 * idx(hv, table.height) + 0.6 * pref();
			var i = clamp(Math.round(score), 0, last);
			current = table.sizes[i];

			out('height').textContent = hv + ' cm';
			out('weight').textContent = wv + ' kg';
			out('height-m').textContent = (hv / 100).toFixed(2).replace('.', ',') + ' m';
			out('size').textContent = current;
			var btn = out('size-btn');
			if (btn) { btn.textContent = current; }

			var note = '';
			var frac = score - Math.floor(score);
			if (wv > table.max.weight || hv > table.max.height) {
				note = 'Acima da nossa tabela: o GG pode ficar justo. Fale com a gente no WhatsApp.';
			} else if (frac > 0.35 && frac < 0.65 && Math.floor(score) < last && score >= 0) {
				note = 'Você está entre ' + table.sizes[Math.floor(score)] + ' e ' + table.sizes[Math.floor(score) + 1] + '. Para ficar mais solto, escolha o ' + table.sizes[Math.floor(score) + 1] + '.';
			}
			out('note').textContent = note;

			// Corpo: largura pelo IMC, altura pela estatura.
			var bmi = wv / Math.pow(hv / 100, 2);
			var sx = clamp(0.7 + (bmi - 18) * 0.036, 0.72, 1.55);
			var sy = clamp(hv / 178, 0.8, 1.16);
			body.style.transform = 'scale(' + sx.toFixed(3) + ',' + sy.toFixed(3) + ')';

			// Roupa: quanto o tamanho escolhido "sobra" no corpo.
			var garment = clamp((0.86 + i * 0.13) / sx, 0.94, 1.22);
			[top, pants].forEach(function (g) { if (g) { g.style.transform = 'scaleX(' + garment.toFixed(3) + ')'; } });

			try { localStorage.setItem(STORE, JSON.stringify({ h: hv, w: wv })); } catch (e) {}
		}

		root.addEventListener('input', update);
		root.addEventListener('change', update);
		update();

		var apply = root.querySelector('.pm-fit__apply');
		if (apply) {
			apply.addEventListener('click', function () {
				var done = false;
				document.querySelectorAll('.pm-swatch').forEach(function (b) {
					if (!done && b.textContent.trim().toUpperCase() === current && !b.disabled) {
						if (!b.classList.contains('is-active')) { b.click(); }
						done = true;
					}
				});
				if (!done) {
					var sel = document.querySelector('.variations select');
					if (sel) {
						Array.prototype.forEach.call(sel.options, function (o) {
							if (o.textContent.trim().toUpperCase() === current) { sel.value = o.value; done = true; }
						});
						sel.dispatchEvent(new Event('change', { bubbles: true }));
						if (window.jQuery) { window.jQuery(sel).trigger('change'); }
					}
				}
				var dlg = root.closest('dialog');
				if (dlg && dlg.close) { dlg.close(); }
			});
		}
	}
	document.querySelectorAll('.pm-fit').forEach(initFit);

	var fitDialog = document.querySelector('.pm-fit-dialog');
	if (fitDialog) {
		document.querySelectorAll('.pm-fit-open').forEach(function (b) {
			b.addEventListener('click', function () {
				if (fitDialog.showModal) { fitDialog.showModal(); } else { fitDialog.setAttribute('open', ''); }
			});
		});
		var fitClose = fitDialog.querySelector('.pm-fit-dialog__close');
		if (fitClose) { fitClose.addEventListener('click', function () { fitDialog.close ? fitDialog.close() : fitDialog.removeAttribute('open'); }); }
		fitDialog.addEventListener('click', function (e) { if (e.target === fitDialog && fitDialog.close) { fitDialog.close(); } });
	}

	// Calculadora de frete por CEP na página do produto.
	var ship = document.querySelector('.pm-ship');
	if (ship && window.pmShip) {
		var cepInput = ship.querySelector('input[name="cep"]');
		var result = ship.querySelector('.pm-ship__result');
		var KEY = 'pm_cep';

		function maskCep(v) {
			v = v.replace(/\D/g, '').slice(0, 8);
			return v.length > 5 ? v.slice(0, 5) + '-' + v.slice(5) : v;
		}
		function esc(t) {
			var d = document.createElement('div');
			d.textContent = t;
			return d.innerHTML;
		}
		cepInput.addEventListener('input', function () { cepInput.value = maskCep(cepInput.value); });
		try { var saved = localStorage.getItem(KEY); if (saved) { cepInput.value = maskCep(saved); } } catch (e) {}

		ship.addEventListener('submit', function (e) {
			e.preventDefault();
			var cep = cepInput.value.replace(/\D/g, '');
			if (cep.length !== 8) {
				result.innerHTML = '<p class="pm-ship__error">' + esc(pmShip.msgCep) + '</p>';
				cepInput.focus();
				return;
			}
			try { localStorage.setItem(KEY, cep); } catch (err) {}
			var cartForm = document.querySelector('form.cart');
			var variation = cartForm ? cartForm.querySelector('input[name="variation_id"]') : null;
			var qty = cartForm ? cartForm.querySelector('input.qty') : null;
			var body = new FormData();
			body.append('cep', cep);
			body.append('product_id', ship.getAttribute('data-product'));
			body.append('variation_id', variation && variation.value ? variation.value : '0');
			body.append('qty', qty && qty.value ? qty.value : '1');
			result.innerHTML = '<p class="pm-ship__loading">' + esc(pmShip.loading) + '</p>';

			fetch(pmShip.url, { method: 'POST', body: body, credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (res) {
					if (!res || !res.success) {
						var msg = res && res.data && res.data.message ? res.data.message : pmShip.msgError;
						result.innerHTML = '<p class="pm-ship__error">' + esc(msg) + '</p>';
						return;
					}
					var html = '<ul class="pm-ship__list">';
					res.data.options.forEach(function (o) {
						html += '<li' + (o.free ? ' class="is-free"' : '') + '><span>' + esc(o.label) + '</span><b>' + esc(o.cost) + '</b></li>';
					});
					html += '</ul>';
					if (res.data.note) { html += '<p class="pm-ship__note">' + esc(res.data.note) + '</p>'; }
					result.innerHTML = html;
				})
				.catch(function () {
					result.innerHTML = '<p class="pm-ship__error">' + esc(pmShip.msgError) + '</p>';
				});
		});
	}

	// Recalcula o checkout ao trocar a forma de pagamento (desconto no Pix).
	if (window.jQuery) {
		window.jQuery(function ($) {
			$('form.checkout').on('change', 'input[name="payment_method"]', function () {
				$(document.body).trigger('update_checkout');
			});
		});
	}
})();
