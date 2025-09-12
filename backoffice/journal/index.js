"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------
import {appelAjax} from "/composant/fonction/ajax.js";
import {confirmer} from "/composant/fonction/afficher.js";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------

/* global lesEvenements */

const btnSupprimer = document.getElementById('btnSupprimer');
const msg = document.getElementById('msg');
const table = document.getElementById('table');


// -----------------------------------------------------------------------------------
// procédures évènementielles
// -----------------------------------------------------------------------------------
// Lorsqu'on clique sur "Vider le journal"
btnSupprimer.onclick = () => {
    confirmer(supprimer, "Voulez-vous vraiment vider le journal des erreurs ?");
};

// -----------------------------------------------------------------------------------
// Fonctions de traitement
// -----------------------------------------------------------------------------------

function supprimer() {
    // Vider la zone de message utilisateur
    msg.innerHTML = "";
    appelAjax({
        url: 'ajax/effacer.php',
        method: 'POST',
        success: () => {
            table.innerHTML = "";
        }
    });
}

// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------

for(const erreur of lesEvenements) {
    // découpage de la chaîne d'erreur
    const lesValeurs = erreur.split('\t');

    // Création de la div englobante avec styles en ligne
    const div = document.createElement('div');
    div.style.border = '1px solid #ccc';
    div.style.borderRadius = '6px';
    div.style.padding = '0.5em';
    div.style.marginBottom = '0.5em';
    div.style.backgroundColor = '#f9f9f9';
    div.style.gap = '1em';
    div.style.fontFamily = 'monospace';

    // Création des balises <p> pour chaque valeur
    for(const valeur of lesValeurs) {
        const p = document.createElement('p');
        p.textContent = valeur;
        p.style.margin = '0';
        p.style.minWidth = '8em';
        div.appendChild(p);
    }

    table.appendChild(div);
}
