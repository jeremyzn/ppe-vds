"use strict";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------

/* global lesDocuments */

const lesLignes = document.getElementById('lesLignes');

// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------

// afficher le tableau des documents

for (const element of lesDocuments) {
    let tr = lesLignes.insertRow();
    tr.style.verticalAlign = 'middle';
    // colonne : voir (si le fichier existe)
    let td = tr.insertCell();
    if (element.present) {
        let a = document.createElement('a');
        a.href = "afficher.php?id=" + element.id;
        a.target = 'pdf';
        a.innerText = '📄';
        td.appendChild(a);
    } else {
        td.innerText = '❓';
        console.log("Le classement " + element.id + " n'a pas été trouvé");
    }
    // colonne : le titre du document
    tr.insertCell().innerText = element.titre;
    // colonne : le type de document
    tr.insertCell().innerText = element.type;
    // colonne fichier
    tr.insertCell().innerText = element.fichier;
}
