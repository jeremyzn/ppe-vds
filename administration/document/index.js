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


    // cellule contenant "voir" + "modifier" + "supprimer"
    let td = tr.insertCell();


    if (element.present) {
        let view = document.createElement('a');
        view.href = "afficher.php?id=" + element.id;
        view.target = 'pdf';
        view.innerText = '📄';
        view.className = 'doc-link';
        td.appendChild(view);
    } else {
        let missing = document.createElement('span');
        missing.innerText = '❓';
        missing.href = "afficher.php?id=" + element.id;
        missing.className = 'doc-missing';
        td.appendChild(missing);
        console.log("Le document " + element.id + " n'a pas été trouvé");
    }

// séparateur puis lien modifier
    let sep = document.createElement('span');
    sep.innerText = ' '; // ou '\u00A0' pour insécable
    td.appendChild(sep);

    let modif = document.createElement('a');
    modif.href = 'maj.php?id=' + element.id; // adapter l'URL de modification
    modif.target = 'pdf';
    modif.innerText = '✏️';
    modif.className = 'modif-link';
    td.appendChild(modif);

// séparateur puis lien supprimer
    let sep2 = document.createElement('span');
    sep2.innerText = ' '; // ou '\u00A0' pour insécable
    td.appendChild(sep2);

    let sup = document.createElement('a');
    sup.href = 'supprimer.php?id=' + element.id; // adapter l'URL de suppression
    sup.target = '_self';
    sup.innerText = '❌';
    sup.className = 'sup-link';
    td.appendChild(sup);
    // colonne : le titre du document
    tr.insertCell().innerText = element.titre;
    // colonne : le type de document
    tr.insertCell().innerText = element.type;
    // colonne fichier
    tr.insertCell().innerText = element.fichier;
}
