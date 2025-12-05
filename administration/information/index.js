"use strict";

/**
 * Gestion des informations (CRUD)
 * - Affichage du tableau des informations
 * - Ajout/modification via modal avec TinyMCE
 * - Gestion des documents PDF associés (upload multiple, suppression différée)
 */

import { appelAjax } from "/composant/fonction/ajax.js";
import { confirmer, afficherToast } from "/composant/fonction/afficher.js";
import { configurerFormulaire, effacerLesErreurs, fichierValide, creerBoutonSuppression, creerBoutonModification } from "/composant/fonction/formulaire.js";

/* global lesInformations, lesDocumentsInfo, tinymce */

// ============================================================================
// ÉLÉMENTS DU DOM
// ============================================================================

const lesLignes = document.getElementById('lesLignes');
const nb = document.getElementById('nb');
const btnAjouter = document.getElementById('btnAjouter');
const formModal = document.getElementById('formModal');
const formTitle = document.getElementById('formTitle');
const btnCancel = document.getElementById('btnCancel');
const btnCloseModal = document.getElementById('btnCloseModal');
const form = document.getElementById('formInfo');
const fichierInput = document.getElementById('fichierModal');
const btnPickFile = document.getElementById('btnPickFile');
const listeFichiers = document.getElementById('listeFichiersAssocies');

configurerFormulaire();

// ============================================================================
// FONCTIONS UTILITAIRES
// ============================================================================

/** Affiche un placeholder "Aucun fichier" dans le tableau des documents */
function montrerAucunFichier() {
    if (!listeFichiers) return;
    listeFichiers.innerHTML = '';
    const tr = document.createElement('tr');
    tr.id = 'placeholderNoFile';
    const td = tr.insertCell();
    td.colSpan = 2;
    td.className = 'text-muted';
    td.textContent = 'Aucun fichier';
    listeFichiers.appendChild(tr);
}

/** Supprime le placeholder "Aucun fichier" s'il existe */
function supprimerPlaceholderSiPresent() {
    if (!listeFichiers) return;
    const p = document.getElementById('placeholderNoFile');
    if (p) p.remove();
}

/** Longueur maximale du titre affiché dans le tableau (tronqué au-delà) */
const TITRE_MAX_LENGTH = 30;

/** Injecte le style CSS pour la ligne en cours d'édition */
function ensureEditingStyle() {
    if (document.getElementById('style-row-editing')) return;
    const style = document.createElement('style');
    style.id = 'style-row-editing';
    style.textContent = `
        .row-editing td { background-color: #90caf9 !important; }
    `;
    document.head.appendChild(style);
}

let currentEditingId = null;

/** Met en surbrillance la ligne du tableau correspondant à l'information en cours d'édition */
function setEditing(id) {
    ensureEditingStyle();
    if (currentEditingId) {
        const prev = document.getElementById(String(currentEditingId));
        if (prev) prev.classList.remove('row-editing');
    }
    currentEditingId = id || null;
    if (id) {
        const tr = document.getElementById(String(id));
        if (tr) tr.classList.add('row-editing');
    }
}

// ============================================================================
// AFFICHAGE DU TABLEAU
// ============================================================================

nb.innerText = (Array.isArray(lesInformations) ? lesInformations.length : 0);

/** Affiche un message quand le tableau est vide */
function montrerAucuneInformation() {
    if (!lesLignes) return;
    lesLignes.innerHTML = '';
    const tr = document.createElement('tr');
    tr.id = 'placeholderNoInfo';
    const td = tr.insertCell();
    td.colSpan = 7;
    td.className = 'text-center text-muted py-4';
    td.innerHTML = '<i class="bi bi-inbox me-2"></i>Aucune information pour le moment';
    lesLignes.appendChild(tr);
}

/** Échappe les caractères HTML spéciaux pour éviter les injections XSS */
function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": "&#39;" }[c];
    });
}

/** Crée une ligne <tr> pour le tableau à partir d'un objet information */
function creerLigne(info) {
    const tr = document.createElement('tr');
    tr.id = info.id;

    const tdId = tr.insertCell();
    tdId.textContent = info.id;
    tdId.className = 'text-center align-middle';

    const tdTitre = tr.insertCell();
    const rawTitle = info.titre || '';
    const displayRaw = rawTitle.length > TITRE_MAX_LENGTH ? rawTitle.slice(0, TITRE_MAX_LENGTH) + '…' : rawTitle;
    tdTitre.textContent = escapeHtml(displayRaw);
    tdTitre.title = rawTitle;
    tdTitre.className = 'align-middle';

    const tdType = tr.insertCell();
    tdType.className = 'text-center align-middle';
    const badge = document.createElement('span');
    badge.className = info.type === 'Publique' ? 'badge bg-success' : 'badge bg-warning text-dark';
    badge.textContent = escapeHtml(info.type);
    tdType.appendChild(badge);

    const tdAuteur = tr.insertCell();
    tdAuteur.textContent = escapeHtml(info.auteur);
    tdAuteur.className = 'align-middle';

    const tdDate = tr.insertCell();
    tdDate.className = 'text-center align-middle';
    if (info.date_modif) {
        const date = new Date(info.date_modif);
        tdDate.textContent = date.toLocaleDateString('fr-FR', { timeZone: 'Europe/Paris' });
    } else if (info.date_creation) {
        const date = new Date(info.date_creation);
        tdDate.textContent = date.toLocaleDateString('fr-FR', { timeZone: 'Europe/Paris' });
    } else {
        tdDate.textContent = '-';
    }

    const tdFichiers = tr.insertCell();
    tdFichiers.className = 'text-center align-middle';
    if (typeof lesDocumentsInfo !== 'undefined' && lesDocumentsInfo[info.id]) {
        const nbDocs = lesDocumentsInfo[info.id].length;
        if (nbDocs > 0) {
            const badgeFichier = document.createElement('span');
            badgeFichier.className = 'badge bg-info text-dark';
            badgeFichier.textContent = `${nbDocs} fichier${nbDocs > 1 ? 's' : ''}`;
            tdFichiers.appendChild(badgeFichier);
        } else {
            tdFichiers.innerHTML = '<span class="text-muted">Aucun</span>';
        }
    } else {
        tdFichiers.innerHTML = '<span class="text-muted">Aucun</span>';
    }

    const tdAction = tr.insertCell();
    tdAction.className = 'text-center align-middle';
    const container = document.createElement('div');
    container.className = 'd-flex justify-content-center gap-2';

    const btnMod = creerBoutonModification(() => ouvrirFormulaire(info.id));
    if (btnMod && btnMod.tagName === 'BUTTON') btnMod.type = 'button';
    container.appendChild(btnMod);

    const supprimer = () => appelAjax({ url: '/ajax/supprimer.php', data: { table: 'Information', id: info.id }, success: () => location.reload() });
    const btnSup = creerBoutonSuppression(() => confirmer(supprimer));
    if (btnSup && btnSup.tagName === 'BUTTON') btnSup.type = 'button';
    container.appendChild(btnSup);

    tdAction.appendChild(container);

    return tr;
}

if (Array.isArray(lesInformations) && lesInformations.length > 0) {
    for (const info of lesInformations) {
        lesLignes.appendChild(creerLigne(info));
    }
} else {
    montrerAucuneInformation();
}

// ============================================================================
// GESTION DU MODAL (AJOUT / MODIFICATION)
// ============================================================================

/** Ouvre le modal. Si id est fourni, préremplit le formulaire pour modification */
function ouvrirFormulaire(id = null) {
    if (formModal) {
        formModal.style.display = 'block';
        formModal.setAttribute('aria-hidden', 'false');
    }
    formTitle.innerText = id ? 'Modifier une information' : 'Ajouter une information';

    fichiersEnAttente = [];
    documentsASupprimer = [];

    if (!id) {
        form.reset();
        if (tinymce) tinymce.get('contenu').setContent('');
        montrerAucunFichier();
        setEditing(null);
        return;
    }

    const info = (Array.isArray(lesInformations) ? lesInformations.find(x => String(x.id) === String(id)) : null);
    if (info) {
        document.getElementById('infoId').value = info.id;
        document.getElementById('titre').value = info.titre;
        document.getElementById('type').value = info.type;
        if (tinymce) tinymce.get('contenu').setContent(info.contenu || '');
    } else {
        afficherToast("Données manquantes : impossible de préremplir le formulaire.", 'error');
        return;
    }

    listeFichiers.innerHTML = '';
    if (typeof lesDocumentsInfo !== 'undefined' && lesDocumentsInfo[id]) {
        const docs = lesDocumentsInfo[id];
        if (Array.isArray(docs) && docs.length > 0) {
            for (const doc of docs) {
                const tr = document.createElement('tr');
                const tdAct = tr.insertCell();
                const tdFic = tr.insertCell();
                tdFic.textContent = doc.nom_original || doc.fichier;
                tr.dataset.docId = doc.id;
                const btn = creerBoutonSuppression(() => {
                    if (!documentsASupprimer.includes(doc.id)) {
                        documentsASupprimer.push(doc.id);
                    }
                    tr.style.textDecoration = 'line-through';
                    tr.style.opacity = '0.5';
                    tr.style.backgroundColor = '#ffcccc';
                    btn.disabled = true;
                    btn.title = 'Sera supprimé à l\'enregistrement';
                });
                if (btn && btn.tagName === 'BUTTON') btn.type = 'button';
                tdAct.appendChild(btn);
                listeFichiers.appendChild(tr);
            }
        } else {
            montrerAucunFichier();
        }
    } else {
        montrerAucunFichier();
    }
    setEditing(id);
}

/** Ferme le modal et réinitialise l'état */
function fermerModal() {
    fichiersEnAttente = [];
    documentsASupprimer = [];
    if (formModal) {
        formModal.style.display = 'none';
        formModal.setAttribute('aria-hidden', 'true');
    }
    setEditing(null);
}

btnAjouter.onclick = () => ouvrirFormulaire(null);
btnCancel.onclick = fermerModal;
if (btnCloseModal) btnCloseModal.onclick = fermerModal;

// ============================================================================
// GESTION DES FICHIERS PDF
// ============================================================================

/** Fichiers sélectionnés en attente d'upload (non encore envoyés) */
let fichiersEnAttente = [];

/** IDs des documents existants marqués pour suppression (supprimés à l'enregistrement) */
let documentsASupprimer = [];

btnPickFile.onclick = () => fichierInput.click();
fichierInput.onchange = () => {
    if (fichierInput.files.length === 0) return;
    const params = { maxSize: 5 * 1024 * 1024, extensions: ['pdf'] };

    for (const file of fichierInput.files) {
        if (!fichierValide(file, params)) continue;

        if (fichiersEnAttente.some(f => f.name === file.name && f.size === file.size)) {
            afficherToast(`Le fichier "${file.name}" est déjà dans la liste`, 'warning');
            continue;
        }

        fichiersEnAttente.push(file);

        supprimerPlaceholderSiPresent();
        const tr = document.createElement('tr');
        tr.dataset.filename = file.name;
        tr.dataset.filesize = file.size;
        const tdAct = tr.insertCell();
        const tdFic = tr.insertCell();
        tdFic.textContent = file.name;

        const btn = creerBoutonSuppression(() => {
            fichiersEnAttente = fichiersEnAttente.filter(f => !(f.name === file.name && f.size === file.size));
            tr.remove();
            if (listeFichiers.rows.length === 0) montrerAucunFichier();
        });
        if (btn && btn.tagName === 'BUTTON') btn.type = 'button';
        tdAct.appendChild(btn);

        listeFichiers.appendChild(tr);
    }

    fichierInput.value = '';
};

// ============================================================================
// SOUMISSION DU FORMULAIRE
// ============================================================================

/**
 * Enregistre l'information puis gère les opérations sur les fichiers :
 * 1. Supprime les documents marqués
 * 2. Upload les nouveaux fichiers
 * 3. Recharge la page une fois terminé
 */
form.onsubmit = function (e) {
    e.preventDefault();
    effacerLesErreurs();
    const id = document.getElementById('infoId').value;
    const dataForm = { table: 'Information', id: id || undefined, titre: document.getElementById('titre').value, type: document.getElementById('type').value, contenu: (tinymce) ? tinymce.get('contenu').getContent() : document.getElementById('contenu').value };

    appelAjax({
        url: '/administration/information/ajax/enregistrer.php', data: dataForm, success: (resp) => {
            const infoId = resp && resp.success ? resp.success : id;

            let operationsEnCours = 0;
            let erreurs = [];

            const terminer = () => {
                if (operationsEnCours === 0) {
                    if (erreurs.length > 0) {
                        afficherToast('Enregistré avec des erreurs: ' + erreurs.join(', '), 'warning');
                    }
                    location.reload();
                }
            };

            if (documentsASupprimer.length > 0) {
                for (const docId of documentsASupprimer) {
                    operationsEnCours++;
                    appelAjax({
                        url: '/administration/information/ajax/supprimer.php',
                        data: { id: docId },
                        success: () => { operationsEnCours--; terminer(); },
                        error: () => { operationsEnCours--; erreurs.push('suppression doc ' + docId); terminer(); }
                    });
                }
            }

            if (fichiersEnAttente.length > 0) {
                operationsEnCours++;
                const fd = new FormData();
                fd.append('idInformation', infoId);
                for (const file of fichiersEnAttente) {
                    fd.append('fichier[]', file);
                }
                appelAjax({
                    url: '/administration/information/ajax/ajouter.php',
                    data: fd,
                    success: () => { operationsEnCours--; terminer(); },
                    error: () => { operationsEnCours--; erreurs.push('upload fichiers'); terminer(); }
                });
            }

            if (documentsASupprimer.length === 0 && fichiersEnAttente.length === 0) {
                location.reload();
            }
        }
    });
};

// ============================================================================
// INITIALISATION DE TINYMCE
// ============================================================================

if (typeof tinymce !== 'undefined') {
    tinymce.init({
        license_key: 'gpl',
        selector: '#contenu',
        menubar: false,
        plugins: 'link lists table code image',
        toolbar: 'undo redo | bold italic | forecolor backcolor | alignleft aligncenter alignright | bullist numlist | image | link | code',
        height: 300,
        automatic_uploads: true,
        images_upload_url: '/administration/information/ajax/upload_image.php',
        file_picker_types: 'image',
        image_title: true,
        setup: function (editor) {
            editor.on('change', function () { /* marque le contenu modifié si besoin */ });
        }
    });
}
