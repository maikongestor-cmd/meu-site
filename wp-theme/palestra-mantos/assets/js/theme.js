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

			// Nossa rota (frete grátis, aviso e CEP salvo) + cotação do plugin Melhor Envio, quando ativo.
			var own = fetch(pmShip.url, { method: 'POST', body: body, credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.catch(function () { return null; });
			var me = Promise.resolve(null);
			if (window.meSC && window.meSC.ajaxUrl) {
				var meBody = new FormData();
				meBody.append('action', 'me_quote');
				meBody.append('nonce', window.meSC.nonce);
				meBody.append('cep', cep);
				meBody.append('product_id', window.meSC.productId || ship.getAttribute('data-product'));
				meBody.append('quantity', qty && qty.value ? qty.value : '1');
				me = fetch(window.meSC.ajaxUrl, { method: 'POST', body: meBody, credentials: 'same-origin' })
					.then(function (r) { return r.json(); })
					.catch(function () { return null; });
			}

			Promise.all([own, me]).then(function (out) {
				var res = out[0], meRes = out[1];
				var options = res && res.success && res.data.options ? res.data.options.slice() : [];
				var hasFree = options.some(function (o) { return o.free; });
				if (hasFree) { options = options.filter(function (o) { return o.free; }); }
				// Com frete grátis o carrinho só mostra ele; aqui também. A cotação direta do
				// Melhor Envio só entra se a da loja (a mesma do checkout) não trouxer opções.
				if (!hasFree && !options.length && meRes && meRes.success && meRes.data && meRes.data.length) {
					meRes.data.forEach(function (s) {
						var price = parseFloat(s.price);
						if (!(price > 0)) { return; }
						var days = parseInt(s.delivery_time, 10);
						var label = [s.company, s.name].filter(Boolean).join(' ');
						if (days > 0) { label += ' · ' + (days === 1 ? pmShip.day : pmShip.days.replace('%d', days)); }
						options.push({ label: label, cost: price.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }), free: false });
					});
				}
				if (!options.length) {
					var msg = res && res.success ? pmShip.msgNone : (res && res.data && res.data.message ? res.data.message : pmShip.msgError);
					result.innerHTML = '<p class="pm-ship__error">' + esc(msg) + '</p>';
					return;
				}
				var html = '<ul class="pm-ship__list">';
				options.forEach(function (o) {
					html += '<li' + (o.free ? ' class="is-free"' : '') + '><span>' + esc(o.label) + '</span><b>' + esc(o.cost) + '</b></li>';
				});
				html += '</ul>';
				if (!hasFree && res && res.success && res.data.note) { html += '<p class="pm-ship__note">' + esc(res.data.note) + '</p>'; }
				result.innerHTML = html;
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

	// ---------- Carrinho lateral ----------
	var drawer = document.getElementById('pm-minicart');
	var drawerOverlay = document.querySelector('.pm-minicart-overlay');
	function openDrawer(added) {
		if (!drawer) { return false; }
		var msg = drawer.querySelector('.pm-minicart__added');
		if (msg) { msg.hidden = !added; }
		if (drawerOverlay) { drawerOverlay.hidden = false; requestAnimationFrame(function () { drawerOverlay.classList.add('is-open'); }); }
		drawer.classList.add('is-open');
		drawer.setAttribute('aria-hidden', 'false');
		document.body.classList.add('pm-drawer-open');
		var close = drawer.querySelector('.pm-minicart__close');
		if (close) { close.focus(); }
		return true;
	}
	function closeDrawer() {
		if (!drawer) { return; }
		drawer.classList.remove('is-open');
		drawer.setAttribute('aria-hidden', 'true');
		if (drawerOverlay) { drawerOverlay.classList.remove('is-open'); }
		document.body.classList.remove('pm-drawer-open');
	}
	function applyFragments(fragments) {
		if (!fragments) { return; }
		Object.keys(fragments).forEach(function (sel) {
			document.querySelectorAll(sel).forEach(function (el) {
				var tmp = document.createElement('div');
				tmp.innerHTML = fragments[sel];
				if (tmp.firstElementChild) { el.replaceWith(tmp.firstElementChild); }
			});
		});
	}
	if (drawer) {
		document.addEventListener('click', function (e) {
			if (e.target.closest('.pm-minicart__close') || e.target === drawerOverlay) { closeDrawer(); return; }
			var cartLink = e.target.closest('.pm-actions a[href]');
			if (cartLink && cartLink.querySelector('.pm-cart-count') && window.pmCart && window.pmCart.drawer) {
				e.preventDefault();
				openDrawer(false);
				return;
			}
			var rm = e.target.closest('.pm-minicart__remove');
			if (rm && window.pmCart) {
				e.preventDefault();
				var body = new URLSearchParams();
				body.append('cart_key', rm.getAttribute('data-cart-key'));
				rm.closest('li').style.opacity = '.4';
				fetch(window.pmCart.remove, { method: 'POST', body: body, credentials: 'same-origin' })
					.then(function (r) { return r.json(); })
					.then(function (res) { applyFragments(res && res.fragments); })
					.catch(function () { window.location.href = rm.href; });
			}
		});
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeDrawer(); } });
		if ($) {
			// Botões "Comprar" da vitrine (AJAX nativo do WooCommerce).
			$(document.body).on('added_to_cart', function () { openDrawer(true); });
		}
	}

	// "Comprar" na página do produto sem recarregar: adiciona e abre o carrinho lateral.
	var singleForm = document.querySelector('.single-product div.product form.cart:not(.grouped_form)');
	if (singleForm && drawer && window.pmCart && window.pmCart.add) {
		singleForm.addEventListener('submit', function (e) {
			var btn = singleForm.querySelector('[type="submit"][name="add-to-cart"], button.single_add_to_cart_button');
			var vid = singleForm.querySelector('input[name="variation_id"]');
			var pid = vid ? vid.value : ((singleForm.querySelector('[name="add-to-cart"]') || {}).value || '');
			if (!pid || pid === '0' || (btn && btn.classList.contains('disabled'))) { return; }
			e.preventDefault();
			var qty = singleForm.querySelector('input.qty');
			var body = new URLSearchParams();
			body.append('product_id', pid);
			body.append('quantity', qty ? qty.value : '1');
			if (btn) { btn.classList.add('loading'); btn.disabled = true; }
			fetch(window.pmCart.add, { method: 'POST', body: body, credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (res) {
					if (!res || res.error) { singleForm.submit(); return; }
					applyFragments(res.fragments);
					if ($) { $(document.body).trigger('wc_fragments_refreshed'); }
					openDrawer(true);
				})
				.catch(function () { singleForm.submit(); })
				.finally(function () { if (btn) { btn.classList.remove('loading'); btn.disabled = false; } });
		});
	}

	// Atalho "Ver vídeo do produto" → abre a aba Vídeo.
	document.addEventListener('click', function (e) {
		var link = e.target.closest('[data-pm-tab]');
		if (!link) { return; }
		var tab = document.querySelector('.wc-tabs li.' + link.getAttribute('data-pm-tab') + '_tab a, a[href="#tab-' + link.getAttribute('data-pm-tab') + '"]:not([data-pm-tab])');
		if (tab) {
			e.preventDefault();
			tab.click();
			var wrap = document.querySelector('.woocommerce-tabs');
			if (wrap) { wrap.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
		}
	});

	// ---------- Pesquisa de saída no checkout ----------
	var exitBox = document.getElementById('pm-exit');
	if (exitBox && window.pmExit) {
		var KEY_EXIT = 'pm_exit_done';
		var shown = false;
		var placing = false;
		var armed = false;
		try { shown = sessionStorage.getItem(KEY_EXIT) === '1'; } catch (err) {}
		setTimeout(function () { armed = true; }, 4000);
		var checkoutForm = document.querySelector('form.checkout');
		if (checkoutForm) { checkoutForm.addEventListener('submit', function () { placing = true; }); }
		if ($) { $(document.body).on('checkout_place_order', function () { placing = true; return true; }); }

		function showExit() {
			if (shown || placing || !armed) { return false; }
			shown = true;
			try { sessionStorage.setItem(KEY_EXIT, '1'); } catch (err) {}
			exitBox.hidden = false;
			document.body.classList.add('pm-exit-open');
			var first = exitBox.querySelector('.pm-exit__opt');
			if (first) { first.focus(); }
			return true;
		}
		function hideExit() {
			exitBox.hidden = true;
			document.body.classList.remove('pm-exit-open');
		}
		function sendReason(reason, text) {
			var body = new URLSearchParams();
			body.append('reason', reason);
			body.append('text', text || '');
			fetch(window.pmExit.url, { method: 'POST', body: body, credentials: 'same-origin' }).catch(function () {});
			exitBox.querySelector('[data-step="ask"]').hidden = true;
			exitBox.querySelector('[data-step="thanks"]').hidden = false;
		}
		// Computador: mouse saindo pelo topo da janela.
		document.addEventListener('mouseout', function (e) {
			if (!e.relatedTarget && e.clientY <= 0) { showExit(); }
		});
		// Celular: botão "voltar".
		if (!shown && window.history && history.pushState) {
			history.pushState({ pmExit: 1 }, '', window.location.href);
			window.addEventListener('popstate', function () {
				if (!showExit()) { history.back(); }
			});
		}
		exitBox.addEventListener('click', function (e) {
			if (e.target === exitBox || e.target.closest('.pm-exit__close') || e.target.closest('.pm-exit__back')) { hideExit(); return; }
			var opt = e.target.closest('.pm-exit__opt');
			if (opt) {
				if (opt.getAttribute('data-reason') === 'outro') {
					exitBox.querySelector('.pm-exit__other').hidden = false;
					exitBox.querySelector('#pm-exit-text').focus();
				} else {
					sendReason(opt.getAttribute('data-reason'));
				}
			}
			if (e.target.closest('.pm-exit__send')) {
				sendReason('outro', exitBox.querySelector('#pm-exit-text').value);
			}
		});
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !exitBox.hidden) { hideExit(); } });
	}

	// Avaliações no checkout: 2 por vez com setas.
	document.querySelectorAll('.pm-co-reviews').forEach(function (box) {
		var pages = parseInt(box.getAttribute('data-pages'), 10) || 1;
		var page = 1;
		var prev = box.querySelector('.pm-co-reviews__prev');
		var next = box.querySelector('.pm-co-reviews__next');
		var count = box.querySelector('.pm-co-reviews__count');
		function show() {
			box.querySelectorAll('.pm-co-review').forEach(function (r) { r.hidden = parseInt(r.getAttribute('data-page'), 10) !== page; });
			if (count) { count.textContent = page + '/' + pages; }
			if (prev) { prev.disabled = page <= 1; }
			if (next) { next.disabled = page >= pages; }
		}
		if (prev) { prev.addEventListener('click', function () { if (page > 1) { page--; show(); } }); }
		if (next) { next.addEventListener('click', function () { if (page < pages) { page++; show(); } }); }
	});

	// Links para #avaliacoes: rolagem suave até o fim da página do produto.
	document.addEventListener('click', function (e) {
		var a = e.target.closest('a[href="#avaliacoes"]');
		var target = a && document.getElementById('avaliacoes');
		if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); history.replaceState(null, '', '#avaliacoes'); }
	});

	// Recalcula o checkout ao trocar a forma de pagamento (desconto no Pix).
	if (window.jQuery) {
		window.jQuery(function ($) {
			$('form.checkout').on('change', 'input[name="payment_method"]', function () {
				$(document.body).trigger('update_checkout');
			});
		});
	}
})();
