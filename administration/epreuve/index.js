"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------

import {appelAjax} from "/composant/fonction/ajax.js";
import {configurerFormulaire, donneesValides } from "/composant/fonction/formulaire.js";
import {messageBox} from "/composant/fonction/afficher.js";
import {initialiserEtapes} from "/composant/fonction/etape.js";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------

/* global lesEpreuves, tinymce */

// récupération des éléments de l'interface
const saison = document.getElementById('saison');
const msg = document.getElementById('msg');
const date = document.getElementById('date');
const description = document.getElementById('description');
const btnModifier = document.getElementById('btnModifier');

// -----------------------------------------------------------------------------------
// Procédures évènementielles
// -----------------------------------------------------------------------------------

// demande de modification
btnModifier.onclick = () => {
    if (donneesValides()) {
            modifier();
    }
};

// sur le changement de l'épreuve (saison), il faut afficher les informations de cette épreuve
saison.onchange = afficher;

// -----------------------------------------------------------------------------------
// Fonctions de traitement
// -----------------------------------------------------------------------------------

/**
 * Affiche les informations de l'épreuve sélectionnée
 */
function afficher() {
    // Récupération de l'épreuve
    const epreuve = lesEpreuves[saison.selectedIndex];
    // affichage des informations sur l'interface
    date.value = epreuve.date;
    description.value = epreuve.description;
    // mise à jour de l'éditeur TinyMCE
    tinymce.get('description').setContent(epreuve.description);
    // alimentation des balises span de class 'saison' avec la saison
    for(const span of document.querySelectorAll('.saison')) {
        span.textContent = epreuve.saison;
    }
}

function modifier() {
    msg.innerHTML = "";
    // Transmission des paramètres
    const lesValeurs = {};
    lesValeurs.date = date.value;
    lesValeurs.description = description.value;

    const formData = new FormData();
    formData.append('table', 'epreuve');
    formData.append('id', saison.value);
    formData.append('lesValeurs', JSON.stringify(lesValeurs));
    appelAjax({
        url: '/ajax/modifier.php',
        data: formData,
        success: () => {
            messageBox("Modification réussie");
            // mettre à jour l'objet
            const epreuve = lesEpreuves[saison.selectedIndex];
            epreuve.date = date.value;
            epreuve.description = description.value;
            epreuve.dateFermeture = dateFermeture.value;
            epreuve.dateOuverture = dateOuverture.value;
            epreuve.urlInscription = urlInscription.value;
            epreuve.urlInscrit = urlInscrit.value;
        },
    });
}

// affichage de la dernière étape : le résumé
function majResume() {
    document.getElementById('r-epreuve').textContent = saison.value;
    document.getElementById('r-description').innerHTML = description.value;
    document.getElementById('r-date').textContent = date.value;
}

// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------

// Initialisation de TinyMCE
tinymce.init({
    license_key: 'gpl',
    selector: '#description',
    menubar: false,
    plugins: 'link lists table autoresize code',
    toolbar: [
        'undo redo | styles | bold italic underline | forecolor backcolor | fontsizeselect | link | bullist numlist outdent indent | table | code'
    ],
    fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
    setup: function (editor) {
        editor.on('change', function () {
            description.value = editor.getContent();
        });
    }
});


// mise en place des balises div de class 'messageErreur' sur chaque champ de saisie
configurerFormulaire();


// alimentation de la zone de liste des épreuves
for (const epreuve of lesEpreuves) {
    saison.add(new Option(epreuve.saison, epreuve.saison));
}

// charger les informations de l'épreuve actuellement sélectionnée
afficher();

// initialisation des étapes avec fonction de rappel sur la dernière étape
initialiserEtapes(majResume);



