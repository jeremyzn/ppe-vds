"use strict";

// -----------------------------------------------------------------------------------
// Import des fonctions nécessaires
// -----------------------------------------------------------------------------------

import { initialiserToutesLesCartes, basculerToutesLesCartes } from "/composant/fonction/openclose.js?";
import { formatDateLong } from "/composant/fonction/date.js";

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
dateEpreuve.innerText = formatDateLong(prochaineEdition.date);
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
        // Ajout d'un bouton ouvrir/fermer par information (persisté dans localStorage)
        let html = '';
        lesInformations.forEach((info, idx) => {
            const uid = info.id ? `info-${info.id}` : `info-${idx}`;
            // calculer la date d'affichage (préférer date_modif si présente)
            const dateSource = info.date_modif || info.date_creation || null;
            const dateAffichage = dateSource ? new Date(dateSource).toLocaleDateString('fr-FR', { timeZone: 'Europe/Paris' }) : '';
            // badge 'Nouveau' pour les informations récentes (par défaut 7 jours)
            const NEW_DAYS = 7;
            const isNew = dateSource ? (Date.now() - new Date(dateSource).getTime()) <= (NEW_DAYS * 24 * 60 * 60 * 1000) : false;
            // ne pas afficher le libellé 'Publique' ; afficher le type seulement s'il n'est pas 'Publique'
            let badgeHtml = '';
            if (isNew) {
                badgeHtml = `<span class="badge bg-danger" style="font-size: 0.75em;">Nouveau</span>`;
            } else if (info.type && info.type !== 'Publique') {
                badgeHtml = `<span class="badge bg-secondary" style="font-size: 0.75em;">${info.type}</span>`;
            }
            // Créer un bloc pour chaque information avec un toggle
            html += `<div class="info-item" style="display: block; text-align: left; margin-top: 4px; margin-left: 0.5rem; margin-right: 0.5rem; padding: 0.5rem; border: 1px solid #0790e4; background-color: #f8f9fa; border-radius: 0.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <strong style="color: #0790e4; font-size: 1.1em;">${info.titre}</strong>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                            ${badgeHtml}
                        <button class="toggle-news btn btn-sm btn-outline-secondary" data-target="${uid}" aria-expanded="true" style="font-size:0.8rem;">▲</button>
                    </div>
                </div>
                <div id="${uid}" class="content-html" style="margin-bottom: 8px;">
                    ${info.contenu}
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #dee2e6; padding-top: 6px;">
                        <small class="text-muted">${(dateAffichage && info.auteur) ? `Publié le ${dateAffichage} • ${info.auteur}` : (dateAffichage ? `Publié le ${dateAffichage}` : (info.auteur ? `Publié par ${info.auteur}` : ''))}</small>
                        <div class="doc-actions">`;
            if (info.documents && info.documents.length > 0) {
                info.documents.forEach(doc => {
                    // Préférer le nom original si présent, sinon nettoyer le nom stocké (supprimer suffixe timestamp)
                    let nomAffichage = doc.nom_original || doc.fichier;
                    if (!doc.nom_original && typeof nomAffichage === 'string') {
                        // Supporter les deux formats de timestamp possibles (_YYYYMMDD-HHMMSS ou _YYYYMMDDHHMMSS) et suffixe incrémental
                        nomAffichage = nomAffichage.replace(/_(?:\d{8}-\d{6}|\d{14})(?:\(\d+\))?(?=\.[^.]+$)/i, '');
                    }
                    html += `<a href="/afficherdocumentinformation.php?id=${doc.id}" target="_blank" class="btn btn-sm btn-outline-primary ms-1" style="font-size: 0.75em;" title="${doc.fichier}">📄 ${nomAffichage}</a>`;
                });
            }
            html += `</div>
                    </div>
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

        // Gestion des toggles par information avec persistance
        const KEY_ETAT_NOUV = 'etatNouvelles';
        const etatsNouv = JSON.parse(localStorage.getItem(KEY_ETAT_NOUV)) || {};
        dernieresNouvelles.querySelectorAll('.toggle-news').forEach(btn => {
            const targetId = btn.getAttribute('data-target');
            const contenu = document.getElementById(targetId);
            if (!contenu) return;

            // Restaurer l'état si présent
            const ouvert = etatsNouv[targetId] !== undefined ? etatsNouv[targetId] : true;
            contenu.style.display = ouvert ? '' : 'none';
            btn.innerText = ouvert ? '▲' : '▼';
            btn.setAttribute('aria-expanded', ouvert ? 'true' : 'false');

            btn.onclick = (e) => {
                e.stopPropagation();
                const visible = window.getComputedStyle(contenu).display !== 'none';
                const afficher = !visible;

                // Si on ouvre cet élément, fermer tous les autres (comportement accordéon)
                if (afficher) {
                    dernieresNouvelles.querySelectorAll('.toggle-news').forEach(otherBtn => {
                        const otherTarget = otherBtn.getAttribute('data-target');
                        if (!otherTarget || otherTarget === targetId) return;
                        const otherContent = document.getElementById(otherTarget);
                        if (!otherContent) return;
                        otherContent.style.display = 'none';
                        otherBtn.innerText = '▼';
                        otherBtn.setAttribute('aria-expanded', 'false');
                        etatsNouv[otherTarget] = false;
                    });
                }

                // basculer l'élément cliqué
                contenu.style.display = afficher ? '' : 'none';
                btn.innerText = afficher ? '▲' : '▼';
                btn.setAttribute('aria-expanded', afficher ? 'true' : 'false');
                etatsNouv[targetId] = afficher;
                localStorage.setItem(KEY_ETAT_NOUV, JSON.stringify(etatsNouv));
            };
        });
    }
}
