"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------

import { appelAjax } from "/composant/fonction/ajax.js";
import { confirmer, messageBox, corriger } from "/composant/fonction/afficher.js";
import {
    fichierValide, effacerLesErreurs,
    creerBoutonSuppression, creerBoutonRemplacer
} from "/composant/fonction/formulaire.js";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------

/* global lesPartenaires, lesParametres */

let idEnCours;

// récupération des éléments sur l'interface
const lesLignes = document.getElementById('lesLignes');
const logo = document.getElementById('logo');
const nb = document.getElementById('nb');

// -----------------------------------------------------------------------------------
// Procédures évènementielles
// -----------------------------------------------------------------------------------


// sur la sélection d'un logo
logo.onchange = () => {
    effacerLesErreurs();
    if (logo.files.length > 0) {
        let file = logo.files[0];
        if (fichierValide(file, lesParametres)) {
            remplacer(file);
        }
    }
};

// -----------------------------------------------------------------------------------
// Fonctions de traitement
// -----------------------------------------------------------------------------------

/**
 * Ajoute la photo sélectionnée ou déposée
 * @param {File} file
 */
function remplacer(file) {
    if (!idEnCours) {
        messageBox("Identifiant du partenaire manquant", "error");
        return;
    }
    // transfert du fichier vers le serveur dans le répertoire sélectionné
    const formData = new FormData();
    formData.append('fichier', file);
    formData.append('id', idEnCours);     // <-- envoi de l'id attendu par le PHP
    appelAjax({
        url: 'ajax/remplacer.php',
        data: formData,
        success: () => {
            messageBox("Logo remplacé avec succès");
            // réinitialiser l'id en cours
            idEnCours = null;
            // Recharger la page pour afficher le nouveau logo
            setTimeout(() => location.reload(), 1000);
        },
        error: (m) => {
            messageBox(m || 'Erreur lors de l\'upload', 'error');
        }
    });
}

/**
 * Demande de modification de la valeur d'une colonne
 * @param {string} colonne
 * @param {object} input balise input
 * @param {int} id identifiant du partenaire à modifier
 */
function modifierColonne(colonne, input, id) {
    appelAjax({
        url: '/ajax/modifiercolonne.php',
        data: {
            table: 'partenaire',
            colonne: colonne,
            valeur: input.value,
            id: id
        },
        success: () => {
            input.style.color = 'green';
            // modifier l'ancienne valeur
            input.dataset.old = input.value;
        },
        error: (message) => {
            input.style.color = 'red';
            messageBox(message, 'error');
        }
    });
}

// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------

logo.accept = lesParametres.accept;
nb.innerText = lesPartenaires.length;

// afficher le tableau des partenaires
for (const p of lesPartenaires) {
    let id = p.id;
    let tr = lesLignes.insertRow();
    tr.style.verticalAlign = 'middle';
    tr.id = p.id;

    // 1. Colonne des actions (remplacer logo / supprimer)
    const tdAction = document.createElement('td');
    const container = document.createElement('div');
    Object.assign(container.style, {
        display: 'flex',
        gap: '8px',
        alignItems: 'center'
    });

    const actionRemplacer = () => {
        // mémorise l'id avant d'ouvrir le file picker
        idEnCours = id;
        logo.click();
    };
    const btnRemplacer = creerBoutonRemplacer(actionRemplacer)
    container.appendChild(btnRemplacer);

    // ajout de l'icone de suppression
    const supprimer = () =>
        appelAjax({
            url: 'ajax/supprimer.php',
            data: {
                id: id
            },
            success: () => {
                tr.remove();
                nb.innerText = parseInt(nb.innerText) - 1;
            }
        });
    const actionSupprimer = () => confirmer(supprimer, "Voulez-vous vraiment supprimer ce partenaire ?");
    const btnSupprimer = creerBoutonSuppression(actionSupprimer);
    container.appendChild(btnSupprimer);

    tdAction.appendChild(container);
    tr.appendChild(tdAction);

    // deuxième colonne : le logo
    const tdLogo = tr.insertCell();
    if (p.fichier) {
        const img = document.createElement('img');
        img.src = p.fichier ? '/data/partenaire/' + p.fichier : '/data/partenaire/.keep';
        img.alt = p.nom || '';
        img.style.maxHeight = '50px';
        img.style.maxWidth = '120px';
        img.style.objectFit = 'contain';
        tdLogo.appendChild(img);
    } else {
        tdLogo.innerHTML = '<span class="text-muted">Aucun logo</span>';
    }

    // troisième colonne : le nom du partenaire qui peut être directement modifié
    let nom = document.createElement("input");
    nom.type = 'text';
    nom.className = 'form-control';
    nom.maxLength = 255;
    nom.minLength = 2;
    nom.required = true;
    nom.value = p.nom;
    nom.dataset.old = p.nom;
    nom.onkeydown = (e) => !/[<>]/.test(e.key);
    nom.onchange = function () {
        if (this.value !== this.dataset.old) {
            if (this.checkValidity()) {
                modifierColonne('nom', this, id);
            } else {
                corriger(this);
            }
        }
    };
    tr.insertCell().appendChild(nom);

    // quatrième colonne : l'URL qui peut être directement modifiée
    let url = document.createElement("input");
    url.type = 'url';
    url.className = 'form-control';
    url.maxLength = 1024;
    url.value = p.url || '';
    url.dataset.old = p.url || '';
    url.placeholder = "https://example.com";
    url.onchange = function () {
        if (this.value !== this.dataset.old) {
            // Ajouter https:// si manquant
            if (this.value && !this.value.startsWith('http')) {
                this.value = 'https://' + this.value;
            }

            if (this.checkValidity() || this.value === '') {
                modifierColonne('url', this, id);
            } else {
                corriger(this);
            }
        }
    };
    tr.insertCell().appendChild(url);
}