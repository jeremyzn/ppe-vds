"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------

import {appelAjax} from "/composant/fonction/ajax.js";
import {confirmer, messageBox, corriger} from "/composant/fonction/afficher.js";
import {
    fichierValide, effacerLesErreurs,
    creerBoutonSuppression, creerBoutonModification, creerBoutonRemplacer

} from "/composant/fonction/formulaire.js";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------

/* global lesClassements, lesParametres */

// conserver le nom du fichier à remplacer
let nomFichier;

// récupération des élements sur l'interface
const lesLignes = document.getElementById('lesLignes');
const fichier = document.getElementById('fichier');
const nb = document.getElementById('nb');

// -----------------------------------------------------------------------------------
// Procédures évènementielles
// -----------------------------------------------------------------------------------

// sur la sélection d'un fichier
fichier.onchange = () => {
    effacerLesErreurs();
    if (fichier.files.length > 0) {
        let file = fichier.files[0];
        if (fichierValide(file, lesParametres)) {
            remplacer(file);
        }
    }
};

// -----------------------------------------------------------------------------------
// Fonctions de traitement
// -----------------------------------------------------------------------------------

function remplacer(file) {
    // transfert du fichier vers le serveur dans le répertoire sélectionné
    const formData = new FormData();
    formData.append('fichier', file);
    formData.append('nomFichier', nomFichier);
    appelAjax({
        url: 'ajax/remplacer.php',
        data: formData,
        success: () => {
            messageBox("Opération réalisée avec succès");
        }
    });
}

/**
 * Demande de modification de la valeur d'une colonne
 * @param {string} colonne
 * @param {object} input balise input
 * @param {int} id identifiant du classement à modifier
 */
function modifierColonne(colonne, input, id) {
    appelAjax({
        url: '/ajax/modifiercolonne.php',
        data: {
            table: 'classement',
            colonne: colonne,
            valeur: input.value,
            id: id
        },
        success: () => {
            input.style.color = 'green';
            // modifier l'ancienne valeur
            input.dataset.old = input.value;
        },
    });
}

// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------

fichier.accept = lesParametres.accept;
nb.innerText = lesClassements.length;

// afficher le tableau des classements
for (const element of lesClassements) {
    let id = element.id;
    let tr = lesLignes.insertRow();
    tr.style.verticalAlign = 'middle';
    tr.id = element.id;

    // 1. Colonne des actions (modifier / supprimer)
    const tdAction = document.createElement('td');
    const container = document.createElement('div');
    Object.assign(container.style, {
        display: 'flex',
        gap: '8px',
        alignItems: 'center'
    });

    // lien pour afficher le classement
    if (element.present) {
        let a = document.createElement('a');
        a.href = "/afficherclassement.php?id=" + element.id;
        a.target = 'pdf';
        a.innerText = '📄';
        container.appendChild(a);
    } else {
        container.innerText = '❓';
        console.log("Le classement " + element.id + " n'a pas été trouvé");
    }

    // icône pour téléverser une nouvelle version du classement
    const actionRemplacer = () => {
        nomFichier = element.fichier;
        fichier.click();
    };

    const btnRemplacer = creerBoutonRemplacer(actionRemplacer)
    container.appendChild(btnRemplacer);

    // ajout de l'icone de suppression
    const supprimer = () =>
        appelAjax({
            url: '/ajax/supprimer.php',
            data: {
                table: 'classement',
                id: id
            },
            success: () => tr.remove()
        });
    const actionSupprimer = () => confirmer(supprimer);
    const btnSupprimer = creerBoutonSuppression(actionSupprimer);
    container.appendChild(btnSupprimer);

    tdAction.appendChild(container);
    tr.appendChild(tdAction);

    // seconde colonne la date
    let date = document.createElement("input");
    date.type = 'date';
    date.value = element.date;
    date.dataset.old = element.date;
    date.max = new Date().toISOString().split('T')[0];
    date.onblur = () => {
        // contrôle de la valeur
        if (date.value !== date.dataset.old) {
            modifierColonne('date', date, id);
        }
    };
    tr.insertCell().appendChild(date);

    // seconde colonne : le titre du classement qui peut être directement modifié
    let titre = document.createElement("input");
    titre.type = 'text';
    titre.maxLength = 100;
    titre.minLength = 10;
    titre.required = true;
    titre.value = element.titre;
    titre.dataset.old = element.titre;
    titre.onkeydown = (e) => !/[<>]/.test(e.key);
    titre.onchange = function () {
        if (this.value !== this.dataset.old) {
            if (this.checkValidity()) {
                modifierColonne('titre', this, id);
            } else {
                corriger(this);
            }
        }
    };
    tr.insertCell().appendChild(titre);

    // quatrième colonne : Le nombre de demandes de téléchargement
    const td = tr.insertCell();
    td.style.textAlign = 'right';
    td.style.paddingRight = '10px';
    td.innerText = element.nbDemande;
}