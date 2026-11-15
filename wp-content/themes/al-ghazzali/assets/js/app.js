/**
 * Al Ghazzali Institut : interactions du site (JavaScript natif, sans dépendance).
 *
 * Chaque module est indépendant, ne fait rien si ses éléments sont absents
 * et respecte la préférence « mouvement réduit ». Les textes affichés viennent
 * des attributs data-* posés par PHP (traduisibles), jamais de ce fichier.
 */
(() => {
	'use strict';

	const root = document.documentElement;
	const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

	/** En-tête : fond au défilement, masqué en descendant, réaffiché en remontant. */
	function initHeader() {
		const header = document.querySelector('[data-header]');
		const backToTop = document.querySelector('[data-back-to-top]');
		if (!header) {
			return;
		}

		let lastY = window.scrollY;
		let ticking = false;

		const update = () => {
			const y = window.scrollY;
			const goingDown = y > lastY && y > 320;
			header.classList.toggle('is-scrolled', y > 24);
			header.classList.toggle('is-hidden', goingDown && !root.classList.contains('nav-open') && !header.contains(document.activeElement));
			backToTop?.classList.toggle('is-visible', y > 900);
			lastY = y;
			ticking = false;
		};

		window.addEventListener('scroll', () => {
			if (!ticking) {
				ticking = true;
				requestAnimationFrame(update);
			}
		}, { passive: true });
		header.addEventListener('focusin', () => header.classList.remove('is-hidden'));
		update();

		backToTop?.addEventListener('click', () => {
			window.scrollTo({ top: 0, behavior: reducedMotion ? 'auto' : 'smooth' });
			document.getElementById('main')?.focus({ preventScroll: true });
		});
	}

	/** Menu mobile plein écran + sous-menus dépliables, utilisables au clavier. */
	function initNav() {
		const toggle = document.querySelector('[data-nav-toggle]');
		const nav = document.querySelector('[data-nav]');
		if (!toggle || !nav) {
			return;
		}

		const label = toggle.querySelector('.screen-reader-text');
		const desktop = window.matchMedia('(min-width: 67.5em)');

		const setOpen = (open) => {
			root.classList.toggle('nav-open', open);
			toggle.setAttribute('aria-expanded', String(open));
			if (label) {
				label.textContent = open ? toggle.dataset.labelClose : toggle.dataset.labelOpen;
			}
			if (open) {
				nav.querySelector('a')?.focus({ preventScroll: true });
			}
		};

		toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));
		nav.addEventListener('click', (event) => {
			if (event.target.closest('a')) {
				setOpen(false);
			}
		});
		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && root.classList.contains('nav-open')) {
				setOpen(false);
				toggle.focus();
			}
		});
		desktop.addEventListener('change', (event) => {
			if (event.matches) {
				setOpen(false);
			}
		});

		nav.querySelectorAll('.menu-item-has-children').forEach((item, index) => {
			const link = item.querySelector(':scope > a');
			const submenu = item.querySelector(':scope > .sub-menu');
			if (!link || !submenu) {
				return;
			}
			submenu.id = submenu.id || `submenu-${index}`;

			const button = document.createElement('button');
			button.type = 'button';
			button.className = 'submenu-toggle';
			button.setAttribute('aria-expanded', 'false');
			button.setAttribute('aria-controls', submenu.id);
			button.setAttribute('aria-label', `${toggle.dataset.labelSubmenu} ${link.textContent.trim()}`);
			button.addEventListener('click', () => {
				button.setAttribute('aria-expanded', String(item.classList.toggle('is-open')));
			});
			link.after(button);
		});
	}

	/** Apparition des blocs au défilement (data-reveal) et tracé de la frise des étapes. */
	function initReveal() {
		const items = document.querySelectorAll('[data-reveal]');
		const steps = document.querySelectorAll('[data-steps]');
		root.classList.add('reveal-on');

		if (reducedMotion || !('IntersectionObserver' in window)) {
			items.forEach((item) => item.classList.add('is-visible'));
			steps.forEach((list) => list.classList.add('is-drawn'));
			return;
		}

		const observer = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (!entry.isIntersecting) {
					return;
				}
				entry.target.classList.add(entry.target.hasAttribute('data-steps') ? 'is-drawn' : 'is-visible');
				observer.unobserve(entry.target);
			});
		}, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });

		items.forEach((item) => observer.observe(item));
		steps.forEach((list) => observer.observe(list));
	}

	/** Compteurs animés de 0 à leur valeur (data-count), une seule fois. */
	function initCounters() {
		const counters = document.querySelectorAll('[data-count]');
		if (reducedMotion || !counters.length || !('IntersectionObserver' in window)) {
			return;
		}

		const format = new Intl.NumberFormat('fr-FR');
		const animate = (element) => {
			const target = Number.parseInt(element.dataset.count, 10) || 0;
			const start = performance.now();
			const duration = 1600;
			const tick = (now) => {
				const progress = Math.min((now - start) / duration, 1);
				element.textContent = format.format(Math.round(target * (1 - (1 - progress) ** 3)));
				if (progress < 1) {
					requestAnimationFrame(tick);
				}
			};
			requestAnimationFrame(tick);
		};

		const observer = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					observer.unobserve(entry.target);
					animate(entry.target);
				}
			});
		}, { threshold: 0.6 });
		counters.forEach((counter) => observer.observe(counter));
	}

	/** Halo lumineux qui suit le pointeur sur les cartes (data-spotlight). */
	function initSpotlight() {
		if (reducedMotion || !finePointer) {
			return;
		}
		document.addEventListener('pointermove', (event) => {
			const card = event.target instanceof Element ? event.target.closest('[data-spotlight]') : null;
			if (!card) {
				return;
			}
			const rect = card.getBoundingClientRect();
			card.style.setProperty('--mx', `${event.clientX - rect.left}px`);
			card.style.setProperty('--my', `${event.clientY - rect.top}px`);
		}, { passive: true });
	}

	/** Légère profondeur du visuel d'accueil selon la position du pointeur. */
	function initHeroParallax() {
		const hero = document.querySelector('.hero');
		if (!hero || reducedMotion || !finePointer) {
			return;
		}
		let frame = 0;
		hero.addEventListener('pointermove', (event) => {
			cancelAnimationFrame(frame);
			frame = requestAnimationFrame(() => {
				const rect = hero.getBoundingClientRect();
				hero.style.setProperty('--px', ((event.clientX - rect.left) / rect.width - 0.5).toFixed(3));
				hero.style.setProperty('--py', ((event.clientY - rect.top) / rect.height - 0.5).toFixed(3));
			});
		}, { passive: true });
	}

	/**
	 * Filtres instantanés : plusieurs groupes (data-filter-group = id de la grille) se combinent.
	 * Un élément reste visible s'il porte la valeur choisie dans chaque groupe (data-tags).
	 * Un groupe muni de data-filter-param se présélectionne par l'URL (ex. ?classe=femmes).
	 * Avec View Transitions, les éléments glissent vers leur nouvelle place.
	 */
	function initFilters() {
		const groups = [...document.querySelectorAll('[data-filter-group]')];
		const params = new URLSearchParams(window.location.search);

		[...new Set(groups.map((group) => group.dataset.filterGroup))].forEach((id) => {
			const target = document.getElementById(id);
			if (!target) {
				return;
			}

			const controls = groups.filter((group) => group.dataset.filterGroup === id);
			const items = [...target.querySelectorAll('[data-tags]')];
			const days = [...target.querySelectorAll('.week__day')];
			const empty = document.querySelector(`[data-filter-empty="${CSS.escape(id)}"]`);
			const choices = new Map(controls.map((group) => [group, '*']));

			const apply = () => {
				const wanted = [...choices.values()].filter((value) => value !== '*');
				items.forEach((item) => {
					const tags = (item.dataset.tags || '').split(' ');
					item.classList.toggle('is-filtered-out', !wanted.every((value) => tags.includes(value)));
					item.classList.add('is-visible');
				});
				days.forEach((day) => {
					day.hidden = !day.querySelector('[data-tags]:not(.is-filtered-out)');
				});
				if (empty) {
					empty.hidden = items.some((item) => !item.classList.contains('is-filtered-out'));
				}
			};

			const press = (group, button) => {
				group.querySelectorAll('[data-filter]').forEach((other) => {
					other.classList.toggle('is-active', other === button);
					other.setAttribute('aria-pressed', String(other === button));
				});
				choices.set(group, button.dataset.filter);
			};

			const animate = () => {
				if (reducedMotion || !document.startViewTransition) {
					apply();
					return;
				}
				items.forEach((item, index) => {
					item.style.viewTransitionName = `ag-${id}-${index}`;
				});
				document.startViewTransition(apply).finished.finally(() => {
					items.forEach((item) => {
						item.style.viewTransitionName = '';
					});
				});
			};

			controls.forEach((group) => {
				const buttons = [...group.querySelectorAll('[data-filter]')];
				buttons.forEach((button) => button.addEventListener('click', () => {
					press(group, button);
					animate();
				}));

				const preset = buttons.find((button) => button.dataset.filter === params.get(group.dataset.filterParam || ''));
				if (preset) {
					press(group, preset);
				}
			});
			if ([...choices.values()].some((value) => value !== '*')) {
				apply();
			}
		});
	}

	/**
	 * Onglets (data-tabs) : la liste d'ancres devient une barre d'onglets utilisable au clavier.
	 * Sans JavaScript, les ancres mènent à des panneaux tous affichés.
	 */
	function initTabs() {
		document.querySelectorAll('[data-tabs]').forEach((root) => {
			const tabs = [...root.querySelectorAll('[data-tab]')];
			const panels = tabs.map((tab) => document.getElementById(tab.hash.slice(1)));
			if (tabs.length < 2 || panels.some((panel) => !panel)) {
				return;
			}

			tabs[0].parentElement.setAttribute('role', 'tablist');
			tabs.forEach((tab, index) => {
				const panel = panels[index];
				tab.id = tab.id || `${panel.id}-tab`;
				tab.setAttribute('role', 'tab');
				tab.setAttribute('aria-controls', panel.id);
				panel.setAttribute('role', 'tabpanel');
				panel.setAttribute('aria-labelledby', tab.id);
				panel.tabIndex = 0;
				// Un panneau masqué n'est jamais observé par l'apparition au défilement.
				panel.classList.add('is-visible');
			});

			const select = (index, focus) => {
				tabs.forEach((tab, i) => {
					const selected = i === index;
					tab.setAttribute('aria-selected', String(selected));
					tab.tabIndex = selected ? 0 : -1;
					tab.classList.toggle('is-active', selected);
					panels[i].hidden = !selected;
				});
				if (focus) {
					tabs[index].focus();
				}
			};

			tabs.forEach((tab, index) => {
				tab.addEventListener('click', (event) => {
					event.preventDefault();
					select(index, false);
				});
				tab.addEventListener('keydown', (event) => {
					const targets = { ArrowRight: index + 1, ArrowLeft: index - 1, Home: 0, End: tabs.length - 1 };
					if (!(event.key in targets)) {
						return;
					}
					event.preventDefault();
					select((targets[event.key] + tabs.length) % tabs.length, true);
				});
			});

			const fromHash = panels.findIndex((panel) => `#${panel.id}` === window.location.hash);
			select(Math.max(fromHash, 0), false);
		});
	}

	initHeader();
	initNav();
	initReveal();
	initCounters();
	initSpotlight();
	initHeroParallax();
	initFilters();
	initTabs();
})();
