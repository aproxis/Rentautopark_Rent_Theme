jQuery(document).ready(function ($) {
	var gridEl = document.getElementById('ar-grid');

	/* View toggle */
	$('.ar-vbtn').on('click', function () {
		if ($(this).hasClass('active')) return false;
		$('.ar-vbtn').toggleClass('active');
		gridEl.className = $('#ar-list-btn').hasClass('active') ? 'ar-list' : 'ar-grid';
		if ($.cookie) $.cookie('gridlist', gridEl.className === 'ar-list' ? 'list' : 'grid', {path:'/'});
		return false;
	});
	if ($.cookie && $.cookie('gridlist') === 'list') {
		$('#ar-list-btn').addClass('active');
		$('#ar-grid-btn').removeClass('active');
		gridEl.className = 'ar-list';
	}

	/* Live search */
	$('#ar-q').on('input', function() { arSrFilter(null); });
	$('#ar-q-mob').on('input', function () {
		$('#ar-q').val($(this).val());
		arSrFilter(null);
	});

	/* Populate drawer from sidebar (deep-clone, sync both caratid + catid) */
	var sidebar = document.getElementById('ar-sidebar-desktop');
	var drawerBody = document.getElementById('ar-drawer-body');
	if (sidebar && drawerBody) {
		sidebar.querySelectorAll('.ar-fsec').forEach(function (sec) {
			var clone = sec.cloneNode(true);
			clone.querySelectorAll('.ar-cb').forEach(function (cb) {
				cb.addEventListener('change', function () {
					var caratid = this.getAttribute('data-caratid');
					var catid   = this.getAttribute('data-catid');
					var desktopCb = caratid
						? sidebar.querySelector('.ar-cb[data-caratid="'+caratid+'"]')
						: sidebar.querySelector('.ar-cb[data-catid="'+catid+'"]');
					if (desktopCb) desktopCb.checked = this.checked;
					arSrFilter(null);
				});
			});
			drawerBody.appendChild(clone);
		});
	}

	setTimeout(function(){ arSrFilter(null); }, 100);
});

var arSrCurrentSort = 'price-asc';

function arSrToggleSort() {
	document.getElementById('ar-sort-dd').classList.toggle('open');
}
function arSrSort(key, btn) {
	arSrCurrentSort = key;
	document.querySelectorAll('.ar-sb-sort-opt').forEach(function(b){ b.classList.remove('sel'); });
	btn.classList.add('sel');
	arSrFilter(null);
}
function arSrToggleSection(titleEl) {
	var opts  = titleEl.nextElementSibling;
	var arrow = titleEl.querySelector('.ar-ftitle-arrow');
	if (!opts) return;
	var hidden = (opts.style.display === 'none');
	opts.style.display = hidden ? '' : 'none';
	if (arrow) arrow.classList.toggle('collapsed', !hidden);
}
function arSrOpenDrawer() {
	document.getElementById('ar-drawer').classList.add('open');
	document.getElementById('ar-overlay').classList.add('open');
	document.body.style.overflow = 'hidden';
}
function arSrCloseDrawer() {
	document.getElementById('ar-drawer').classList.remove('open');
	document.getElementById('ar-overlay').classList.remove('open');
	document.body.style.overflow = '';
}

function arSrFilter(clickedEl) {
	var qEl = document.getElementById('ar-q');
	var q = (qEl ? qEl.value : '').toLowerCase().trim();

	/* Transmission mutual exclusion */
	if (clickedEl && clickedEl.checked) {
		var grp = clickedEl.getAttribute('data-group');
		if (grp === 'transmission') {
			document.querySelectorAll('.ar-cb[data-group="transmission"]').forEach(function(cb) {
				if (cb.getAttribute('data-caratid') !== clickedEl.getAttribute('data-caratid')) {
					cb.checked = false;
				}
			});
		}
	}

	/* Build groups map: { groupKey: { itemKey: {type, id} } }
	   OR within group, AND between groups */
	var groups = {};
	var sidebar = document.getElementById('ar-sidebar-desktop');
	if (sidebar) {
		sidebar.querySelectorAll('.ar-cb:checked').forEach(function(cb) {
			var grp    = cb.getAttribute('data-group');
			var caratid = cb.getAttribute('data-caratid');
			var catid   = cb.getAttribute('data-catid');
			var key     = caratid ? 'c' + caratid : 'k' + catid;
			if (!groups[grp]) groups[grp] = {};
			groups[grp][key] = { type: caratid ? 'carat' : 'cat', id: caratid || catid };
		});
	}

	var groupKeys = Object.keys(groups);
	var cards = Array.from(document.querySelectorAll('.ar-card'));
	var grid  = document.getElementById('ar-grid');
	var visible = [];

	/* For recounting available filter options */
	var visibleCaratIds = {};
	var visibleCatIds   = {};

	cards.forEach(function(card) {
		var name     = card.getAttribute('data-name') || '';
		var caratIds = (card.getAttribute('data-caratids') || '').replace(/;+$/, '').split(';').filter(Boolean);
		var catIds   = (card.getAttribute('data-catids')   || '').replace(/;+$/, '').split(';').filter(Boolean);

		if (q && name.indexOf(q) === -1) { card.style.display = 'none'; return; }

		/* AND between groups, OR within group */
		var passAll = groupKeys.every(function(grp) {
			return Object.values(groups[grp]).some(function(item) {
				if (item.type === 'carat') return caratIds.indexOf(item.id) !== -1;
				if (item.type === 'cat')   return catIds.indexOf(item.id)   !== -1;
				return false;
			});
		});

		if (passAll) {
			card.style.display = '';
			visible.push(card);
			caratIds.forEach(function(id) { visibleCaratIds[id] = (visibleCaratIds[id] || 0) + 1; });
			catIds.forEach(function(id)   { visibleCatIds[id]   = (visibleCatIds[id]   || 0) + 1; });
		} else {
			card.style.display = 'none';
		}
	});

	/* Sort */
	visible.sort(function(a, b) {
		var pa = parseInt(a.getAttribute('data-price') || 0);
		var pb = parseInt(b.getAttribute('data-price') || 0);
		return arSrCurrentSort === 'price-desc' ? pb - pa : pa - pb;
	});
	visible.forEach(function(c){ grid.appendChild(c); });

	/* Recount filter option labels & dim unavailable */
	document.querySelectorAll('.ar-fopt').forEach(function(opt) {
		var cb = opt.querySelector('.ar-cb');
		if (!cb) return;
		var isCarat = cb.hasAttribute('data-caratid');
		var id      = isCarat ? cb.getAttribute('data-caratid') : cb.getAttribute('data-catid');
		var count   = isCarat ? (visibleCaratIds[id] || 0) : (visibleCatIds[id] || 0);
		var cntSpan = opt.querySelector('.ar-fcnt');
		if (cntSpan) cntSpan.textContent = '(' + count + ')';
		if (count === 0 && !cb.checked) {
			opt.style.opacity = '0.4';
			cb.disabled = true;
		} else {
			opt.style.opacity = '1';
			cb.disabled = false;
		}
	});

	var cEl = document.getElementById('ar-count');
	var countText = cEl ? cEl.getAttribute('data-count-text') : 'automobile gasite';
	if (cEl) cEl.textContent = visible.length + ' ' + countText;
	var emEl = document.getElementById('ar-empty');
	if (emEl) emEl.style.display = visible.length === 0 ? 'block' : 'none';

	arSrUpdateChips();
}

function arSrUpdateChips() {
	var chips = document.getElementById('ar-chips');
	if (!chips) return;
	chips.innerHTML = '';
	var sidebar = document.getElementById('ar-sidebar-desktop');
	if (!sidebar) return;
	sidebar.querySelectorAll('.ar-cb:checked').forEach(function(cb) {
		var label   = cb.getAttribute('data-label') || '';
		var caratid = cb.getAttribute('data-caratid');
		var catid   = cb.getAttribute('data-catid');
		var chip    = document.createElement('span');
		chip.className = 'ar-chip';
		chip.innerHTML = label + '<span class="ar-chip-x">&times;</span>';
		chip.onclick = function() {
			if (caratid) {
				document.querySelectorAll('.ar-cb[data-caratid="'+caratid+'"]')
					.forEach(function(c){ c.checked = false; });
			} else if (catid) {
				document.querySelectorAll('.ar-cb[data-catid="'+catid+'"]')
					.forEach(function(c){ c.checked = false; });
			}
			arSrFilter(null);
		};
		chips.appendChild(chip);
	});
}