"use strict";

function injecterStyle() {
    if (!document.getElementById('menuHorizontalStyle')) {
        const link = document.createElement('link');
        link.id = 'menuHorizontalStyle';
        link.rel = 'stylesheet';
        link.href = '/composant/menuhorizontal/menu.css';
        document.head.appendChild(link);
    }
}

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

        const currentPath = location.pathname.replace(/^\/|\/$/g, '');
        const optionPath = option.href.replace(/^\/|\/$/g, '');

        // Applique la classe sur le <li> (et non sur le <a>)
        if (currentPath === optionPath) {
            li.classList.add('actif');
        }

        li.appendChild(a);
        ul.appendChild(li);
    }

    nav.appendChild(ul);
    main.insertBefore(nav, main.firstChild);
}

export function initialiserMenuHorizontal(lesOptions) {
    injecterStyle();
    genererMenu(lesOptions);
}
