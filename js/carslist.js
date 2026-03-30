jQuery(document).ready(function ($) {
	/* ---- View toggle + cookie ---- */
	var gridEl = document.getElementById('ar-grid');
	$('.ar-vbtn').on('click', function () {
		if ($(this).hasClass('active')) return false;
		$('.ar-vbtn').toggleClass('active');
		if ($('#ar-list-btn').hasClass('active')) {
			gridEl.className = 'ar-list';
			$.cookie && $.cookie('gridlist', 'list', { path: '/' });
		} else {
			gridEl.className = 'ar-grid';
			$.cookie && $.cookie('gridlist', 'grid', { path: '/' });
		}
		return false;
	});
	if ($.cookie && $.cookie('gridlist') === 'list') {
		$('#ar-list-btn').addClass('active');
		$('#ar-grid-btn').removeClass('active');
		gridEl.className = 'ar-list';
	}

	/* ---- Live search ---- */
	$('#ar-q').on('input', arFilter);
	$('#ar-q-mob').on('input', function () {
		$('#ar-q').val($(this).val());
		arFilter();
	});

	/* ---- Populate drawer from sidebar HTML ---- */
	var sidebarContent = document.getElementById('ar-sidebar-desktop');
	if (sidebarContent) {
		var drawerBody = document.getElementById('ar-drawer-body');
		if (drawerBody) {
			var sections = sidebarContent.querySelectorAll('.ar-fsec');
			sections.forEach(function (sec) {
				var clone = sec.cloneNode(true);
				clone.querySelectorAll('.ar-cb').forEach(function (cb) {
					cb.addEventListener('change', function () {
						var caratid = this.getAttribute('data-caratid');
						var catid   = this.getAttribute('data-catid');
						var desktopCb = caratid
							? sidebarContent.querySelector('.ar-cb[data-caratid="'+caratid+'"]')
							: sidebarContent.querySelector('.ar-cb[data-catid="'+catid+'"]');
						if (desktopCb) desktopCb.checked = this.checked;
						arFilter();
					});
				});
				drawerBody.appendChild(clone);
			});
		}
	}

	setTimeout(function() { arFilter(); }, 100);
});

/* Sort */
var arCurrentSort = 'price-asc';

function arSortToggle() {
    var btn = document.querySelector('.ar-sort-toggle');
    if (arCurrentSort === 'price-asc') {
        arCurrentSort = 'price-desc';
        var lbl = btn ? btn.getAttribute('data-label-desc') : 'Preț: Mare → Mic';
        document.querySelectorAll('.ar-sort-toggle-lbl').forEach(function(el) { el.textContent = lbl; });
        document.querySelectorAll('.ar-sort-icon').forEach(function(el) {
            el.className = 'fa fa-arrow-down ar-sort-icon';
        });
    } else {
        arCurrentSort = 'price-asc';
        var lbl = btn ? btn.getAttribute('data-label-asc') : 'Preț: Mic → Mare';
        document.querySelectorAll('.ar-sort-toggle-lbl').forEach(function(el) { el.textContent = lbl; });
        document.querySelectorAll('.ar-sort-icon').forEach(function(el) {
            el.className = 'fa fa-arrow-up ar-sort-icon';
        });
    }
    arFilter();
}

function arToggleSection(titleEl) {
	var opts = titleEl.nextElementSibling;
	var arrow = titleEl.querySelector('.ar-ftitle-arrow');
	if (!opts) return;
	var hidden = (opts.style.display === 'none');
	opts.style.display = hidden ? '' : 'none';
	if (arrow) arrow.classList.toggle('collapsed', !hidden);
}
function arOpenDrawer() {
	document.getElementById('ar-drawer').classList.add('open');
	document.getElementById('ar-overlay').classList.add('open');
	document.body.style.overflow = 'hidden';
}
function arCloseDrawer() {
	document.getElementById('ar-drawer').classList.remove('open');
	document.getElementById('ar-overlay').classList.remove('open');
	document.body.style.overflow = '';
}

function arFilter(clickedEl) {
	var qEl = document.getElementById('ar-q');
	var q = (qEl ? qEl.value : '').toLowerCase().trim();

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

	var groups = {};
	document.querySelectorAll('.ar-cb:checked').forEach(function (cb) {
		var grp = cb.getAttribute('data-group');
		var caratid = cb.getAttribute('data-caratid');
		var catid = cb.getAttribute('data-catid');
		var key = caratid ? 'c' + caratid : 'k' + catid;
		if (!groups[grp]) groups[grp] = {};
		groups[grp][key] = { type: caratid ? 'carat' : 'cat', id: caratid || catid };
	});

	var groupKeys = Object.keys(groups);
	var cards = Array.from(document.querySelectorAll('.ar-card'));
	var grid = document.getElementById('ar-grid');
	var visible = [];
	var visibleCaratIds = {};
	var visibleCatIds = {};

	cards.forEach(function (card) {
		var name = card.getAttribute('data-name') || '';
		var caratIds = (card.getAttribute('data-caratids') || '').replace(/;+$/, '').split(';').filter(Boolean);
		var catIds = (card.getAttribute('data-catids') || '').replace(/;+$/, '').split(';').filter(Boolean);
		if (q && name.indexOf(q) === -1) { card.style.display = 'none'; return; }
		var passAll = groupKeys.every(function (grp) {
			return Object.values(groups[grp]).some(function (item) {
				if (item.type === 'carat') return caratIds.indexOf(item.id) !== -1;
				if (item.type === 'cat')   return catIds.indexOf(item.id) !== -1;
				return false;
			});
		});
		if (passAll) {
			card.style.display = '';
			visible.push(card);
			caratIds.forEach(function(id) { visibleCaratIds[id] = (visibleCaratIds[id] || 0) + 1; });
			catIds.forEach(function(id) { visibleCatIds[id] = (visibleCatIds[id] || 0) + 1; });
		} else { card.style.display = 'none'; }
	});

	visible.sort(function (a, b) {
		if (arCurrentSort === 'price-asc') return parseInt(a.getAttribute('data-price')||0) - parseInt(b.getAttribute('data-price')||0);
		if (arCurrentSort === 'price-desc') return parseInt(b.getAttribute('data-price')||0) - parseInt(a.getAttribute('data-price')||0);
		return 0;
	});
	visible.forEach(function (card) { grid.appendChild(card); });

	document.querySelectorAll('.ar-fopt').forEach(function(opt) {
		var cb = opt.querySelector('.ar-cb');
		if (!cb) return;
		var isCarat = cb.hasAttribute('data-caratid');
		var id = isCarat ? cb.getAttribute('data-caratid') : cb.getAttribute('data-catid');
		var count = isCarat ? (visibleCaratIds[id] || 0) : (visibleCatIds[id] || 0);
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

	var countText = document.getElementById('ar-count')
	    ? document.getElementById('ar-count').getAttribute('data-count-text')
	    : 'automobile gasite';
	['ar-count', 'ar-count-mob'].forEach(function(id) {
	    var el = document.getElementById(id);
	    if (el) el.textContent = visible.length + ' ' + countText;
	});
	document.getElementById('ar-empty').style.display = visible.length === 0 ? 'block' : 'none';
	arUpdateChips();
}

function arUpdateChips() {
    var chips    = document.getElementById('ar-chips');
    var chipsMob = document.getElementById('ar-chips-mob');
    if (chips)    chips.innerHTML = '';
    if (chipsMob) chipsMob.innerHTML = '';

    document.querySelectorAll('#ar-sidebar-desktop .ar-cb:checked').forEach(function (cb) {
        var label   = cb.getAttribute('data-label') || '';
        var caratid = cb.getAttribute('data-caratid');
        var catid   = cb.getAttribute('data-catid');

        [chips, chipsMob].forEach(function(container) {
            if (!container) return;
            var chip = document.createElement('span');
            chip.className = 'ar-chip';
            chip.innerHTML = label + '<span class="ar-chip-x">×</span>';
            chip.onclick = function () {
                if (caratid) {
                    document.querySelectorAll('.ar-cb[data-caratid="'+caratid+'"]')
                        .forEach(function(c){ c.checked = false; });
                } else if (catid) {
                    document.querySelectorAll('.ar-cb[data-catid="'+catid+'"]')
                        .forEach(function(c){ c.checked = false; });
                }
                arFilter();
            };
            container.appendChild(chip);
        });
    });
}
