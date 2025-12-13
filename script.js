// Navigation horizontal scroll controls & header scroll state
document.addEventListener('DOMContentLoaded', () => {
	const header = document.getElementById('main-header');
	const nav = document.getElementById('nav-scroll');
	const leftBtn = document.getElementById('nav-scroll-left');
	const rightBtn = document.getElementById('nav-scroll-right');

	function updateHeader() {
		if (window.scrollY > 10) {
			header.classList.add('scrolled');
		} else {
			header.classList.remove('scrolled');
		}
	}

	function updateNavButtons() {
		if (!nav) return;
		const max = nav.scrollWidth - nav.clientWidth;
		const atStart = nav.scrollLeft <= 0;
		const atEnd = nav.scrollLeft >= max - 1;
		if (leftBtn) leftBtn.hidden = atStart;
		if (rightBtn) rightBtn.hidden = atEnd;
	}

	function scrollNav(direction) {
		if (!nav) return;
		nav.scrollBy({ left: direction * 220, behavior: 'smooth' });
	}

	if (leftBtn) leftBtn.addEventListener('click', () => scrollNav(-1));
	if (rightBtn) rightBtn.addEventListener('click', () => scrollNav(1));
	if (nav) {
		nav.addEventListener('scroll', updateNavButtons);
		nav.addEventListener('keydown', (e) => {
			if (e.key === 'ArrowRight') { scrollNav(1); e.preventDefault(); }
			if (e.key === 'ArrowLeft') { scrollNav(-1); e.preventDefault(); }
		});
	}
	window.addEventListener('resize', updateNavButtons);
	window.addEventListener('scroll', updateHeader, { passive: true });

	updateHeader();
	updateNavButtons();

	// ============================================
	// Console Access Dropdown
	// Sprint 1.6 QA: New fold-out menu
	// ============================================
	const consoleDropdown = document.querySelector('.console-access-dropdown');
	const consoleTrigger = document.querySelector('.console-access-trigger');
	const consoleMenu = document.querySelector('.console-access-menu');

	if (consoleTrigger && consoleDropdown) {
		// Initialize aria-expanded
		consoleDropdown.setAttribute('aria-expanded', 'false');
		consoleTrigger.setAttribute('aria-expanded', 'false');

		consoleTrigger.addEventListener('click', (e) => {
			e.preventDefault();
			e.stopPropagation();
			const isExpanded = consoleDropdown.getAttribute('aria-expanded') === 'true';
			const newState = isExpanded ? 'false' : 'true';
			consoleDropdown.setAttribute('aria-expanded', newState);
			consoleTrigger.setAttribute('aria-expanded', newState);
			console.log('Console dropdown toggled:', newState);
		});

		// Close on outside click
		document.addEventListener('click', (e) => {
			if (!consoleDropdown.contains(e.target)) {
				consoleDropdown.setAttribute('aria-expanded', 'false');
				consoleTrigger.setAttribute('aria-expanded', 'false');
			}
		});

		// Close on escape
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape') {
				consoleDropdown.setAttribute('aria-expanded', 'false');
				consoleTrigger.setAttribute('aria-expanded', 'false');
			}
		});
	}
});
