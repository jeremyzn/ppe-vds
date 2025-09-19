"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------

import {afficherCompteur} from "/composant/fonction/afficher.js";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------
/* global lesMembres */

let liste = document.getElementById('liste');
const nb = document.getElementById('nb');

// -----------------------------------------------------------------------------------
// Fonctions de traitement
// -----------------------------------------------------------------------------------

/**
 * Crée une carte Bootstrap (card) pour afficher les informations d'un membre
 * @param {Object} membre - L'objet membre avec nom, prénom, mail, téléphone et photo
 * @returns {HTMLElement} - Élément DOM représentant une carte complète
 */
function creerCardMembre(membre) {
    // Création du conteneur card
    const card = document.createElement('div');
    card.classList.add('card', 'm-1', 'd-flex', 'flex-row', 'justify-content-between');

    // Partie texte : nom, mail, téléphone
    const coordonnees = document.createElement('div');
    let contenu = `<strong>${membre.nom} ${membre.prenom}</strong>`;

    contenu += membre.mail !== 'Non communiqué'
        ? `<br>${membre.mail}`
        : `<br><span style="font-style: italic">Adresse mail masquée</span>`;

    contenu += membre.telephone !== 'Non renseigné'
        ? `<br>${membre.telephone}`
        : `<br><span style="font-style: italic">Téléphone non renseigné</span>`;

    coordonnees.innerHTML = contenu;
    card.appendChild(coordonnees);

    // Partie photo
    const photo = document.createElement('div');
    photo.style.height = '100px';
    photo.style.width = '110px';

    if (membre.photo !== 'Non trouvée' && membre.photo !== 'Non renseignée') {
        const img = document.createElement('img');
        img.src = membre.photo;
        img.alt = "";
        img.style.maxWidth = '100px';
        img.style.maxHeight = '100px';
        photo.appendChild(img);
    }

    card.appendChild(photo);

    return card;
}

/**
 * Affiche tous les membres sous forme de cartes Bootstrap dans un élément cible
 */
function afficher() {
   liste.innerHTML = ''; // Vide le contenu existant

    const row = document.createElement('div');
    row.classList.add('row');

    for (const membre of lesMembres) {
        const col = document.createElement('div');
        col.classList.add('col-xl-4', 'col-lg-4', 'col-md-6', 'col-sm-6', 'col-12');

        const card = creerCardMembre(membre);
        col.appendChild(card);
        row.appendChild(col);
    }

    liste.appendChild(row);
}

// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------

afficher();

afficherCompteur({
    conteneur: nb,
    valeurFinale: lesMembres.length,
    duree: 2000,
    styles: {
        color: 'white',
        fontSize: '20px',
        textAlign: 'right',
        backgroundColor: "#0790e4",
        padding: "5px",
        borderRadius: "8px",
        margin: "10px 0"
    }
});
