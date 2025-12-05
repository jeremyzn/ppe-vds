import {appelAjax} from '/composant/fonction/ajax.js';
import {messageBox} from '/composant/fonction/afficher.js';

/*
  inscription/index.js
  - Utilise les composants partagés pour réduire le code (appelAjax, messageBox)
*/

document.addEventListener('DOMContentLoaded', () => {
    const btnEpreuve = document.getElementById('btn-date-epreuve');
    const btnOuverture = document.getElementById('btn-date-ouverture');
    const btnCloture = document.getElementById('btn-date-cloture');
    const liste = document.getElementById('liste-inscriptions');
    if (!liste) return;

    // Crée rapidement un bouton (utilitaire simple)
    function makeBtn(text, href, cls = 'btn-primary') {
        const a = document.createElement('a');
        a.className = `btn ${cls} me-2`;
        a.href = href;
        a.target = '_blank';
        a.rel = 'noopener noreferrer';
        a.innerText = text;
        return a;
    }

    // Afficher les éléments renvoyés par l'API
    function renderItems(items) {
        if (!items || items.length === 0) {
            liste.innerHTML = '<p class="muted">Aucune inscription ouverte pour le moment.</p>';
            return;
        }

        liste.innerHTML = '';
        for (const item of items) {
            const card = document.createElement('div');
            card.className = 'card mb-3';
            card.innerHTML = `
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <div>${formatDateFr(item.dateEpreuve)} - ${item.nom}</div>
                </div>
                <div class="card-body">
                    <div class="mb-2" id="actions-${item.id}"></div>
                    <div>Date limite d'inscription : ${formatDateFr(item.dateCloture)}</div>
                </div>`;

            liste.appendChild(card);

            const actions = document.getElementById(`actions-${item.id}`);
            if (item.lienInscription && item.lienInscription !== '0') {
                actions.appendChild(makeBtn("S'inscrire", item.lienInscription, 'btn-danger'));
            }
            if (item.lienInscrit && item.lienInscrit !== '0') {
                actions.appendChild(makeBtn('Voir les inscrits', item.lienInscrit, 'btn-primary'));
            } else {
                const span = document.createElement('span');
                span.className = 'muted';
                span.innerText = '(Inscrit: Non défini)';
                actions.appendChild(span);
            }
        }
    }

    // Chargement des données
    function loadAndRender(sortField = null) {
        appelAjax({
            url: '/administration/inscription/ajax/lister.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                if (!data) {
                    liste.innerHTML = '<p class="muted">Impossible de charger les inscriptions pour le moment.</p>';
                    return;
                }
                if (sortField) {
                    data.sort(function(a, b) {
                        return (a[sortField] || '').localeCompare(b[sortField] || '');
                    });
                }
                renderItems(data);
            },
            error: function() {
                liste.innerHTML = '<p class="muted">Impossible de charger les inscriptions pour le moment.</p>';
            }
        });
    }

    // simple: convertit YYYY-MM-DD en '16 novembre 2025'
    function formatDateFr(dateStr) {
        if (!dateStr) return '';
        const parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        const months = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
        const y = parts[0], m = parseInt(parts[1], 10), d = parseInt(parts[2], 10);
        if (isNaN(m) || isNaN(d)) return dateStr;
        return d + ' ' + months[m - 1] + ' ' + y;
    }

    // Chargement initial
    loadAndRender();

    // Tri / filtre simple pour débutant
    btnEpreuve?.addEventListener('click', () => loadAndRender('dateEpreuve'));
    btnOuverture?.addEventListener('click', () => loadAndRender('dateOuverture'));
    btnCloture?.addEventListener('click', () => loadAndRender('dateCloture'));
});
