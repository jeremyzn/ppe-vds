"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------

import {initialiserToutesLesCartes, basculerToutesLesCartes} from "/composant/fonction/openclose.js?";
import {formatDateLong} from "/composant/fonction/date.js";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------

/* global prochaineEdition, lesClassements, lesDocumentsPublics, lesDocuments4s, lesDocumentClubs*/

// Récupération des éléments de l'interface
const detailClassement = document.getElementById('detailClassement');
const dateEpreuve = document.getElementById('dateEpreuve');
const descriptionEpreuve = document.getElementById('descriptionEpreuve');
const btnOuvrirToutes = document.getElementById('btnOuvrirToutes');
const btnFermerToutes = document.getElementById('btnFermerToutes');
const documentPublic = document.getElementById('documentPublic');
const document4Saisons = document.getElementById('document4Saisons');
const documentClub = document.getElementById('documentClub');

// -----------------------------------------------------------------------------------
// Procédures évènementielles
// -----------------------------------------------------------------------------------

btnOuvrirToutes.onclick = () => basculerToutesLesCartes(true);
btnFermerToutes.onclick = () => basculerToutesLesCartes(false); // fermer


// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------

// Mise en place du système d'ouverture/fermeture des cadres
initialiserToutesLesCartes();

// les informations

// affichage de la prochaine épreuve
dateEpreuve.innerText =  formatDateLong(prochaineEdition.date);
descriptionEpreuve.innerHTML = prochaineEdition.description;


// afficher les derniers classements pdf
for (const element of lesClassements) {
    let a = document.createElement('a');
    a.classList.add('lien'),
        a.href = "/afficherclassement.php?id=" + element.id;
    a.innerText = element.dateFr + ' ' + element.titre;
    detailClassement.appendChild(a);
}



// afficher le tableau des documents

for (const doc of lesDocumentsPublics) {
    let a = document.createElement('a');
    a.classList.add('lien'),
        a.href = "/afficher.php?id=" + doc.id;
    a.innerText = doc.titre;
    documentPublic.appendChild(a);
}

for (const doc of lesDocuments4s) {
    let a = document.createElement('a');
    a.classList.add('lien'),
        a.href = "/afficher.php?id=" + doc.id;
    a.innerText = doc.titre;
    document4Saisons.appendChild(a);
}

for (const doc of lesDocumentClubs) {
    let a = document.createElement('a');
    a.classList.add('lien'),
        a.href = "/afficher.php?id=" + doc.id;
    a.innerText = doc.titre;
    documentClub.appendChild(a);
}






