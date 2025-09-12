"use strict";
// Récherche de la catégorie à partir d'une année de naissance et d'une date de course

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------

import {getDateCourante, getSaison} from "/composant/fonction/date.js";

/*global lesCategories */

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------

// récupération des éléments du formulaire
const annee = document.getElementById('annee');
const dateCourse = document.getElementById('dateCourse');
const nomCategorie = document.getElementById('nomCategorie');
const distanceMax = document.getElementById('distanceMax');

let saison; // année de la saison correspondant à la date de la course

// -----------------------------------------------------------------------------------
// Procédures évènementielles
// -----------------------------------------------------------------------------------

// lancement automatique du traitement sur le changement de valeur :
annee.onchange = calculer;

annee.oninput = () => {
    // Convertir les valeurs en nombres
    const anneeValue = Number(annee.value);
    const anneeMax = Number(annee.max);
    const anneeMin = Number(annee.min);

    // Vérifier si la valeur est dans les limites
    if (anneeValue <= anneeMax && anneeValue >= anneeMin) {
        calculer();
    } else {
        nomCategorie.textContent = '';
        distanceMax.textContent = '';
    }
};


// si la date de course change il faut déterminer la saison
dateCourse.onchange = () => {
    if (dateCourse.checkValidity()) {
        const date = new Date(dateCourse.value);
        // détermination de la saison en fonction de la date de course
        saison = getSaison(date);
        // re-paramètrage de l'intervalle accepté pour le champ année
        parametrerAnnee();
        // relance du calcul
        calculer();
    }
};

// -----------------------------------------------------------------------------------
// Fonctions de traitement
// -----------------------------------------------------------------------------------

/**
 * Paramètrage du champ annee
 * en fonction de la saison courante et des catégories
 */
function parametrerAnnee() {
    // Paramètrage du champ annee de type 'range'
    // doit être compris entre l'âge minimum et l'âge maximum des catégories
    // la valeur par défaut est la moyenne des âges min et max
    annee.max = saison - lesCategories[0].ageMin;
    annee.min = saison - lesCategories[lesCategories.length - 1].ageMax;
}

/**
 * Calculer la catégorie en fonction de l'année de naissance
 * et de la saison courante
 */
function calculer() {

    // déterminer l'âge de la personne
    const age = saison - parseInt(annee.value);

    // recherche de la catégorie correspondante
    const categorie = lesCategories.find(x => age <= x.ageMax && age >= x.ageMin);

    // affichage du résultat
    if (categorie !== undefined) {
        nomCategorie.textContent = `${categorie.nom} (${categorie.id})`;
        distanceMax.textContent = categorie.distance;
    }
}

// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------

// initialisation des champs
// paramétrage de la date de course
saison = getSaison(new Date());
dateCourse.min = getDateCourante();
dateCourse.value = dateCourse.min;
// paramétrage de l'année
parametrerAnnee();
// Set l'année par défaut à la moyenne des années min et max
annee.value = Math.floor((parseInt(annee.min) + parseInt(annee.max)) / 2);
// lancement du calcul
calculer();