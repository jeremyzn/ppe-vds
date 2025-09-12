"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------

import {initialiserMenuHorizontal} from "/composant/menuhorizontal/menu.js";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------

/*global data, lesOptions */

const nom = document.getElementById('nom');
const email = document.getElementById('email');


// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------

initialiserMenuHorizontal(lesOptions);

// afficher les informations
nom.textContent = data.nom + ' ' + data.prenom;
email.textContent = data.email;

