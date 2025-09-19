"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------
import {appelAjax} from "/composant/fonction/ajax.js";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------

/* global lesFonctions, lesAdministrateurs */

// récupération des éléments du DOM
let idAdministrateur = document.getElementById('idAdministrateur');
let listeFonction = document.getElementById('listeFonction');
let btnSupprimerTout = document.getElementById('btnSupprimerTout');
let btnAjouterTout = document.getElementById('btnAjouterTout');
let frmGestion = document.getElementById('frmGestion');

// -----------------------------------------------------------------------------------
// procédures évènementielles
// -----------------------------------------------------------------------------------

btnSupprimerTout.onclick = () => supprimerTousLesDroits(idAdministrateur.value);
btnAjouterTout.onclick = () => ajouterTousLesDroits(idAdministrateur.value);
idAdministrateur.onchange = function () {
    chargerLesDroits(this.value);
};

// -----------------------------------------------------------------------------------
// Fonctions de traitement
// -----------------------------------------------------------------------------------

/**
 * Récupération des droits de l'administrateur sélectionné afin de cocher les bonnes cases
 */
function chargerLesDroits(id) {
    const formData = new FormData();
    formData.append('idAdministrateur', id);
    // appel ajax pour récupérer les droits de l'administrateur
    appelAjax({
        url: 'ajax/getlesdroits.php',
        method: 'POST',
        data: formData,
        success: (data) => {
            decocherToutesLesCases();
            // mise à jour de l'interface en cochant les cases correspondant aux droits de l'administrateur
            for (const fonction of data) {
                document.getElementById(fonction.repertoire).checked = true;
            }
        },
    });
}

/**
 * Suppression de tous les droits de l'administrateur actuellement sélectionné sur l'interface
 * Toutes les cases sont décochées sur l'interface
 */
function supprimerTousLesDroits(id) {
    const formData = new FormData();
    formData.append('idAdministrateur', id);
    appelAjax({
        url: 'ajax/supprimertous.php',
        data: formData,
        success: decocherToutesLesCases
    });
}

/**
 * Ajouter tous les droits
 * Toutes les cases sont cochées sur l'interface
 */
function ajouterTousLesDroits(id) {
    const formData = new FormData();
    formData.append('idAdministrateur', id);

    // appel ajax pour ajouter tous les droits
    appelAjax({
        url: 'ajax/ajoutertous.php',
        data: formData,
        success: cocherToutesLesCases,
    });
}

function decocherToutesLesCases() {
    for (const input of document.getElementsByName("fonction")) {
        input.checked = false;
    }
}

function cocherToutesLesCases() {
    for (const input of document.getElementsByName("fonction")) {
        input.checked = true;
    }
}

// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------

// afficher les fonctions dans le formulaire de gestion
for (const fonction of lesFonctions) {
    let div = document.createElement('div');
    div.classList.add("d-flex", "mb-1");
    let uneCase = document.createElement('input');
    uneCase.type = 'checkbox';
    uneCase.id = fonction.repertoire;
    uneCase.classList.add("form-check-input", "my-auto", "m-3");
    uneCase.style.width = '25px';
    uneCase.style.height = '25px';
    // pour permettre de récupérer toutes les cases
    uneCase.name = 'fonction';

    // le clic sur une case à cocher déclenche la mise à jour des droits de l'administrateur (ajout ou suppression)
    uneCase.onclick = function () {
        let url = uneCase.checked ? "ajax/ajouter.php" : "ajax/supprimer.php";
        const formData = new FormData();
        formData.append('idAdministrateur', idAdministrateur.value);
        formData.append('repertoire', fonction.repertoire);
        appelAjax({
            url: url,
            data: formData,
            method: 'post',
            error: () => {
                uneCase.checked = !uneCase.checked; // on remet la case à son état initial
            }
        });
    };
    div.appendChild(uneCase);
    let label = document.createElement('label');
    label.innerText = fonction.nom;
    label.classList.add("my-auto");
    div.appendChild(label);
    listeFonction.appendChild(div);
}

// Selection de l'interface à afficher

if (lesAdministrateurs.length > 0) {
    frmGestion.style.display = 'block';
    // Remplir la zone de liste des administrateurs
    for (const admin of lesAdministrateurs) {
        idAdministrateur.add(new Option(admin.nom, admin.id));
    }
    chargerLesDroits(idAdministrateur.value);
} else {
    frmGestion.style.display = 'none';
}

