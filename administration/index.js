"use strict";

/*global data */

const fonctions = document.getElementById('fonctions');


for (let element of data) {
    // Colonne responsive 1-2-3-4 colonnes selon écran
    let col = document.createElement('div');
    col.className = 'col-12 col-sm-4 col-md-3 col-xl-3 d-flex';

    // Carte stylisée
    let a = document.createElement('a');
    a.className = 'admin-card flex-fill';
    a.href = '/administration/' + element.repertoire;
    a.textContent = element.nom;

    // Insertion
    col.appendChild(a);
    fonctions.appendChild(col);
}


