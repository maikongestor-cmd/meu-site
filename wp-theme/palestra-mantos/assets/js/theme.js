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

	// Recalcula o checkout ao trocar a forma de pagamento (desconto no Pix).
	if (window.jQuery) {
		window.jQuery(function ($) {
			$('form.checkout').on('change', 'input[name="payment_method"]', function () {
				$(document.body).trigger('update_checkout');
			});
		});
	}
})();
