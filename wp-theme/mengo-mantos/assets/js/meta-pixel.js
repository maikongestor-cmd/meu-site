/**
 * Eventos do Meta Pixel. Cada evento vai pelo navegador (fbq) e pelo servidor
 * (API de Conversões) com o mesmo eventID, para a Meta descartar a cópia.
 */
(function () {
	'use strict';
	var cfg = window.pmFb;
	if (!cfg || typeof window.fbq !== 'function') { return; }

	function uid(prefix) {
		return prefix + '.' + Date.now().toString(36) + Math.random().toString(36).slice(2, 10);
	}
	function track(name, data, id) {
		window.fbq('track', name, data || {}, { eventID: id });
	}
	// Envia ao servidor (que recalcula os valores e repassa à Meta).
	function beacon(name, id, extra) {
		var body = new FormData();
		body.append('n', name);
		body.append('i', id);
		body.append('u', window.location.href);
		Object.keys(extra || {}).forEach(function (k) { body.append(k, extra[k]); });
		if (navigator.sendBeacon && navigator.sendBeacon(cfg.beacon, body)) { return; }
		fetch(cfg.beacon, { method: 'POST', body: body, credentials: 'same-origin', keepalive: true }).catch(function () {});
	}
	function both(name, data, extra) {
		var id = uid(name.toLowerCase());
		track(name, data, id);
		beacon(name, id, extra);
	}
	// Eventos já enviados pelo servidor: o navegador repete com o mesmo ID.
	function replay(list) {
		(list || []).forEach(function (e) { if (e && e.n && e.i) { track(e.n, e.d, e.i); } });
	}

	// Espera o fbevents.js criar o cookie _fbp antes de avisar o servidor.
	var ready = new Promise(function (resolve) {
		var tries = 0;
		(function wait() {
			if (/(?:^|; )_fbp=/.test(document.cookie) || tries++ > 20) { resolve(); return; }
			setTimeout(wait, 150);
		})();
	});

	ready.then(function () {
		both('PageView', {});
		var page = cfg.page || {};
		if (page.view) {
			both('ViewContent', page.view, { p: page.view.content_ids[0] });
		}
		if (page.search) {
			both('Search', { search_string: page.search }, { q: page.search });
		}

		replay(cfg.events);
		replay(window.pmFbLate);

		var params = new URLSearchParams(window.location.search);
		if (params.get('newsletter') === 'ok') { both('Lead', { content_name: 'Newsletter' }); }
		if (params.get('contato') === 'ok') { both('Contact', { content_name: 'Formulário de contato' }); }
	});

	// "Comprar" com AJAX: o servidor devolve o AddToCart junto do carrinho.
	var origFetch = window.fetch;
	if (origFetch) {
		window.fetch = function () {
			var req = arguments[0];
			var url = typeof req === 'string' ? req : (req && req.url) || '';
			var p = origFetch.apply(this, arguments);
			if (/wc-ajax=(add_to_cart|pm_)/.test(url)) {
				p.then(function (res) {
					return res.clone().json().then(function (json) {
						if (json && json.fragments && json.fragments.pm_fb_events) { replay(json.fragments.pm_fb_events); }
					});
				}).catch(function () {});
			}
			return p;
		};
	}
	if (window.jQuery) {
		window.jQuery(document.body).on('added_to_cart', function (e, fragments) {
			if (fragments && fragments.pm_fb_events) { replay(fragments.pm_fb_events); }
		});
		// AddPaymentInfo: ao clicar em "Finalizar compra".
		var sentPayment = false;
		window.jQuery('form.checkout').on('checkout_place_order', function () {
			if (!sentPayment) {
				sentPayment = true;
				var method = (document.querySelector('input[name="payment_method"]:checked') || {}).value || '';
				both('AddPaymentInfo', { payment_type: method, currency: 'BRL' });
			}
			return true;
		});
	}

	// Contact: clique no WhatsApp.
	document.addEventListener('click', function (e) {
		var a = e.target.closest && e.target.closest('a[href*="wa.me/"], a[href*="api.whatsapp.com"]');
		if (a) { both('Contact', { content_name: 'WhatsApp' }); }
	}, true);
})();
