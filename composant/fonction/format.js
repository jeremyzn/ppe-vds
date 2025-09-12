"use strict";

// Version 2025.3
// Date version : 28/08/2025
// Correctif sur ucWord pour gérer les chaînes indéfinies ou nulles


/**
 * Retourne la chaine passée en paramètre avec la première lettre de chaque mot en majuscule
 * @param {string} nom
 * @return {string} avec la première lettre de chaque mot en majuscule
 */
export function ucWord(nom) {
    // Vérifier si la chaîne est définie
    if (nom === undefined || nom === null) {
        return ''; // Retourner une chaîne vide si la chaîne est indéfinie ou nulle
    }
    // Vérifier si la chaîne n'est pas vide
    if (nom.length === 0) {
        return nom; // Retourner la chaîne vide si elle l'est
    }
    const lesMots = nom.trim().toLowerCase().split(/[\s]+/);
    for (let i = 0; i < lesMots.length; i += 1) {
        const lesSousMots = lesMots[i].split('-');
        for (let j = 0; j < lesSousMots.length; j += 1) {
            lesSousMots[j] = lesSousMots[j].charAt(0).toUpperCase() + lesSousMots[j].slice(1);
        }
        lesMots[i] = lesSousMots.join('-');
    }
    return lesMots.join(' ');
}

/**
 * Retourne la chaine passée en paramètre avec la première lettre en majuscule
 * @param nom
 * @returns {*|string}
 */
export function ucFirst(nom) {
    // Vérifier si la chaîne n'est pas vide
    if (nom.length === 0) {
        return nom; // Retourner la chaîne vide si elle l'est
    }
    // Mettre en majuscule la première lettre et le reste en minuscule
    return nom.charAt(0).toUpperCase() + nom.slice(1).toLowerCase();
}

/**
 * retourne le nombre de décimales composant un nombre
 * @param {number} x nombre à analyser
 * @returns {number}
 */
export function nbDecimale(x) {
    // Convertir le nombre en chaîne de caractères
    const nombreEnChaine = x.toString();

    // Rechercher l'indice du séparateur décimal (virgule ou point)
    const indiceSeparateur = nombreEnChaine.indexOf('.') === -1 ? nombreEnChaine.indexOf(',') : nombreEnChaine.indexOf('.');

    if (indiceSeparateur === -1) {
        // Le nombre est un nombre entier, il n'y a pas de décimales
        return 0;
    } else {
        // Le nombre possède des décimales, il faut retourner le nombre de chiffres après le séparateur
        return nombreEnChaine.length - indiceSeparateur - 1;
    }
}

/**
 * Retourne la valeur passée en paramètre dans le format demandé
 * @param {number} valeur nombre à formater
 * @param {string} format par défaut €
 * @param {number} decimale à définir uniquement si le format est ''
 * @param {string} separateur à définir uniquement si le format est ''
 * @returns {string}
 */

export function formater(valeur, {format = '€', decimale = 2, separateur = '.'} = {}) {
    let resultat;
    if (format === '€') {
        resultat = valeur.toLocaleString('fr-FR', {style: 'currency', currency: 'EUR'});
    } else if (format === '%') {
        // recherche du nombre de décimales pour le pourcentage en fonction de la valeur initale
        const decimal = Math.max(nbDecimale(valeur) - 2, 0);
        resultat = valeur.toLocaleString('fr-FR', {
            style: 'percent',
            minimumFractionDigits: decimal,
            maximumFractionDigits: decimal
        });
    } else if (format.toLowerCase() === '') {
        resultat = valeur.toLocaleString('fr-FR', {minimumFractionDigits: decimale, maximumFractionDigits: decimale});
    } else if (format.toLowerCase() === 'km/h') {
        // pour le format km/h, on arrondit à 2 décimales
        resultat = valeur.toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' km/h';
    }
    // recherche du séparateur courant
    // Obtenir la langue par défaut du navigateur
    const langue = navigator.language;

    // récupérer le séparateur décimal par défaut du navigateur
    const separateurDecimal = langue.includes('fr') ? ',' : '.';

    if (separateur !== separateurDecimal) {
        // la variable resultat est de type Number si le format est différent de % ou €
        resultat = resultat.toString().replace(separateurDecimal, separateur);
    }
    return resultat;
}

/**
 * Retourne le numéro de téléphone passé en paramètre dans le format demandé
 *
 * @param {string} telephone un numéro de téléphone à mettre en forme
 * @param {string} separateur  le séparateur à utiliser entre les groupes de chiffres
 * @returns {string}
 */
export function miseEnFormeTelephone(telephone, separateur = '.') {
    // return telephone.substring(0, 2) + separateur + telephone.substring(2, 4) + separateur + telephone.substring(4, 6) + separateur + telephone.substring(6, 8) + separateur + telephone.substring(8);
    // Supprimer d'éventuels caractères non numériques du numéro
    const numero = telephone.replace(/\D/g, '');

    // Créer un tableau pour stocker les paires de chiffres
    const paires = [];

    // Diviser le numéro en paires de chiffres
    for (let i = 0; i < numero.length; i += 2) {
        paires.push(numero.slice(i, i + 2));
    }

    // Joindre les paires avec le séparateur souhaité (par exemple, '-')
    return paires.join(separateur);
}

/**
 *  Arrondit la valeur passée en argument avec la précision demandée
 * @param {int} valeur
 * @param {int} precision
 * @returns {number}
 */
export function arrondir(valeur, precision = 0) {
    const tmp = 10 ** precision;
    return Math.round(valeur * tmp) / tmp;
}

/**
 *  Convertit un nombre exprimé en octet dans une autre unité (Ko, Mo ou Go)
 *  @param {number} nb nombre représentant un nombre d'octets
 *  @param {string} unite unité souhaitée : Ko Mo ou Go
 *  @return {string}  nombre exprimé dans l'unité avec une mise en forme par groupe de 3 chiffres
 */
export function conversionOctet(nb, unite = 'o') {
    let diviseur = 1;
    if (unite === 'Ko') {
        diviseur = 1024;
    } else if (unite === 'Mo') {
        diviseur = 1024 * 1024;
    } else if (unite === 'Go') {
        diviseur = 1024 * 1024 * 1024;
    }
    let str = Math.round(nb / diviseur).toString();
    let result = str.slice(-3);
    str = str.substring(0, str.length - 3);  // sans les trois derniers
    while (str.length > 3) {
        const elt = str.slice(-3);
        result = elt.concat(' ', result);
        str = str.substring(0, str.length - 3);
    }
    result = str.concat(result, ' ', unite);
    return result;
}