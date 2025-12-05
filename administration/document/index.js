"use strict";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------

/* global lesDocuments */

const lesLignes = document.getElementById('lesLignes');


// -----------------------------------------------------------------------------------
// Fonctions de traitement
// -----------------------------------------------------------------------------------

/**
 * Demande de suppression
 * @param {int} id id de l'enregistrement
 */
function supprimer(id) {
    appelAjax({
        url: 'ajax/supprimer.php',
        data: { id: id  },
        success: () => document.getElementById(id.toString())?.remove()
    });
}

/**
 * Remplace le fichier sur le serveur
 * @param file
 */
function remplacer(file) {
    // transfert du fichier vers le serveur dans le répertoire sélectionné
    const formData = new FormData();
    formData.append('fichier', file);
    formData.append('nomFichier', nomFichier);
    appelAjax({
        url: 'ajax/remplacer.php',
        data: formData,
        success: () => afficherToast("Opération réalisée avec succès")
    });
}

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
    sup.href = '#';
    sup.target = '_self';
    sup.innerText = '❌';
    sup.className = 'sup-link';
    sup.style.cursor = 'pointer';
    sup.addEventListener('click', function(e) {
        e.preventDefault();
        if (confirm("Confirmer la suppression ?")) supprimer(element.id);
    });
    td.appendChild(sup);


    // colonne : le titre du document
    tr.insertCell().innerText = element.titre;
    // colonne : le type de document
    tr.insertCell().innerText = element.type;
    // colonne fichier
    tr.insertCell().innerText = element.fichier;
}
