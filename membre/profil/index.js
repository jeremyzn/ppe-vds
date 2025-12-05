"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------


// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------

/*global data */

const nom = document.getElementById('nom');
const email = document.getElementById('email');


// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------


// afficher les informations
nom.textContent = data.nom + ' ' + data.prenom;
email.textContent = data.email;

