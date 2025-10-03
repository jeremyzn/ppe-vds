"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------

import {appelAjax} from '/composant/fonction/ajax.js';
import {confirmer, genererMessage} from '/composant/fonction/afficher.js';
import {creerBoutonSuppression} from '/composant/fonction/formulaire.js';
import {getTd, getTr} from "/composant/fonction/trtd.js";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------

/* global lesDocuments */

const lesLignes = document.getElementById('lesLignes');
const msg = document.getElementById('msg');
const deleteOld = document.getElementById('deleteOld');

// -----------------------------------------------------------------------------------
// Procédures évènementielles
// -----------------------------------------------------------------------------------

// Demande de suppression des anciennes annonces
deleteOld.onclick = () => confirmer(delOld);

// -----------------------------------------------------------------------------------
// Fonctions de traitement
// -----------------------------------------------------------------------------------

/**
 *  Fonction principale d'affichage des documents
 */
function afficherLesDocuments() {
    lesLignes.innerHTML = '';
    for (const document of LesDocuments) {
        const ligne = creerLigneDocument(document);
        lesLignes.appendChild(ligne);
    }
}


/**
 * Crée et retourne une ligne de tableau représentant un document.
 * @param {object} document - Objet document avec id, nom, prenom
 * @returns {HTMLTableRowElement}
 */
function creerLigneDocument(document) {
    const { id, titre } = document;

    // 1. Colonne des actions (modifier / supprimer)
    const actionModifier = () => location.href = '/maj/?id=' + id;

    const supprimer = () =>
        appelAjax({
            url: '/ajax/supprimer.php',
            data: { table: 'document', id: id },
            success: () => tr.remove()
        });

    const actionSupprimer = () => confirmer(supprimer);

    const btnSupprimer = creerBoutonSuppression(actionSupprimer);

    const tdAction = getTd('');
    tdAction.appendChild(btnSupprimer);

    // colonne du titre
    const tdTitre = getTd(titre);

    // colonne du type
    const tdType = getTd(type);

    // colonne du fichier
    const tdFichier = getTd(fichier);

    // Création de la ligne
    const tr = getTr([tdAction, tdTitre, tdType, tdFichier]);
    tr.id = id;

    return tr;
}

/**
 * Supprime les anciens documents
 */
function delOld() {
    msg.innerText = "";
    appelAjax({
        url: 'ajax/deleteold.php',
        success: (data) => {
            for (const element of data) {
                document.getElementById(element.id).remove();
            }
            msg.innerHTML = genererMessage('Suppression effectuée', 'success', 2000);
        }
    });
}

// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------

afficherLesDocuments();