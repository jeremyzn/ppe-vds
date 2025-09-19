"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------

import {configurerFormulaire, donneesValides} from "/composant/fonction/formulaire.js";
import {getAllure, getVitesse} from "/composant/fonction/vitesse.js";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------

// récupération des éléments du DOM
let temps = document.getElementById('temps');
let distance = document.getElementById('distance');
let btnCalculer = document.getElementById('btnCalculer');

// -----------------------------------------------------------------------------------
// Procédures évènementielles
// -----------------------------------------------------------------------------------

// le bouton btnCalculer doit appeler la fonction calculerVitesseAllure
btnCalculer.onclick = () => {
    if (donneesValides()) {
        calculer(temps.value, parseFloat(distance.value));
    }
};

// -----------------------------------------------------------------------------------
// Fonctions de traitement
// -----------------------------------------------------------------------------------

/**
 * Calculer la vitesse et l'allure à partir du temps et de la distance
 * @param temps
 * @param distance
 */
function calculer(temps, distance) {

    // calcul de la vitesse en km/h
    let vitesse = getVitesse(temps, distance);

    // calcul de l'allure en min/km
    //let allure = Math.floor(3600 / vitesse);
    let allure =  getAllure(temps, distance);

    // affichage des résultats
    document.getElementById('vitesse').value = vitesse.toFixed(2);
    document.getElementById('allure').value = allure;
}

// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------
configurerFormulaire();

temps.value = '00:45:00';
distance.value = '10';

