"use strict";

import { appelAjax } from "/composant/fonction/ajax.js";
import { confirmer, afficherToast } from "/composant/fonction/afficher.js";
import { configurerFormulaire, effacerLesErreurs, fichierValide, creerBoutonSuppression, creerBoutonModification } from "/composant/fonction/formulaire.js";

/**
 * administration/information/index.js
 * Gestion de la page d'administration des informations : affichage, formulaire
 * et gestion des documents associés (upload / suppression).
 *
 * Variables globales attendues: `lesInformations`, `lesDocumentsInfo`, `tinymce`
 */

/* global lesInformations, lesDocumentsInfo, tinymce */

// DOM - éléments de l'interface
const lesLignes = document.getElementById('lesLignes');
const nb = document.getElementById('nb');
const btnAjouter = document.getElementById('btnAjouter');
const formModal = document.getElementById('formModal');
const formTitle = document.getElementById('formTitle');
const btnCancel = document.getElementById('btnCancel');
const form = document.getElementById('formInfo');
const fichierInput = document.getElementById('fichierModal');
const btnPickFile = document.getElementById('btnPickFile');
const listeFichiers = document.getElementById('listeFichiersAssocies');

configurerFormulaire();

// Assistant pour la gestion de l'affichage de la liste des fichiers associés
// Fonctions utilitaires pour la liste des fichiers associés
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

function supprimerPlaceholderSiPresent() {
    if (!listeFichiers) return;
    const p = document.getElementById('placeholderNoFile');
    if (p) p.remove();
}

// longueur max du titre affiché dans le tableau
const TITRE_MAX_LENGTH = 30;

// Style pour la ligne en édition
function ensureEditingStyle() {
    if (document.getElementById('style-row-editing')) return;
    const style = document.createElement('style');
    style.id = 'style-row-editing';
    style.textContent = `
        .row-editing { background-color: #fff8c4 !important; }
        .row-editing td { border-left: 4px solid #ffcc00; }
    `;
    document.head.appendChild(style);
}

let currentEditingId = null;
function setEditing(id) {
    ensureEditingStyle();
    // retirer la classe d'édition de la ligne précédente
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

// nombre d'éléments
nb.innerText = (Array.isArray(lesInformations) ? lesInformations.length : 0);

// Affiche un message quand il n'y a aucune information
function montrerAucuneInformation() {
    if (!lesLignes) return;
    lesLignes.innerHTML = '';
    const tr = document.createElement('tr');
    tr.id = 'placeholderNoInfo';
    const td = tr.insertCell();
    td.colSpan = 7; // nombre de colonnes dans le tableau
    td.className = 'text-muted';
    td.textContent = 'Aucune information';
    lesLignes.appendChild(tr);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": "&#39;" }[c];
    });
}

// Crée une ligne de tableau représentant une information
// Colonnes: ID, Titre, Type, Auteur, Date, Fichiers, Actions
function creerLigne(info) {
    const tr = document.createElement('tr');
    tr.id = info.id;

    const tdId = tr.insertCell(); tdId.textContent = info.id;
    const tdTitre = tr.insertCell();
    // Tronque le titre pour l'affichage et met le titre complet en 'title'
    const rawTitle = info.titre || '';
    const displayRaw = rawTitle.length > TITRE_MAX_LENGTH ? rawTitle.slice(0, TITRE_MAX_LENGTH) + '…' : rawTitle;
    tdTitre.textContent = escapeHtml(displayRaw);
    tdTitre.title = rawTitle;
    const tdType = tr.insertCell(); tdType.textContent = escapeHtml(info.type);
    const tdAuteur = tr.insertCell(); tdAuteur.textContent = escapeHtml(info.auteur);

    // Date (modification ou création)
    const tdDate = tr.insertCell();
    if (info.date_modif) {
        const date = new Date(info.date_modif);
        tdDate.textContent = date.toLocaleDateString('fr-FR', { timeZone: 'Europe/Paris' });
    } else if (info.date_creation) {
        const date = new Date(info.date_creation);
        tdDate.textContent = date.toLocaleDateString('fr-FR', { timeZone: 'Europe/Paris' });
    } else {
        tdDate.textContent = '-';
    }

    // Cellule: nombre de fichiers associés
    const tdFichiers = tr.insertCell();
    if (typeof lesDocumentsInfo !== 'undefined' && lesDocumentsInfo[info.id]) {
        const nbDocs = lesDocumentsInfo[info.id].length;
        tdFichiers.textContent = nbDocs > 0 ? `${nbDocs} fichier${nbDocs > 1 ? 's' : ''}` : 'Aucun fichier';
    } else {
        tdFichiers.textContent = 'Aucun fichier';
    }

    // Actions: modifier / supprimer
    const tdAction = tr.insertCell();
    const container = document.createElement('div');
    container.style.display = 'flex';
    container.style.gap = '8px';

    // Bouton modifier
    const btnMod = creerBoutonModification(() => ouvrirFormulaire(info.id));
    // éviter que le bouton déclenche un submit si il est dans un <form>
    if (btnMod && btnMod.tagName === 'BUTTON') btnMod.type = 'button';
    container.appendChild(btnMod);

    // Bouton supprimer (appel AJAX générique pour supprimer l'entrée)
    const supprimer = () => appelAjax({ url: '/ajax/supprimer.php', data: { table: 'Information', id: info.id }, success: () => location.reload() });
    const btnSup = creerBoutonSuppression(() => confirmer(supprimer));
    if (btnSup && btnSup.tagName === 'BUTTON') btnSup.type = 'button';
    container.appendChild(btnSup);

    tdAction.appendChild(container);

    return tr;
}

// Construction initiale du tableau
if (Array.isArray(lesInformations) && lesInformations.length > 0) {
    for (const info of lesInformations) {
        lesLignes.appendChild(creerLigne(info));
    }
} else {
    montrerAucuneInformation();
}

/*
 * Ouvre le modal du formulaire. Si `id` est fourni, préremplit les champs
 * avec les données de `lesInformations`; sinon prépare un formulaire vide.
 */
function ouvrirFormulaire(id = null) {
    if (formModal) {
        formModal.style.display = 'block';
        formModal.setAttribute('aria-hidden', 'false');
    }
    formTitle.innerText = id ? 'Modifier une information' : 'Ajouter une information';

    if (!id) {
        form.reset();
        if (tinymce) tinymce.get('contenu').setContent('');
        montrerAucunFichier();
        setEditing(null);
        return;
    }

    // Préremplir depuis `lesInformations`
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

    // Remplit le tableau des fichiers associés (lesDocumentsInfo[id])
    listeFichiers.innerHTML = '';
    if (typeof lesDocumentsInfo !== 'undefined' && lesDocumentsInfo[id]) {
        const docs = lesDocumentsInfo[id];
        if (Array.isArray(docs) && docs.length > 0) {
            for (const doc of docs) {
                const tr = document.createElement('tr');
                const tdAct = tr.insertCell();
                const tdFic = tr.insertCell();
                tdFic.textContent = doc.nom_original || doc.fichier;
                // Supprime le document via AJAX, puis retire la ligne du tableau
                const btn = creerBoutonSuppression(() => {
                    appelAjax({
                        url: '/administration/information/ajax/supprimer.php',
                        data: { id: doc.id },
                        success: () => { tr.remove(); if (listeFichiers.rows.length === 0) montrerAucunFichier(); },
                        error: () => { afficherToast('Suppression du document impossible', 'error'); }
                    });
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
    // Met en évidence la ligne actuellement éditée
    setEditing(id);
}

// Événements DOM
btnAjouter.onclick = () => ouvrirFormulaire(null);
btnCancel.onclick = () => { if (formModal) { formModal.style.display = 'none'; formModal.setAttribute('aria-hidden', 'true'); } setEditing(null); };

btnPickFile.onclick = () => fichierInput.click();
fichierInput.onchange = () => {
    if (fichierInput.files.length === 0) return;
    const file = fichierInput.files[0];
    const params = { maxSize: 5 * 1024 * 1024, extensions: ['pdf'] };
    if (!fichierValide(file, params)) return;

    // Ajoute une ligne dans le tableau des fichiers sélectionnés
    supprimerPlaceholderSiPresent();
    const tr = document.createElement('tr');
    const tdAct = tr.insertCell();
    const tdFic = tr.insertCell();
    tdFic.textContent = file.name;

    // Bouton suppression pour retirer le fichier sélectionné
    const btn = creerBoutonSuppression(() => {
        tr.remove();
        if (listeFichiers.rows.length === 0) montrerAucunFichier();
    });
    if (btn && btn.tagName === 'BUTTON') btn.type = 'button';
    tdAct.appendChild(btn);

    listeFichiers.appendChild(tr);
};

// Soumission du formulaire: enregistre l'information, puis upload du fichier si présent
form.onsubmit = function (e) {
    e.preventDefault();
    effacerLesErreurs();
    const id = document.getElementById('infoId').value;
    const dataForm = { table: 'Information', id: id || undefined, titre: document.getElementById('titre').value, type: document.getElementById('type').value, contenu: (tinymce) ? tinymce.get('contenu').getContent() : document.getElementById('contenu').value };

    appelAjax({
        url: '/administration/information/ajax/enregistrer.php', data: dataForm, success: (resp) => {
            const infoId = resp && resp.success ? resp.success : id;
            // Si un fichier a été sélectionné, on l'envoie après l'enregistrement
            if (fichierInput.files.length > 0) {
                const fd = new FormData(); fd.append('idInformation', infoId); fd.append('fichier', fichierInput.files[0]);
                appelAjax({ url: '/administration/information/ajax/ajouter.php', data: fd, success: () => location.reload(), error: () => { afficherToast('Enregistré, upload échoué', 'error'); } });
            } else {
                location.reload();
            }
        }
    });
};

// TinyMCE: configuration de l'éditeur pour le champ `#contenu`
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
        /*
         * TinyMCE s'attend à une réponse JSON de la forme { location : 'URL' }
         * En cas d'erreur, renvoyer { error: { message: '...' } } et un code HTTP 4xx.
         */
        setup: function (editor) {
            editor.on('change', function () { /* marque le contenu modifié si besoin */ });
        }
    });
}
