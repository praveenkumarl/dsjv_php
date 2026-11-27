//to avoid session timout, send a msg to server
var refreshTime = 600000; // every 10 minutes in milliseconds
window.setInterval( function() {
$.ajax({
	cache: false,
	type: "GET",
	url: "refreshSession.php",
	success: function(data) {
	}
});
}, refreshTime );

// Language selector helpers
function setLanguage(lang) {
	// set cookie for 1 year
	document.cookie = 'language=' + lang + ';path=/;max-age=' + (60*60*24*365);
	// update visible label if present
	var lbl = document.getElementById('currentLangLabel');
	if(lbl) lbl.textContent = (lang === 'english') ? 'EN' : 'தமிழ்';
	var message = (lang === 'english') ? 'Language changed to English' : 'தமிழ் மொழிக்கு மாற்றப்பட்டது';
	// Inform server (if logged-in) so DB and session are updated, then reload
	try {
		fetch('set_user_lang.php', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: 'lang=' + encodeURIComponent(lang)
		}).then(function(resp){
			// Try to parse response and show toast, then reload shortly so user sees message
			resp.json().then(function(json){
				showToast(message, 900);
				setTimeout(function(){ window.location.reload(); }, 900);
			}).catch(function(){
				// fallback
				showToast(message, 700);
				setTimeout(function(){ window.location.reload(); }, 700);
			});
		}).catch(function(){
			// network error
			showToast(message, 700);
			setTimeout(function(){ window.location.reload(); }, 700);
		});
	} catch (e) {
		window.location.reload();
	}
}

// Simple toast utility
function showToast(message, duration) {
	duration = duration || 1500;
	var existing = document.getElementById('siteToast');
	if (existing) existing.remove();
	var t = document.createElement('div');
	t.id = 'siteToast';
	t.className = 'site-toast';
	t.textContent = message;
	document.body.appendChild(t);
	// show animation
	requestAnimationFrame(function(){
		t.classList.add('visible');
	});
	setTimeout(function(){
		t.classList.remove('visible');
		setTimeout(function(){ if(t.parentNode) t.parentNode.removeChild(t); }, 300);
	}, duration);
}

function getCookie(name){
	var m = document.cookie.match('(^|;)\\s*'+name+'\\s*=\\s*([^;]+)');
	return m ? m.pop() : '';
}

function toggleLanguage(){
	var cur = getCookie('language') || 'tamil';
	var next = (cur === 'english') ? 'tamil' : 'english';
	setLanguage(next);
}

function toggleLangDropdown() {
	var dd = document.getElementById('langDropdown');
	if(!dd) return;
	dd.style.display = (dd.style.display === 'block') ? 'none' : 'block';
}

// close dropdown on outside click
document.addEventListener('click', function(e){
	var dd = document.getElementById('langDropdown');
	var btn = document.querySelector('.lang-btn');
	if(!dd || !btn) return;
	if(!btn.contains(e.target) && !dd.contains(e.target)) {
		dd.style.display = 'none';
	}
});

// Inject compact language links for mobile navbars (if not already present)
// Modern mobile FAB language control
document.addEventListener('DOMContentLoaded', function(){
	var columnMenu = document.querySelector('.column-menu');
	var appTopbar = columnMenu && columnMenu.querySelector('.app-topbar');
	if (appTopbar && appTopbar.parentElement !== columnMenu) {
		columnMenu.insertBefore(appTopbar, columnMenu.firstElementChild);
	}

	var pagePanel = document.querySelector('.app-page-panel');
	var pagePath = window.location.pathname.split('/').pop().toLowerCase();
	var currentLanguage = getCookie('language') || 'tamil';
	var isEnglish = currentLanguage === 'english';
	var isIndexPage = document.body.classList.contains('theme-index-preview')
		|| pagePath === 'index.php'
		|| pagePath === 'indexmobile.php';
	if (!pagePanel && !isIndexPage && !document.getElementById('panchangBody')
		&& document.querySelector('.column-menu, .column-body')) {
		pagePanel = document.createElement('nav');
		pagePanel.className = 'app-page-panel';
		pagePanel.setAttribute('aria-label', isEnglish ? 'Page actions' : 'பக்க செயல்கள்');
		var homeLink = document.createElement('a');
		homeLink.className = 'app-panel-home';
		homeLink.href = 'index.php';
		homeLink.setAttribute('aria-label', isEnglish ? 'Home' : 'முகப்பு');
		homeLink.title = isEnglish ? 'Home' : 'முகப்பு';
		homeLink.innerHTML = '<i class="fa fa-home" aria-hidden="true"></i>';
		pagePanel.appendChild(homeLink);
		document.body.appendChild(pagePanel);
	}

	if (pagePanel) {
		if (pagePanel.classList.contains('app-page-panel-clean-nav')) {
			document.body.classList.add('app-overlay-navigation');
		}
		if (pagePanel.parentElement !== document.body) {
			document.body.appendChild(pagePanel);
		}
		document.body.classList.add('has-app-page-panel');

		var pdfTrigger = null;
		var pdfCandidates = document.querySelectorAll('a, button, input[type="button"], input[type="submit"]');
		for (var i = 0; i < pdfCandidates.length; i++) {
			var candidate = pdfCandidates[i];
			if (candidate.closest('.app-page-panel') || candidate.disabled) continue;
			var description = [
				candidate.id,
				candidate.getAttribute('aria-label'),
				candidate.getAttribute('title'),
				candidate.value,
				candidate.textContent,
				candidate.getAttribute('onclick'),
				candidate.getAttribute('href')
			].join(' ');
			if (/pdf|html2pdf|generatepdf/i.test(description)) {
				pdfTrigger = candidate;
				break;
			}
		}

		if (pdfTrigger && !pagePanel.querySelector('.app-panel-download')) {
			var downloadLabel = isEnglish ? 'Download PDF' : 'PDF பதிவிறக்கம்';
			var downloadButton = document.createElement('button');
			downloadButton.type = 'button';
			downloadButton.className = 'app-panel-download';
			downloadButton.setAttribute('aria-label', downloadLabel);
			downloadButton.setAttribute('title', downloadLabel);
			downloadButton.innerHTML = '<span class="app-pdf-download-icon" aria-hidden="true"><i class="fa fa-file-pdf-o app-pdf-download-file"></i><i class="fa fa-download app-pdf-download-arrow"></i></span>';
			downloadButton.addEventListener('click', function(){
				pdfTrigger.click();
			});
			pagePanel.appendChild(downloadButton);
		}
	}

	if(document.querySelector('#menuPanel .lang-selector')) return;
	if(document.getElementById('panchangBody')) return;
	if(document.querySelector('#panchangDock .panchang-dock-language')) return;
	if(document.querySelector('.app-topbar .language-switch')) return;

	// Only add on small screens
	if(window.innerWidth > 768) return;

	// avoid duplicate
	if(document.getElementById('fabLang')) return;

	// Create FAB container
	var fab = document.createElement('div');
	fab.id = 'fabLang';
	fab.className = 'fab-lang';

	// Button
	var btn = document.createElement('button');
	btn.className = 'fab-btn';
	btn.setAttribute('aria-label','Language / மொழி');
	btn.setAttribute('title','Language / மொழி');
	btn.innerHTML = '<i class="fa fa-language" aria-hidden="true"></i>';
	fab.appendChild(btn);

	// Popover
	var pop = document.createElement('div');
	pop.className = 'fab-popover';

	// Determine current language from cookie or global var
	var curLang = getCookie('language') || (typeof currentLanguage !== 'undefined' ? currentLanguage : 'tamil');

	// Build options so we can mark the selected one
	var btnEn = document.createElement('button');
	btnEn.className = 'fab-option';
	btnEn.setAttribute('data-lang','english');
	btnEn.textContent = 'EN';
	if(curLang === 'english') btnEn.classList.add('selected');

	var btnTa = document.createElement('button');
	btnTa.className = 'fab-option';
	btnTa.setAttribute('data-lang','tamil');
	btnTa.textContent = 'தமிழ்';
	if(curLang === 'tamil') btnTa.classList.add('selected');

	pop.appendChild(btnEn);
	pop.appendChild(btnTa);
	fab.appendChild(pop);

	var menuContainer = document.querySelector('#appMenu .mobile')
		|| document.querySelector('#appMenu')
		|| document.querySelector('.column-menu .mobile')
		|| document.querySelector('.column-menu');

	if (menuContainer) {
		fab.classList.add('fab-inline');
		menuContainer.appendChild(fab);
	} else {
		document.body.appendChild(fab);
	}

	// Toggle popover
	btn.addEventListener('click', function(e){
		e.stopPropagation();
		pop.classList.toggle('open');
	});

	// option clicks
	pop.addEventListener('click', function(e){
		var t = e.target;
		if(t && t.classList.contains('fab-option')){
			var lang = t.getAttribute('data-lang');
			setLanguage(lang);
		}
	});

	// close when tapping outside
	document.addEventListener('click', function(e){
		if(!fab.contains(e.target)) pop.classList.remove('open');
	});
});