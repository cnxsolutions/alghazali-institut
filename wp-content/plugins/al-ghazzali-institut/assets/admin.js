/**
 * Listes répétables des boîtes de saisie : ajouter, monter, descendre ou supprimer une ligne.
 *
 * L'ordre des lignes dans la page est l'ordre enregistré : PHP renumérote à la sauvegarde.
 */
(() => {
	'use strict';

	// Index unique des nouvelles lignes (les lignes existantes sont numérotées 0, 1, 2…).
	let counter = Date.now();

	document.addEventListener('click', (event) => {
		const button = event.target instanceof Element ? event.target.closest('[data-ag-add], [data-ag-move], [data-ag-remove]') : null;
		const repeater = button?.closest('[data-ag-repeater]');
		const rows = repeater?.querySelector('[data-ag-rows]');
		if (!button || !rows) {
			return;
		}
		event.preventDefault();

		if (button.hasAttribute('data-ag-add')) {
			const template = repeater.querySelector('[data-ag-template]');
			counter += 1;
			rows.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__i__', String(counter)));
			rows.lastElementChild?.querySelector('input, textarea, select')?.focus();
			return;
		}

		const row = button.closest('.ag-repeater__row');
		if (!row) {
			return;
		}
		if (button.hasAttribute('data-ag-remove')) {
			const next = row.nextElementSibling || row.previousElementSibling;
			row.remove();
			(next?.querySelector('input, textarea, select') || repeater.querySelector('[data-ag-add]'))?.focus();
			return;
		}

		const up = button.dataset.agMove === '-1';
		const sibling = up ? row.previousElementSibling : row.nextElementSibling;
		if (sibling) {
			sibling[up ? 'before' : 'after'](row);
			button.focus();
		}
	});
})();
