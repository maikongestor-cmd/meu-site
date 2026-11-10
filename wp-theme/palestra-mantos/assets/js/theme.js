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

	// Banner rotativo.
	var slider = document.querySelector('.pm-slider');
	if (slider) {
		var slides = slider.querySelectorAll('.pm-slide');
		var dots = slider.querySelectorAll('.pm-slider__dot');
		var current = 0;
		var timer = null;
		var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		function go(n) {
			current = (n + slides.length) % slides.length;
			slides.forEach(function (s, i) {
				var on = i === current;
				s.classList.toggle('is-active', on);
				if (on) { s.removeAttribute('aria-hidden'); } else { s.setAttribute('aria-hidden', 'true'); }
				s.querySelectorAll('a').forEach(function (a) { if (on) { a.removeAttribute('tabindex'); } else { a.setAttribute('tabindex', '-1'); } });
			});
			dots.forEach(function (d, i) { d.classList.toggle('is-active', i === current); });
		}
		function start() { if (!reduce && slides.length > 1) { stop(); timer = setInterval(function () { go(current + 1); }, 6000); } }
		function stop() { if (timer) { clearInterval(timer); timer = null; } }

		var prev = slider.querySelector('.pm-slider__arrow--prev');
		var next = slider.querySelector('.pm-slider__arrow--next');
		if (prev) { prev.addEventListener('click', function () { go(current - 1); start(); }); }
		if (next) { next.addEventListener('click', function () { go(current + 1); start(); }); }
		dots.forEach(function (d, i) { d.addEventListener('click', function () { go(i); start(); }); });
		slider.addEventListener('mouseenter', stop);
		slider.addEventListener('mouseleave', start);
		slider.addEventListener('focusin', stop);

		var x0 = null;
		slider.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; stop(); }, { passive: true });
		slider.addEventListener('touchend', function (e) {
			if (x0 === null) { return; }
			var dx = e.changedTouches[0].clientX - x0;
			if (Math.abs(dx) > 40) { go(current + (dx < 0 ? 1 : -1)); }
			x0 = null;
			start();
		});
		document.addEventListener('visibilitychange', function () { if (document.hidden) { stop(); } else { start(); } });
		start();
	}

	// Provador "Descubra seu tamanho" (feminino / masculino).
	function initFit(root) {
		var table;
		try { table = JSON.parse(root.getAttribute('data-table')); } catch (e) { return; }
		var h = root.querySelector('input[name="height"]');
		var w = root.querySelector('input[name="weight"]');
		var bodies = root.querySelectorAll('.pm-fit__body');
		var garments = root.querySelectorAll('.pm-fit__top, .pm-fit__pants');
		var out = function (k) { return root.querySelector('[data-out="' + k + '"]'); };
		var STORE = 'pm_fit';
		var current = 'M';

		function setSex(v) {
			root.setAttribute('data-sex', v);
			root.querySelectorAll('.pm-fit__sex input').forEach(function (r) { r.checked = r.value === v; });
		}
		try {
			var saved = JSON.parse(localStorage.getItem(STORE) || 'null');
			if (saved) { h.value = saved.h; w.value = saved.w; if (saved.s) { setSex(saved.s); } }
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
			var sex = root.getAttribute('data-sex') === 'm' ? 'm' : 'f';
			var t = table[sex] || table;
			var hv = parseInt(h.value, 10);
			var wv = parseInt(w.value, 10);
			var last = table.sizes.length - 1;
			var score = 0.65 * idx(wv, t.weight) + 0.35 * idx(hv, t.height) + 0.6 * pref();
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
			if (wv > t.max.weight || hv > t.max.height) {
				note = 'Acima da nossa tabela: o GG pode ficar justo. Fale com a gente no WhatsApp.';
			} else if (frac > 0.35 && frac < 0.65 && Math.floor(score) < last && score >= 0) {
				note = 'Você está entre ' + table.sizes[Math.floor(score)] + ' e ' + table.sizes[Math.floor(score) + 1] + '. Para ficar mais solto, escolha o ' + table.sizes[Math.floor(score) + 1] + '.';
			}
			out('note').textContent = note;

			// Corpo: largura pelo IMC, altura pela estatura (referência diferente por sexo).
			var bmi = wv / Math.pow(hv / 100, 2);
			var sx = clamp(0.7 + (bmi - 18) * 0.036, 0.72, 1.55);
			var sy = clamp(hv / (sex === 'm' ? 178 : 168), 0.8, 1.16);
			bodies.forEach(function (b) { b.style.transform = 'scale(' + sx.toFixed(3) + ',' + sy.toFixed(3) + ')'; });

			var garment = clamp((0.86 + i * 0.13) / sx, 0.94, 1.22);
			garments.forEach(function (g) { g.style.transform = 'scaleX(' + garment.toFixed(3) + ')'; });

			try { localStorage.setItem(STORE, JSON.stringify({ h: hv, w: wv, s: sex })); } catch (e) {}
		}

		root.querySelectorAll('.pm-fit__sex input').forEach(function (r) {
			r.addEventListener('change', function () { if (r.checked) { setSex(r.value); update(); } });
		});
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
				var cart = document.querySelector('form.cart');
				if (cart) {
					cart.scrollIntoView({ behavior: 'smooth', block: 'center' });
					cart.classList.add('pm-flash');
					setTimeout(function () { cart.classList.remove('pm-flash'); }, 1600);
				}
			});
		}
	}
	document.querySelectorAll('.pm-fit').forEach(initFit);

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

	// ---------- Fluxo de compra ----------
	var $ = window.jQuery;

	// Quantidade com botões + e –.
	function addQtyButtons(scope) {
		(scope || document).querySelectorAll('.quantity input.qty:not([type="hidden"])').forEach(function (input) {
			if (input.closest('.pm-qty') || input.readOnly) { return; }
			var wrap = document.createElement('span');
			wrap.className = 'pm-qty';
			input.parentNode.insertBefore(wrap, input);
			var minus = document.createElement('button');
			minus.type = 'button'; minus.textContent = '−'; minus.setAttribute('aria-label', 'Diminuir quantidade');
			var plus = document.createElement('button');
			plus.type = 'button'; plus.textContent = '+'; plus.setAttribute('aria-label', 'Aumentar quantidade');
			wrap.appendChild(minus); wrap.appendChild(input); wrap.appendChild(plus);
			function step(d) {
				var min = parseFloat(input.min) || 0;
				var max = parseFloat(input.max) || Infinity;
				var v = (parseFloat(input.value) || 0) + d;
				if (v < Math.max(min, input.closest('.woocommerce-cart-form') ? 0 : 1)) { return; }
				if (v > max) { return; }
				input.value = v;
				input.dispatchEvent(new Event('change', { bubbles: true }));
				if ($) { $(input).trigger('change'); }
			}
			minus.addEventListener('click', function () { step(-1); });
			plus.addEventListener('click', function () { step(1); });
		});
	}
	addQtyButtons();

	// Carrinho: atualiza sozinho ao mudar a quantidade.
	var qtyTimer;
	document.addEventListener('change', function (e) {
		if (!e.target.matches || !e.target.matches('.woocommerce-cart-form input.qty')) { return; }
		clearTimeout(qtyTimer);
		qtyTimer = setTimeout(function () {
			var btn = document.querySelector('.woocommerce-cart-form [name="update_cart"]');
			if (btn) { btn.disabled = false; btn.removeAttribute('aria-disabled'); btn.click(); }
		}, 500);
	});
	if ($) {
		$(document.body).on('updated_wc_div updated_cart_totals', function () { addQtyButtons(); });
	}

	// Máscaras.
	function onlyDigits(v) { return (v || '').replace(/\D/g, ''); }
	var masks = {
		cpf: function (v) { v = onlyDigits(v).slice(0, 11); return v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2'); },
		cep: function (v) { v = onlyDigits(v).slice(0, 8); return v.length > 5 ? v.slice(0, 5) + '-' + v.slice(5) : v; },
		phone: function (v) {
			v = onlyDigits(v).slice(0, 11);
			if (v.length <= 2) { return v.length ? '(' + v : v; }
			if (v.length <= 6) { return '(' + v.slice(0, 2) + ') ' + v.slice(2); }
			if (v.length <= 10) { return '(' + v.slice(0, 2) + ') ' + v.slice(2, 6) + '-' + v.slice(6); }
			return '(' + v.slice(0, 2) + ') ' + v.slice(2, 7) + '-' + v.slice(7);
		}
	};
	var maskMap = { billing_cpf: 'cpf', billing_postcode: 'cep', shipping_postcode: 'cep', calc_shipping_postcode: 'cep', billing_phone: 'phone' };
	document.addEventListener('input', function (e) {
		var m = maskMap[e.target.id];
		if (!m) { return; }
		var before = e.target.value;
		var after = masks[m](before);
		if (after !== before) { e.target.value = after; }
		if (m === 'cep' && onlyDigits(after).length === 8 && e.target.id !== 'calc_shipping_postcode') { fillCep(e.target); }
	});

	// Preenchimento automático do navegador e CEP que já vem preenchido.
	document.addEventListener('change', function (e) {
		var id = e.target.id;
		if ((id === 'billing_postcode' || id === 'shipping_postcode') && onlyDigits(e.target.value).length === 8) {
			e.target.value = masks.cep(e.target.value);
			fillCep(e.target);
		}
	});
	function autoFillOnLoad() {
		['billing', 'shipping'].forEach(function (prefix) {
			var cep = document.getElementById(prefix + '_postcode');
			var street = document.getElementById(prefix + '_address_1');
			if (cep && street && !street.value && onlyDigits(cep.value).length === 8) {
				cep.value = masks.cep(cep.value);
				fillCep(cep);
			}
		});
	}

	// CEP → endereço (ViaCEP).
	function setField(id, value) {
		var el = document.getElementById(id);
		if (!el || !value) { return; }
		el.value = value;
		if ($) { $(el).trigger('change'); } else { el.dispatchEvent(new Event('change', { bubbles: true })); }
	}
	function fillCep(input) {
		if (!window.pmCheckout || input.dataset.pmCep === onlyDigits(input.value)) { return; }
		var cep = onlyDigits(input.value);
		input.dataset.pmCep = cep;
		var prefix = input.id.indexOf('shipping') === 0 ? 'shipping' : 'billing';
		var row = input.closest('.form-row');
		var msg = row && row.querySelector('.pm-cep-msg');
		if (row && !msg) { msg = document.createElement('span'); msg.className = 'pm-cep-msg'; row.appendChild(msg); }
		if (msg) { msg.className = 'pm-cep-msg'; msg.textContent = 'Buscando endereço…'; }
		fetch(window.pmCheckout.viacep.replace('%s', cep))
			.then(function (r) { return r.json(); })
			.then(function (d) {
				if (!d || d.erro) { throw new Error('cep'); }
				setField(prefix + '_address_1', d.logradouro);
				setField(prefix + '_neighborhood', d.bairro);
				setField(prefix + '_city', d.localidade);
				setField(prefix + '_state', d.uf);
				if (msg) { msg.textContent = [d.logradouro, d.bairro, d.localidade + '/' + d.uf].filter(Boolean).join(' · '); }
				var num = document.getElementById(prefix + '_number');
				if (num && !num.value) { num.focus(); }
				if ($) { $(document.body).trigger('update_checkout'); }
			})
			.catch(function () {
				if (msg) { msg.className = 'pm-cep-msg is-error'; msg.textContent = window.pmCheckout.msgCep; }
			});
	}

	if (window.pmCheckout) { setTimeout(autoFillOnLoad, 300); }

	// Recalcula o checkout ao trocar a forma de pagamento (desconto no Pix).
	if (window.jQuery) {
		window.jQuery(function ($) {
			$('form.checkout').on('change', 'input[name="payment_method"]', function () {
				$(document.body).trigger('update_checkout');
			});
		});
	}
})();
