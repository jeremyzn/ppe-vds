"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------

import {appelAjax} from "/composant/fonction/ajax.js";
import {afficherToast} from "/composant/fonction/afficher.js";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------

/* global data, tinymce */

let contenu = document.getElementById('contenu');
let btnModifier = document.getElementById('btnModifier');

// -----------------------------------------------------------------------------------
// Procédures évènementielles
// -----------------------------------------------------------------------------------

btnModifier.onclick = () => {
    appelAjax({
        url: 'ajax/modifier.php',
        data: {
            contenu: contenu.value,
        },
        success: () => {
            afficherToast("Opération réalisée avec succès");
        }
    });
};

// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------

// Initialisation de TinyMCE
tinymce.init({
    license_key: 'gpl',
    selector: '#contenu',
    menubar: false,
    plugins: 'link lists table  code',
    toolbar: [
        'undo redo | styles | bold italic underline | forecolor backcolor | fontsizeselect | link | bullist numlist outdent indent| table | code'
    ],
    fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
    height : 400,
    setup: function (editor) {
        editor.on('init', function () {
            editor.setContent(data);
        });
        editor.on('change', function () {
            contenu.value = editor.getContent();
        });
    },

});

