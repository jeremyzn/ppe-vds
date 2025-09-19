"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------

import {appelAjax} from "/composant/fonction/ajax.js";
import {afficherToast,} from "/composant/fonction/afficher.js";
import {configurerFormulaire, fichierValide } from "/composant/fonction/formulaire.js";
import {initialiserMenuHorizontal} from "/composant/menuhorizontal/menu.js";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------

/*global lesParametres, photo, lesOptions */

const msg = document.getElementById('msg');
const cible = document.getElementById('cible');
const fichier = document.getElementById('fichier');
const btnSupprimer = document.getElementById('btnSupprimer');
fichier.accept = lesParametres.accept;

// -----------------------------------------------------------------------------------
// procédures évènementielles
// -----------------------------------------------------------------------------------

// Déclencher le clic sur le champ de type file lors d'un clic dans la zone cible
cible.onclick = () => fichier.click();

// sur la sélection d'un fichier
fichier.onchange = () => {
    if (fichier.files.length > 0) {
        let file = fichier.files[0];
        if (controlerFichier(file)) {
            ajouter(file);
        }
    }
};

// définition des gestionnaires d'événements pour déposer un fichier dans la cible
cible.ondragover = (e) => e.preventDefault();
cible.ondrop = (e) => {
    e.preventDefault();
    let file = e.dataTransfer.files[0];
    if (controlerFichier(file)) {
        ajouter(file);
    }
};

// suppression de la photo
btnSupprimer.onclick = () => {
    supprimer();
};

// -----------------------------------------------------------------------------------
// Fonctions de traitement
// -----------------------------------------------------------------------------------

/**
 * Traite le fichier sélectionné ou déposé
 * @param {File} file - Le fichier à contrôler
 */
function controlerFichier(file) {
    // Vérification de taille et d'extension
    // on ne vérifie les dimensions car la photo sera automatiquement redimensionnée
    if (fichierValide(file, lesParametres)) {
        ajouter(file);
    }
}

/**
 * Ajoute la photo sélectionnée ou déposée
 * @param file
 */
function ajouter(file) {
    // Vider la zone de message utilisateur
    msg.innerHTML = "";

    // Créer un objet FormData pour envoyer les données du formulaire
    const formData = new FormData();
    formData.append('fichier', file);

    appelAjax({
        url: 'ajax/ajouter.php',
        data: formData,
        success: (data) => {
            // il faut afficher le bouton pour supprimer la photo
            btnSupprimer.style.display = 'block';
            const img = document.createElement('img');
            img.src = lesParametres.repertoire + '/' + data.success;
            cible.innerHTML = '';
            cible.appendChild(img);
            afficherToast('La photo a été modifiée');
        }
    });
}

/**
 * Supprime la photo
 */
function supprimer() {
    // Vider la zone de message utilisateur
    msg.innerHTML = "";

    appelAjax({
        url: 'ajax/supprimer.php',
        success: () => {
            // il faut afficher le bouton pour supprimer la photo
            btnSupprimer.style.display = 'none';
            // effacer la photo
            cible.innerHTML = '';
        }
    });
}

// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------

initialiserMenuHorizontal(lesOptions);

configurerFormulaire();

// alimentation de la zone cible avec la photo si elle existe
if (photo.present) {
    let img = document.createElement('img');
    img.src = lesParametres.repertoire + '/' + photo.photo;
    img.alt = 'photo du membre';
    cible.appendChild(img);
    btnSupprimer.style.display = 'block';
}