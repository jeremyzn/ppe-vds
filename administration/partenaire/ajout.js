"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------

import {appelAjax} from "/composant/fonction/ajax.js";
import {retournerVers, afficherSousLeChamp} from '/composant/fonction/afficher.js';
import {
    configurerFormulaire,
    filtrerLaSaisie,
    donneesValides,
    fichierValide,
    effacerLesErreurs
} from "/composant/fonction/formulaire.js";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------

/* global lesParametres */
let leLogo = null;

const logo = document.getElementById('logo');
const nomLogo = document.getElementById('nomLogo');
const nom = document.getElementById('nom');
const url = document.getElementById('url');
const btnLogo = document.getElementById('btnLogo');
const btnAjouter = document.getElementById('btnAjouter');
const msg = document.getElementById('msg');

// -----------------------------------------------------------------------------------
// Procédures évènementielles
// -----------------------------------------------------------------------------------

// Ouvrir le file picker
btnLogo.onclick = () => logo.click();

// Contrôler le fichier choisi
logo.onchange = () => {
    if (logo.files.length > 0) {
        controlerLogo(logo.files[0]);
    }
};

btnAjouter.onclick = () => {
    // Effacer les erreurs visuelles
    effacerLesErreurs();
    // normaliser champs
    nom.value = nom.value.trim().replace(/\s+/g, ' ');
    url.value = url.value.trim();

    if (leLogo === null && (lesParametres.require ?? false)) {
        afficherSousLeChamp('logo', 'Veuillez sélectionner un logo');
        return;
    }

    if (!donneesValides()) return;

    ajouter();
};

// -----------------------------------------------------------------------------------
// Fonctions de traitement
// -----------------------------------------------------------------------------------

/**
 * Contrôle le logo sélectionné au niveau de son extension et de sa taille
 * Affiche le nom du fichier dans la balise 'nomLogo' ou un message d'erreur sous le champ logo
 * Renseigne la variable globale leLogo
 * @param {File} file
 */
function controlerLogo(file) {
    effacerLesErreurs();
    if (fichierValide(file, lesParametres)) {
        nomLogo.textContent = file.name;
        leLogo = file;
        if (nom.value.length === 0) {
            // proposer un nom à partir du nom du fichier sans extension
            nom.value = file.name.replace(/\.[^/.]+$/, "");
        }
    } else {
        leLogo = null;
        nomLogo.textContent = '';
    }
}

/**
 * Ajout d'un partenaire dans la table partenaire et du logo associé dans le répertoire correspondant
 * En cas de succès retour sur la page index des partenaires
 */
function ajouter() {
    let formData = new FormData();
    // le serveur s'attend à la clé 'fichier' pour les uploads (convention utilisée ailleurs)
    if (leLogo) formData.append('fichier', leLogo);
    formData.append('nom', nom.value);
    formData.append('url', url.value);
    
    appelAjax({
        url: 'ajax/ajouter.php',
        data: formData,
        success: () => {
            retournerVers("Partenaire ajouté", '..');
        },
        error: (message) => {
            afficherSousLeChamp('global', message);
        }
    });
}

// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------

// Contrôle des données
configurerFormulaire();

// Filtrage de la saisie pour le nom (lettres, chiffres, espaces et quelques caractères spéciaux)
filtrerLaSaisie('nom', /[0-9A-Za-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ,' \-]/);

// Configuration du champ URL
url.addEventListener('blur', function() {
    if (this.value && !this.value.startsWith('http')) {
        this.value = 'https://' + this.value;
    }
});

// Initialisation des données sur l'interface
logo.accept = lesParametres.accept;

let label = document.querySelector(`label[for="nomLogo"]`);
label.innerText = lesParametres.label;