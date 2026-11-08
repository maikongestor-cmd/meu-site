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
