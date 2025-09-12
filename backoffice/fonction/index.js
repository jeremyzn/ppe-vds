"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------

import {appelAjax} from "/composant/fonction/ajax.js";
import {configurerFormulaire, donneesValides, effacerLesChamps} from "/composant/fonction/formulaire.js";
import {afficherToast, confirmer} from "/composant/fonction/afficher.js";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------
/* global lesFonctions*/
const nom = document.getElementById('nom');
const repertoire = document.getElementById('repertoire');
const msg = document.getElementById('msg');
const btnAjouter = document.getElementById('btnAjouter');
const lesLignes = document.getElementById('lesLignes');

// -----------------------------------------------------------------------------------
// procédures évènementielles
// -----------------------------------------------------------------------------------

// bouton d'ajout
btnAjouter.onclick = () => {
    if (donneesValides()) {
        ajouter();
    }
};

// -----------------------------------------------------------------------------------
// Fonctions de traitement
// -----------------------------------------------------------------------------------

// fonction d'affichage
function afficherLesFonctions() {
    if (lesFonctions.length === 0) {
        lesLignes.innerHTML = "<p>Aucune donnée à afficher</p>";
    } else {
        effacerLesChamps();
        lesLignes.innerHTML = "";

        for (const fonction of lesFonctions) {
            const tr = lesLignes.insertRow();
            tr.id = fonction.repertoire;

            const td = tr.insertCell();
            const btnSupprimer = document.createElement('span');
            btnSupprimer.textContent = '✘';
            btnSupprimer.style.color = 'red';
            btnSupprimer.style.cursor = 'pointer';
            btnSupprimer.title = 'Supprimer';
            btnSupprimer.onclick = () => confirmer(() => supprimer(fonction));
            td.appendChild(btnSupprimer);

            tr.insertCell().innerText = fonction.repertoire;
            tr.insertCell().innerText = fonction.nom;
        }
    }
}

/**
 * Ajout d'une fonction
 */
function ajouter() {
    // vider la zone de message msg
    msg.innerHTML = "";
    appelAjax({
        url: '/ajax/ajouter.php',
        data : {
            table: 'fonction',
            repertoire: repertoire.value,
            nom: nom.value
        },
        success: () => {
            afficherToast("Ajout réussi !");
            // mettre à jour le tableau lesFonctions
            lesFonctions.push({
                repertoire: repertoire.value,
                nom: nom.value
            });
            // trier le tableau
            lesFonctions.sort((a, b) => a.nom.localeCompare(b.nom));
            // vider les champs
            effacerLesChamps();
            // mettre à jour l'affichage
            repertoire.focus();
            afficherLesFonctions();
        }
    });
}

/**
 * Suppression d'une fonction
 * @param {object} fonction : objet fonction à supprimer
 */
function supprimer(fonction) {

    appelAjax({
        url: '/ajax/supprimer.php',
        data : {
            table: 'fonction',
            id: fonction.repertoire
        },
        success: () => {
            // mettre à jour le tableau lesFonctions
            // indexOf compare les références mémoire et non les valeurs
            // fonction est bien ici le même objet que dans le tableau
            lesFonctions.splice(lesFonctions.indexOf(fonction), 1);
            // mettre à jour l'affichage
            afficherLesFonctions();
        }
    });
}

// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------
// configuration du formulaire
configurerFormulaire();
// affichage initial
afficherLesFonctions();

