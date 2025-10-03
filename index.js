"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------

import {initialiserToutesLesCartes, basculerToutesLesCartes} from "/composant/fonction/openclose.js?";
import {formatDateLong} from "/composant/fonction/date.js";

// -----------------------------------------------------------------------------------
// Déclaration des variables globales
// -----------------------------------------------------------------------------------

/* global prochaineEdition, lesClassements, lesInformations*/

// Récupération des éléments de l'interface
const detailClassement = document.getElementById('detailClassement');
const dernieresNouvelles = document.getElementById('dernieres-nouvelles');
const dateEpreuve = document.getElementById('dateEpreuve');
const descriptionEpreuve = document.getElementById('descriptionEpreuve');
const btnOuvrirToutes = document.getElementById('btnOuvrirToutes');
const btnFermerToutes = document.getElementById('btnFermerToutes');

// -----------------------------------------------------------------------------------
// Procédures évènementielles
// -----------------------------------------------------------------------------------

btnOuvrirToutes.onclick = () => basculerToutesLesCartes(true);
btnFermerToutes.onclick = () => basculerToutesLesCartes(false); // fermer


// -----------------------------------------------------------------------------------
// Programme principal
// -----------------------------------------------------------------------------------

// Mise en place du système d'ouverture/fermeture des cadres
initialiserToutesLesCartes();

// les informations

// affichage de la prochaine épreuve
dateEpreuve.innerText =  formatDateLong(prochaineEdition.date);
descriptionEpreuve.innerHTML = prochaineEdition.description;


// afficher les derniers classements pdf
for (const element of lesClassements) {
    let a = document.createElement('a');
    a.classList.add('lien'),
        a.href = "/afficherclassement.php?id=" + element.id;
    a.innerText = element.dateFr + ' ' + element.titre;
    detailClassement.appendChild(a);
}

// afficher les dernières informations
if (typeof lesInformations !== 'undefined' && dernieresNouvelles) {
    // si pas d'informations, afficher un message convivial
    if (!Array.isArray(lesInformations) || lesInformations.length === 0) {
        dernieresNouvelles.innerHTML = '<div class="p-2 text-muted" style="padding-left: 10px;">Aucune information pour le moment.</div>';
    } else {
        // génération avec le même style visuel que les autres sections
        let html = '';
        lesInformations.forEach((info) => {
            // Créer un bloc pour chaque information sans la classe .lien (pour éviter le hover)
            html += `<div style="display: block; text-align: left; margin-top: 4px; margin-left: 0.5rem; margin-right: 0.5rem; padding: 0.5rem; border: 1px solid #0790e4; background-color: #f8f9fa; border-radius: 0.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <strong style="color: #0790e4; font-size: 1.1em;">${info.titre}</strong>
                    <span class="badge bg-secondary" style="font-size: 0.75em;">${info.type}</span>
                </div>
                <div class="content-html" style="margin-bottom: 8px;">${info.contenu}</div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #dee2e6; padding-top: 6px;">
                    <small class="text-muted">Par ${info.auteur || '—'}</small>
                    <div class="doc-actions">`;
            if (info.documents && info.documents.length > 0) {
                info.documents.forEach(doc => {
                    // Nettoyer le nom du fichier pour l'affichage (enlever le timestamp si présent)
                    let nomAffichage = doc.fichier;
                    nomAffichage = nomAffichage.replace(/_\d{8}-\d{6}(_\d+)?\.pdf$/i, '.pdf');
                    html += `<a href="/afficherdocumentinformation.php?id=${doc.id}" target="_blank" class="btn btn-sm btn-outline-primary ms-1" style="font-size: 0.75em;" title="${doc.fichier}">📄 ${nomAffichage}</a>`;
                });
            }
            html += `</div>
                </div>
            </div>`;
        });
        dernieresNouvelles.innerHTML = html;
        // rendre les images responsives si présentes dans le contenu
        dernieresNouvelles.querySelectorAll('.content-html img').forEach(img => {
            img.classList.add('img-fluid');
            img.style.maxWidth = '100%';
            img.style.height = 'auto';
        });
    }
}








