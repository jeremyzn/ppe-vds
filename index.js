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
        dernieresNouvelles.innerHTML = '<div class="p-2 text-muted">Aucune information pour le moment.</div>';
        dernieresNouvelles.style.display = 'block';
    } else {
        // génération avec des cards Bootstrap pour un rendu plus propre
        let html = '';
        lesInformations.forEach((info, idx) => {
            html += `<div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">${info.titre}</h5>
                    <span class="badge bg-secondary ms-2">${info.type}</span>
                </div>
                <div class="card-body content-html">${info.contenu}</div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div class="text-muted"><small>Par ${info.auteur || '—'}</small></div>
                    <div class="doc-actions">`;
            if (info.documents && info.documents.length > 0) {
                info.documents.forEach(docId => {
                    html += `<a href="/afficherdocumentinformation.php?id=${docId}" target="_blank" class="btn btn-sm btn-outline-primary ms-1">PDF</a>`;
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
            // limiter la largeur pour éviter de casser la mise en page
            img.style.maxWidth = '100%';
            img.style.height = 'auto';
        });
        dernieresNouvelles.style.display = 'block';
    }
}








