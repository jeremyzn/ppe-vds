"use strict";

/**
 * Injecte la feuille de style pour le menu horizontal si elle n'est pas encore chargée.
 */
function injecterStyle() {
    if (!document.getElementById('menuHorizontalStyle')) {
        const link = document.createElement('link');
        link.id = 'menuHorizontalStyle';
        link.rel = 'stylesheet';
        link.href = '/composant/menuhorizontal/menu.css';
        document.head.appendChild(link);
    }
}

/**
 * Génère le HTML du menu horizontal et l'insère en haut de <main>.
 * @param {Array} lesOptions - Tableau d’objets { label, href, sousMenu? }
 */
function genererMenu(lesOptions) {
    const main = document.querySelector('main');
    if (!main) {
        return;
    }

    const nav = document.createElement('nav');
    nav.id = 'menuHorizontal';

    const ul = document.createElement('ul');

    for (const option of lesOptions) {
        const li = document.createElement('li');
        const a = document.createElement('a');
        a.href = option.href;
        a.textContent = option.label;
        // a.classList.add('btn', 'btn-outline-danger', 'me-2'); // spacing horizontal

        // Supprimer les barres obliques au début et à la fin de location.pathname et option.href
        const currentPath = location.pathname.replace(/^\/|\/$/g, '');
        const optionPath = option.href.replace(/^\/|\/$/g, '');

        // Comparer les chemins
        if (currentPath === optionPath) {
            a.classList.add('actif', 'btn-danger'); // btn-outline → btn plein
            a.classList.remove('btn-outline-danger');
        }
        li.appendChild(a);
        ul.appendChild(li);
    }

    nav.appendChild(ul);
    main.insertBefore(nav, main.firstChild);
}

/**
 * Fonction à appeler pour initialiser le menu horizontal.
 * @param {Array} lesOptions - Liste des liens et sous-menus
 */
export function initialiserMenuHorizontal(lesOptions) {
    injecterStyle();
    genererMenu(lesOptions);
}
