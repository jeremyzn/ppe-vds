"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------

import {appelAjax} from "/composant/fonction/ajax.js";
import {afficherToast,} from "/composant/fonction/afficher.js";
import {configurerFormulaire, fichierValide, verifierImage, effacerLesErreurs} from "/composant/fonction/formulaire.js";
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

/// Déclencher le clic sur le champ de type file lors d'un clic dans la zone cible
cible.onclick = () => fichier.click();

// // ajout du glisser déposer dans la zone cible
cible.ondragover = (e) => e.preventDefault();
cible.ondrop = (e) => {
    e.preventDefault();
    controlerFichier(e.dataTransfer.files[0]);
};

// traitement du champ file associé aux modifications de photos
fichier.onchange = function () {
    if (this.files.length > 0) {
        controlerFichier(this.files[0]);
    }
};

// suppression de la photo
btnSupprimer.onclick = supprimer;

// -----------------------------------------------------------------------------------
// Fonctions de traitement
// -----------------------------------------------------------------------------------

/**
 * Contrôle le fichier sélectionné au niveau de son extension et de sa taille
 * Vérifie que le fichier est bien une image et que ses dimensions sont correctes si le redimensionnement n'est pas demandé
 * lancer la demande de remplacement de l'image
 * @param file {object} fichier à ajouter
 */
function controlerFichier(file) {
    // Efface les erreurs précédentes
    effacerLesErreurs();
    // Vérification de taille et d'extension
    if (!fichierValide(file, lesParametres)) {
        return;
    }
    // Vérifications spécifiques pour un fichier image
    // La fonction de rappel reçoit implicitement en paramètre l'objet file et l'objet Image créé
    verifierImage(file, lesParametres, ajouter);
}

/**
 * Ajoute la photo sélectionnée ou déposée
 * @param file
 * @param img
 */
function ajouter(file, img) {
    // Vider la zone de message utilisateur
    msg.innerHTML = "";

    // Créer un objet FormData pour envoyer les données du formulaire
    const formData = new FormData();
    formData.append('fichier', file);

    appelAjax({
        url: 'ajax/ajouter.php',
        data: formData,
        success: () => {
            // il faut afficher le bouton pour supprimer la photo
            btnSupprimer.style.display = 'block';
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
            // il faut masquer  le bouton pour supprimer la photo
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