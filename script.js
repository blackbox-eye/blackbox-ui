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
});
