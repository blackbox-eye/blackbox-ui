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
		// Initialize aria-expanded on BUTTON only (ARIA compliant - aria-expanded is invalid on div)
		consoleTrigger.setAttribute('aria-expanded', 'false');
		// Use CSS class for styling hooks instead of aria-expanded on container
		consoleDropdown.classList.remove('is-open');

		consoleTrigger.addEventListener('click', (e) => {
			e.preventDefault();
			e.stopPropagation();
			const isExpanded = consoleTrigger.getAttribute('aria-expanded') === 'true';
			const newState = isExpanded ? 'false' : 'true';
			consoleTrigger.setAttribute('aria-expanded', newState);
			consoleDropdown.classList.toggle('is-open', newState === 'true');
			console.log('Console dropdown toggled:', newState);
		});

		// Close on outside click
		document.addEventListener('click', (e) => {
			if (!consoleDropdown.contains(e.target)) {
				consoleTrigger.setAttribute('aria-expanded', 'false');
				consoleDropdown.classList.remove('is-open');
			}
		});

		// Close on escape
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape') {
				consoleTrigger.setAttribute('aria-expanded', 'false');
				consoleDropdown.classList.remove('is-open');
			}
		});
	}
});
