(function () {
	// Sticky shadow on scroll
	var h = document.getElementById('ar-header');
	if (h) window.addEventListener('scroll', function () {
		h.classList.toggle('scrolled', window.scrollY > 10);
	}, { passive: true });
}());

/* ── Mobile nav toggle ───────────────────────────────────────────── */
function arCloseMobileMenu() {
	var menu  = document.getElementById('ar-mobile-menu');
	var btn   = document.getElementById('ar-mobile-toggle-btn');
	var iconM = document.getElementById('ar-menu-icon');
	var iconC = document.getElementById('ar-close-icon');
	if (menu)  menu.classList.remove('open');
	if (btn)   btn.setAttribute('aria-expanded', 'false');
	if (iconM) iconM.style.display = '';
	if (iconC) iconC.style.display = 'none';
}

/* ── Mobile logout — reuses CSRF token from desktop module form ── */
function arMobileLogout() {
	// The desktop .ar-account-dropdown form already has the Joomla CSRF token
	var form = document.querySelector('.ar-account-dropdown form');
	if (form) { form.submit(); return; }
	// Fallback: find any logout form on the page
	var allForms = document.querySelectorAll('form');
	for (var _i = 0; _i < allForms.length; _i++) {
		var _task = allForms[_i].querySelector('input[name="task"]');
		if (_task && _task.value === 'user.logout') { allForms[_i].submit(); return; }
	}
}

function arToggleMobile() {
	var menu   = document.getElementById('ar-mobile-menu');
	var btn    = document.getElementById('ar-mobile-toggle-btn');
	var iconM  = document.getElementById('ar-menu-icon');
	var iconC  = document.getElementById('ar-close-icon');
	if (!menu) return;
	var open = menu.classList.contains('open');
	menu.classList.toggle('open', !open);
	if (btn)   btn.setAttribute('aria-expanded', String(!open));
	if (iconM) iconM.style.display = open ? '' : 'none';
	if (iconC) iconC.style.display = open ? 'none' : '';
}

document.addEventListener('DOMContentLoaded', function () {

	/* Close nav on link click */
	document.querySelectorAll('.ar-mobile-menu a').forEach(function (l) {
		l.addEventListener('click', arCloseMobileMenu);
	});

	/* Move login modal out of sticky header (stacking-context trap) */
	var modal = document.getElementById('ja-login-form');
	if (modal) document.body.appendChild(modal);

	/* ── Desktop language dropdown ─────────────────────────────── */
	var lc = document.querySelector('.ar-lang .mod-languages');
	var lt = document.querySelector('.ar-lang .dropdown-toggle');
	if (lt && lc) {
		lt.addEventListener('click', function (e) {
			e.preventDefault(); e.stopPropagation();
			lc.classList.toggle('open');
		});
		document.addEventListener('click', function (e) {
			if (!lc.contains(e.target)) lc.classList.remove('open');
		});
	}

	/* ── Sync mobile language URLs from desktop switcher ─────────
	   The desktop .ar-lang module computes correct current-page
	   language association links via mod_languages + Joomla assoc API.
	   We read those links and copy them to the mobile globe dropdown.
	   Matching is done by language name (img alt or span text).       */
	(function syncLangLinks() {
		var desktopLinks = document.querySelectorAll('.ar-lang .dropdown-menu li a');
		var mobileLinks  = document.querySelectorAll('.ar-mobile-lang-dropdown li a');
		if (!desktopLinks.length || !mobileLinks.length) return;

		// Build map: normalised language name → href from desktop
		var map = {};
		desktopLinks.forEach(function (a) {
			var href = a.getAttribute('href');
			if (!href || href === '#') return;
			var img  = a.querySelector('img');
			var span = a.querySelector('span');
			var name = img
				? img.getAttribute('alt')
				: (span ? span.textContent : a.textContent);
			if (name) map[name.trim().toLowerCase()] = href;
		});

		// Apply matched URLs to mobile links
		mobileLinks.forEach(function (a) {
			var name = a.getAttribute('data-lang-name') || '';
			var key  = name.trim().toLowerCase();
			if (key && map[key]) {
				a.setAttribute('href', map[key]);
			}
		});
	}());


	/* ── Mobile account dropdown (logged-in) ───────────────────── */
	document.querySelectorAll('.ar-mob-acct-toggle').forEach(function (btn) {
		btn.addEventListener('click', function (e) {
			e.preventDefault(); e.stopPropagation();
			var wrap   = btn.closest('.ar-mob-acct-wrap');
			if (!wrap) return;
			var isOpen = wrap.classList.contains('open');
			// Close all mobile dropdowns first
			document.querySelectorAll('.ar-mob-acct-wrap.open, .ar-mobile-lang-wrap.open').forEach(function (w) {
				w.classList.remove('open');
			});
			if (!isOpen) {
				wrap.classList.add('open');
				btn.setAttribute('aria-expanded', 'true');
			} else {
				btn.setAttribute('aria-expanded', 'false');
			}
		});
	});

	// Close account dropdown on outside click
	document.addEventListener('click', function (e) {
		if (!e.target.closest('.ar-mob-acct-wrap')) {
			document.querySelectorAll('.ar-mob-acct-wrap.open').forEach(function (w) {
				w.classList.remove('open');
				var b = w.querySelector('.ar-mob-acct-toggle');
				if (b) b.setAttribute('aria-expanded', 'false');
			});
		}
	});

	/* ── Mobile globe toggle ───────────────────────────────────── */
	document.querySelectorAll('.ar-mobile-lang-toggle').forEach(function (btn) {
		btn.addEventListener('click', function (e) {
			e.preventDefault(); e.stopPropagation();
			var wrap   = btn.closest('.ar-mobile-lang-wrap');
			if (!wrap) return;
			var isOpen = wrap.classList.contains('open');
			document.querySelectorAll('.ar-mobile-lang-wrap.open').forEach(function (w) {
				w.classList.remove('open');
				var b = w.querySelector('.ar-mobile-lang-toggle');
				if (b) b.setAttribute('aria-expanded', 'false');
			});
			if (!isOpen) {
				wrap.classList.add('open');
				btn.setAttribute('aria-expanded', 'true');
			}
		});
	});

	/* Close globe dropdown on outside click or Escape */
	document.addEventListener('click', function (e) {
		if (!e.target.closest('.ar-mobile-lang-wrap')) {
			document.querySelectorAll('.ar-mobile-lang-wrap.open').forEach(function (w) {
				w.classList.remove('open');
				var b = w.querySelector('.ar-mobile-lang-toggle');
				if (b) b.setAttribute('aria-expanded', 'false');
			});
		}
	});
	document.addEventListener('keydown', function (e) {
		if (e.key !== 'Escape') return;
		document.querySelectorAll('.ar-mobile-lang-wrap.open').forEach(function (w) {
			w.classList.remove('open');
			var b = w.querySelector('.ar-mobile-lang-toggle');
			if (b) { b.setAttribute('aria-expanded', 'false'); b.focus(); }
		});
	});

});