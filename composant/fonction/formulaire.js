// Active le mode strict afin d'éviter les erreurs silencieuses et d'imposer un JS plus rigoureux.
"use strict";

// ============================================================================
// Version      : 2025.4
// Date         : 09/09/2025
// Historique   :
//   ✓ Correctif de l'affichage des erreurs pour les champs input
//   ✓ Suppression du paramètre onInput dans configurerFormulaire (désuet)
//   ✓ Nouvelle fonction configurerDate (min, max, valeur, label dynamique)
//   ✓ Ajustement de verifierDimensionsImage pour intégrer redimmensionner
//   ✓ Ajout d’un paramètre 'zone' à effacerLesErreurs pour ciblage local
//   ✓ Ajout des fonctions creerBoutonAction pour générer des boutons d'action
//   ✓ Ajout de la fonction creerCheckbox pour générer des cases à cocher
//   ✓ Ajout de la fonction creerSelect pour générer des éléments <select> stylisés
//   ✓ Ajout de la fonction creerInputTexte pour générer des champs <input type="text">
//   ✓ Ajout de la fonction supprimerEspace pour nettoyer les espaces superflus
//   ✓ Ajout de la fonction enleverAccent pour supprimer les accents d'une chaîne
//   ✓ Ajout de la fonction enleverAccentEtMajuscule pour supprimer les accents et les majuscules
//   ✓ Modification de donneesValides et configurerformulaire pour exclure les cases à cocher
//   ✓ utilisation d'un chemin relatif pour l'import de afficher.js
// ============================================================================

// Import de fonctions utilitaires depuis un module externe

import {afficherSousLeChamp, messageBox} from '/composant/fonction/afficher.js';

/**
 * Prépare dynamiquement le DOM en insérant une div.messageErreur après chaque champ de saisie.
 * Cette div servira à afficher des messages de validation personnalisés.
 */
export function configurerFormulaire() {
    // Ajout du style une seule fois
    if (!document.getElementById('style-message-erreur')) {
        const style = document.createElement('style');
        style.id = 'style-message-erreur';
        style.textContent = `
            .messageErreur {
                font-size: 0.8rem;
                color: #c00;
                font-style: italic;
                margin : 0;
            }

            @media (max-width: 600px) {
                .messageErreur {
                    margin-left: 0 !important;
                    padding-left: 0 !important;
                }
            }
        `;
        document.head.appendChild(style);
    }

    // Parcours de tous les champs de formulaire, sauf les cases à cocher
    const elements = document.querySelectorAll('input:not([type="checkbox"]), select, textarea');

    for (const element of elements) {
        const parent = element.parentElement;
        let conteneurChamp = null;

        // Vérifie si l'élément est contenu dans une div.champ
        if (parent && parent.classList.contains('champ')) {
            conteneurChamp = parent;
        }

        // Détermine si un message d'erreur existe déjà (pour éviter les doublons)
        let messageDejaPresent = false;

        if (conteneurChamp !== null) {
            const elementSuivant = conteneurChamp.nextElementSibling;
            if (elementSuivant && elementSuivant.classList.contains('messageErreur')) {
                messageDejaPresent = true;
            }
        } else {
            const elementSuivant = element.nextElementSibling;
            if (elementSuivant && elementSuivant.classList.contains('messageErreur')) {
                messageDejaPresent = true;
            }
        }

        if (messageDejaPresent) {
            continue;
        }

        // Création du conteneur du message
        const divMessage = document.createElement('div');
        divMessage.classList.add('messageErreur');

        // Insertion du message après le champ ou après le conteneur .champ
        if (conteneurChamp !== null) {
            conteneurChamp.insertAdjacentElement('afterend', divMessage);
        } else {
            element.insertAdjacentElement('afterend', divMessage);
        }
    }
}

/**
 * Configure dynamiquement les contraintes min/max et la valeur d’un champ <input type="date">
 * et met à jour le label associé pour expliciter les règles à l'utilisateur.
 *
 * @param {HTMLInputElement} inputDate - Élément <input type="date"> à configurer (obligatoire)
 * @param {Object} [options] - Options de configuration
 * @param {string} [options.min] - Date minimale (format YYYY-MM-DD)
 * @param {string} [options.max] - Date maximale (format YYYY-MM-DD)
 * @param {string|null} [options.valeur] - Valeur initiale à définir (sinon min ou vide)
 */
export function configurerDate(inputDate, {min = null, max = null, valeur = null} = {}) {
    if (!inputDate || !(inputDate instanceof HTMLInputElement)) {
        console.error("Le paramètre fourni n'est pas un élément <input type='date'> valide.");
        return;
    }

    // Cas limite : ni min ni max → on ne fait rien
    if (!min && !max) {
        console.warn(`Aucune contrainte min/max spécifiée pour ${inputDate.id} — configuration ignorée.`);
        return;
    }

    if (min) {
        inputDate.min = min;
    }
    if (max) {
        inputDate.max = max;
    }
    inputDate.value = valeur || '';

    const formatFr = {day: '2-digit', month: '2-digit', year: 'numeric'};
    const minFr = min ? new Date(min).toLocaleDateString('fr-FR', formatFr) : null;
    const maxFr = max ? new Date(max).toLocaleDateString('fr-FR', formatFr) : null;

    let commentaire = '';
    if (min && max) {
        commentaire = `La date doit être comprise entre le ${minFr} et le ${maxFr}`;
    } else if (min) {
        commentaire = `La date ne peut être antérieure au ${minFr}`;
    } else if (max) {
        commentaire = `La date ne peut excéder le ${maxFr}`;
    }

    const label = document.querySelector(`label[for="${inputDate.id}"]`);
    if (label && commentaire) {
        // Supprimer un éventuel ancien commentaire pour éviter les doublons
        const ancienCommentaire = label.querySelector('.commentaire');
        if (ancienCommentaire) {
            ancienCommentaire.remove();
        }

        // Créer et injecter un nouveau commentaire
        const span = document.createElement('span');
        span.className = 'commentaire';
        span.textContent = ` (${commentaire})`;
        label.appendChild(span);
    }
}


/**
 * Intercepte les frappes clavier dans un champ <input> pour restreindre les caractères autorisés via RegExp.
 * @param {string} idInput attribut id de la balise input
 * @param {RegExp} regExp expression régulière contenant les caractères autorisés
 */
export function filtrerLaSaisie(idInput, regExp) {
    const input = document.getElementById(idInput);
    if (input) {
        input.addEventListener('keydown', (e) => {
            // Autoriser le passage des touches spéciales
            if (e.key.length > 1) {
                return;
            }
            // Vérifier si la touche est un chiffre
            if (!regExp.test(e.key)) {
                e.preventDefault(); // Empêcher la saisie de caractères non contenus dans l'expression régulière
            }
        });
    } else {
        console.error(`L'élément d'entrée ${idInput} n'existe pas.`);
    }
}

/**
 * Valide un champ de formulaire via les API HTML5 de validation.
 * En cas d’échec, bordure rouge + messageBox en modal.
 * @param {object} input balise input
 * @returns {boolean} true si la valeur saisie est valide
 */
export function verifier(input) {
    input.title = input.validationMessage;
    if (input.checkValidity()) {
        input.style.borderColor = '';
        return true;
    } else {
        input.style.borderColor = 'red';
        messageBox(input.validationMessage, "error");
        return false;
    }
}

// Import de la fonction utilitaire pour convertir les octets
import {conversionOctet} from './format.js';

/**
 * Valide un fichier en vérifiant sa taille et son extension, et affiche éventuellement un message d'erreur.
 *
 * @param {File} file - Le fichier à valider.
 * @param {Object} options - Options de validation.
 * @param {number} [options.maxSize] - Taille maximale autorisée en octets.
 * @param {string[]} [options.extensions] - Extensions autorisées (ex: ['pdf', 'docx']).
 * @returns {boolean} - true si le fichier est valide, false sinon.
 */
export function fichierValide(file, options = {}) {
    // extraction depuis l'objet options de trois propriétés
    const {maxSize, extensions} = options;
    let message = '';

    if (!file) {
        message = 'Aucun fichier transmis';
    } else if (maxSize && file.size > maxSize) {
        const size = conversionOctet(file.size, 'Ko');
        const taille = conversionOctet(maxSize, 'Ko');
        message = `La taille du fichier (${size}) dépasse la taille autorisée (${taille})`;
    } else if (extensions) {
        const extension = file.name.split('.').pop().toLowerCase();
        if (!extensions.includes(extension)) {
            message = `Extension ${extension} non acceptée`;
        }
    }
    // affiche le message sous le champ 'fichier' ou dans une fenêtre modale s'il n'existe pas
    if (message !== '') {
        afficherSousLeChamp('fichier', message);
    }
    return message === '';
}

/**
 * Vérifie les dimensions d’une image si un contrôle est requis.
 * @param {File} file - Le fichier image à tester.
 * @param {Object} lesParametres - Paramètres de validation contenant les dimensions max et le flag de redimensionnement.
 * @param {Function} onSuccess - Callback si les dimensions sont valides qui reçoit en paramètre l'objet file et l'objet img.
 */
export function verifierDimensionsImage(file, lesParametres, onSuccess = {}) {
    const img = new Image();
    img.src = URL.createObjectURL(file);

    img.onload = () => {
        let message = null; // Variable pour stocker le message d'erreur si nécessaire

        // faut-il vérifier les dimensions de l'image ?
        if (!lesParametres.redimmensionner) {

            // Récupération des dimensions max à partir de l'objet lesParametres
            const dimensions = {
                width: lesParametres.width || 0, // Largeur max, 0 si non spécifiée
                height: lesParametres.height || 0 // Hauteur max, 0 si non spécifiée
            };

            // Cas 1 : Les deux dimensions (largeur et hauteur) sont renseignées
            if (dimensions.width !== 0 && dimensions.height !== 0) {
                if (img.width > dimensions.width || img.height > dimensions.height) {
                    message = `Les dimensions de l'image (${img.width}×${img.height}) dépassent la limite autorisée (${dimensions.width}×${dimensions.height})`;
                }
            }
            // Cas 2 : Seule la largeur est renseignée
            else if (dimensions.width !== 0) {
                if (img.width > dimensions.width) {
                    message = `La largeur de l'image (${img.width}) dépasse la limite autorisée (${dimensions.width})`;
                }
            }
            // Cas 3 : Seule la hauteur est renseignée
            else if (dimensions.height !== 0) {
                if (img.height > dimensions.height) {
                    message = `La hauteur de l'image (${img.height}) dépasse la limite autorisée (${dimensions.height})`;
                }
            }
            // Cas 4 : Aucune dimension n'est renseignée (pas de vérification nécessaire)
            // Dans ce cas, 'message' restera null
        }

        // Traitement final basé sur le résultat de la vérification
        if (message) {
            afficherSousLeChamp('fichier', message);
        } else {
            onSuccess?.(file, img);
        }

        // Nettoyage de l'URL temporaire
        URL.revokeObjectURL(img.src);
    };

    img.onerror = () => {
        afficherSousLeChamp('fichier', "Le fichier n'est pas une image valide.");
        URL.revokeObjectURL(img.src); // Nettoyer aussi en cas d'erreur de chargement
    };
}

/**
 * Contrôle tous les champs input et textarea et select
 * chaque champ xxx doit être suivi d'une balise <div class='messageErreur'></div> pour afficher le message d'erreur : méthode configurerFormulaire
 * @param {Node} [zone=document] - La zone dans laquelle les champs doivent être contrôlés.
 * @returns {boolean} true si tous les champs respectent les contraintes définies dans leurs attributs pattern, minlength, maxlength, required, min, max ...
 */
export function donneesValides(zone = document) {
    let valide = true;

    // Sélectionner tous les éléments input et select qui sont required et non désactivés, sauf les cases à cocher
    const lesInputs = zone.querySelectorAll('input[required]:not([disabled]):not([type="checkbox"]), select[required]:not([disabled])');

    // Parcourir et traiter les éléments sélectionnés
    lesInputs.forEach(x => {
        afficherSousLeChamp(x.id);
        if (!x.checkValidity()) {
            valide = false;
        }
    });

    // Vérifier séparément les champs non-required qui ont une valeur
    const champsNonRequired = zone.querySelectorAll('input:not([required]):not([disabled]):not([type="checkbox"]), select:not([required]):not([disabled])');
    champsNonRequired.forEach(x => {
        if (x.value !== '') {
            afficherSousLeChamp(x.id);
            if (!x.checkValidity()) {
                valide = false;
            }
        }
    });
    return valide;
}

/**
 * Controle la validité de la date saisie (format jj/mm/aaaa) dans la balise input dont l'id est trasnmis en paramètre
 * @param {string} idInput attribut id de la balise input
 * @returns {boolean} true si la date est valide
 */
export function dateValide(idInput) {
    const input = document.getElementById(idInput);

    if (input) {
        // Vérifier le format jj/mm/aaaa avec une expression régulière
        const dateRegex = /^\d{1,2}\/\d{1,2}\/\d{4}$/;
        if (!dateRegex.test(input.value)) {
            afficherSousLeChamp(idInput, 'Cette date ne respecte pas le format attendu (jj/mm/aaaa)');
            return false; // Le format n'est pas correct
        }

        // Récupération des éléments de la date
        const [jour, mois, annee] = input.value.split('/').map(Number);

        // création d'un objet Date avec les éléments de la date
        const date = new Date(annee, mois - 1, jour);

        // La date est valide si l'année, le mois et le jour sont identiques à ceux de l'objet Date
        if (date.getFullYear() === annee && date.getMonth() === mois - 1 && date.getDate() === jour) {
            return true;
        } else {
            afficherSousLeChamp(idInput, 'Cette date n\'est pas valide');
            return false;
        }
    } else {
        console.error(`L'élément d'entrée ${idInput} n'existe pas.`);
    }
}

/**
 * Vide les champs de formulaire (input et textarea) dans une zone spécifiée.
 *
 * @param {Node} [zone=document] - La zone dans laquelle les champs doivent être vidés.
 * Par défaut, il s'agit du document entier.
 */
export function effacerLesChamps(zone = document) {
    for (const input of zone.querySelectorAll('input, textarea')) {
        input.value = '';
    }
}

/**
 * Supprime les messages d'erreur visibles dans une zone, y compris ceux de la zone #msg centrale
 */
export function effacerLesErreurs(zone = document) {
    for (const div of zone.getElementsByClassName('messageErreur')) {
        div.innerText = '';
    }
    const msg = document.getElementById('msg');
    if (msg) {
        msg.innerHTML = "";
    }
}


/**
 * Crée dynamiquement un bouton d'action (icône cliquable)
 *
 * @param {Object} options - Paramètres de configuration du bouton
 * @param {string} options.icone - Symbole ou texte affiché (ex: ✎, ✘)
 * @param {string} [options.couleur='black'] - Couleur du symbole
 * @param {string} [options.titre=''] - Info-bulle (attribut title)
 * @param {function} [options.action] - Fonction de rappel exécutée au clic
 * @returns {HTMLElement} Élément <span> configuré
 */
export function creerBoutonAction({
                                      icone,
                                      couleur = 'black',
                                      titre = '',
                                      action = null
                                  }) {
    const bouton = document.createElement('span');
    bouton.textContent = icone;
    bouton.title = titre;

    Object.assign(bouton.style, {
        color: couleur,
        cursor: 'pointer'
    });

    if (typeof action === 'function') {
        bouton.addEventListener('click', action);
    }

    return bouton;
}

export function creerBoutonModification(action) {
    return creerBoutonAction({
        icone: '✎',
        couleur: 'orange',
        titre: 'Modifier l\'enregistrement',
        action: action
    });
}


export function creerBoutonSuppression(action) {
    return creerBoutonAction({
        icone: '✘',
        couleur: 'red',
        titre: 'Supprimer l\'enregistrement',
        action: action
    });
}

export function creerBoutonRemplacer(action) {
    return creerBoutonAction({
        icone: '♻️',
        couleur: 'red',
        titre: "Téléverser une nouvelle version du document PDF",
        action: action
    });
}

export function creerCheckbox() {
    const input = document.createElement('input');
    input.type = 'checkbox';
    input.classList.add("form-check-input", "my-auto", "m-3");
    input.style.width = '25px';
    input.style.height = '25px';
    return input;
}

/**
 * Crée un élément <select> stylisé.
 * @param {Array<string>} [classes=[]] - Liste de classes CSS à appliquer.
 * @returns {HTMLSelectElement}
 */
export function creerSelect(classes = []) {
    const select = document.createElement('select');
    select.classList.add(...classes);
    return select;
}


/**
 * Crée un champ <input type="text"> stylisé.
 * @param {Object} [options={}]
 * @param {string} [options.placeholder] - Texte d’aide visuel.
 * @param {Array<string>} [options.classes] - Classes CSS à appliquer.
 * @returns {HTMLInputElement}
 */
export function creerInputTexte({placeholder = '', classes = []} = {}) {
    const input = document.createElement('input');
    input.type = 'text';
    input.placeholder = placeholder;
    input.classList.add(...classes);
    return input;
}


/**
 * Supprime les espaces superflus au début, à la fin et à l'intérieur de la valeur
 * @param {string} valeur
 * @returns {string} valeur sans espace superflu au début, à la fin et à l'intérieur
 */
export function supprimerEspace(valeur) {
    return valeur.trim().replace(/ {2,}/g, ' ');
}

/**
 *   Enlève les accents
 *   en normalisant la chaine dans le jeu de caractère unicode, la lettre et son accent sont décomposés en deux codes
 *   tous les accents sont codés entre 0300 et 036f, il suffit donc de les remplacer par une chaîne vide
 *   https://stackoverflow.com/questions/990904/remove-accents-diacritics-in-a-string-in-javascript
 */
export const enleverAccent = (valeur) => valeur.normalize('NFD').replace(/[\u0300-\u036f]/g, '');


/**
 * Supprime les accents d'une chaîne de caractères
 * @param str
 * @returns {*}
 */
export function enleverAccentEtMajuscule(str) {
    return enleverAccent(str).toLowerCase();
}

/**
 * Compare 2 chaînes sans tenir compte des accents et de la casse
 * @param {string} str1 chaîne à comparer
 * @param {string} str2 chaîne à comparer
 * @returns {boolean} true si les 2 chaînes sont identiques
 */
export function comparerSansAccentEtSansCasse(str1, str2) {
    const x = enleverAccentEtMajuscule(str1);
    const y = enleverAccentEtMajuscule(str2);
    return x === y;
}

/**
 * Compare 2 chaînes sans tenir compte de la casse
 * @param {string} str1 chaîne à comparer
 * @param {string} str2 chaîne à comparer
 * @returns {boolean} true si les 2 chaînes sont identiques
 */
export function comparerSansCasse(str1, str2) {
    return str1.toLowerCase() === str2.toLowerCase();
}

/**
 * Vérifie si une chaîne de caractères contient une autre chaîne, sans tenir compte des accents et de la casse
 * @param texte
 * @param recherche
 * @returns {*}
 */
export function contenir(texte, recherche) {
    const txt = enleverAccentEtMajuscule(texte);
    const cherche = enleverAccentEtMajuscule(recherche)
    return txt.includes(cherche);
}
