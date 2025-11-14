"use strict";

import { appelAjax } from "/composant/fonction/ajax.js";
import { confirmer, afficherToast } from "/composant/fonction/afficher.js";
import { configurerFormulaire, effacerLesErreurs, fichierValide, creerBoutonSuppression, creerBoutonModification } from "/composant/fonction/formulaire.js";

/* global lesInformations, lesDocumentsInfo, tinymce */

// éléments de l'interface
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

// longueur max pour le titre affiché dans le tableau (tronque côté JS, n'affecte pas le CSS)
const TITRE_MAX_LENGTH = 30;

// style pour la ligne en cours d'édition
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
    // effacer le précédent
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

// rendu du tableau à la manière des autres pages admin
nb.innerText = (Array.isArray(lesInformations) ? lesInformations.length : 0);

// Assistant : afficher un message quand il n'y a aucune information
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

function creerLigne(info) {
    const tr = document.createElement('tr');
    tr.id = info.id;

    // créer les cellules dans l'ordre attendu par l'en-tête : ID, Titre, Type, Auteur, Date, Fichiers, Actions
    const tdId = tr.insertCell(); tdId.textContent = info.id;
    const tdTitre = tr.insertCell();
    // tronquer le titre côté JS pour éviter le débordement dans le tableau
    const rawTitle = info.titre || '';
    const displayRaw = rawTitle.length > TITRE_MAX_LENGTH ? rawTitle.slice(0, TITRE_MAX_LENGTH) + '…' : rawTitle;
    tdTitre.textContent = escapeHtml(displayRaw);
    // attribut title pour voir le titre complet au survol
    tdTitre.title = rawTitle;
    const tdType = tr.insertCell(); tdType.textContent = escapeHtml(info.type);
    const tdAuteur = tr.insertCell(); tdAuteur.textContent = escapeHtml(info.auteur);

    // date de mise à jour (ou création si jamais modifié)
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

    // cellule fichiers
    const tdFichiers = tr.insertCell();
    if (typeof lesDocumentsInfo !== 'undefined' && lesDocumentsInfo[info.id]) {
        const nbDocs = lesDocumentsInfo[info.id].length;
        tdFichiers.textContent = nbDocs > 0 ? `${nbDocs} fichier${nbDocs > 1 ? 's' : ''}` : 'Aucun fichier';
    } else {
        tdFichiers.textContent = 'Aucun fichier';
    }

    // cellule actions (dernière)
    const tdAction = tr.insertCell();
    const container = document.createElement('div');
    container.style.display = 'flex';
    container.style.gap = '8px';

    // bouton modifier
    const btnMod = creerBoutonModification(() => ouvrirFormulaire(info.id));
    container.appendChild(btnMod);

    // bouton supprimer (supprime l'information elle-même — utiliser l'API générique)
    const supprimer = () => appelAjax({ url: '/ajax/supprimer.php', data: { table: 'Information', id: info.id }, success: () => location.reload() });
    const btnSup = creerBoutonSuppression(() => confirmer(supprimer));
    container.appendChild(btnSup);

    tdAction.appendChild(container);

    return tr;
}

// construction initiale du tableau
if (Array.isArray(lesInformations) && lesInformations.length > 0) {
    for (const info of lesInformations) {
        lesLignes.appendChild(creerLigne(info));
    }
} else {
    montrerAucuneInformation();
}

// ouvre le formulaire et remplit les champs
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

    // remplir depuis lesInformations
    const info = (Array.isArray(lesInformations) ? lesInformations.find(x => String(x.id) === String(id)) : null);
    if (info) {
        document.getElementById('infoId').value = info.id;
        document.getElementById('titre').value = info.titre;
        document.getElementById('type').value = info.type;
        if (tinymce) tinymce.get('contenu').setContent(info.contenu || '');
    } else {
        // Les données sont normalement injectées côté serveur dans `lesInformations`.
        // Si l'information n'est pas trouvée, on ne fera pas d'appel générique vers /ajax/get.php
        // (consigne : ne pas utiliser de get.php global). On informe l'utilisateur.
        afficherToast("Données manquantes : impossible de préremplir le formulaire.", 'error');
        return;
    }

    // documents associés via lesDocumentsInfo -> remplir le tableau
    listeFichiers.innerHTML = '';
    if (typeof lesDocumentsInfo !== 'undefined' && lesDocumentsInfo[id]) {
        const docs = lesDocumentsInfo[id];
        if (Array.isArray(docs) && docs.length > 0) {
            for (const doc of docs) {
                const tr = document.createElement('tr');
                const tdAct = tr.insertCell();
                const tdFic = tr.insertCell();
                tdFic.textContent = doc.nom_original || doc.fichier;
                const btn = creerBoutonSuppression(() => confirmer(() => {
                    appelAjax({ url: '/administration/information/ajax/supprimer.php', data: { id: doc.id }, success: () => { tr.remove(); if (listeFichiers.rows.length === 0) montrerAucunFichier(); } });
                }));
                tdAct.appendChild(btn);
                listeFichiers.appendChild(tr);
            }
        } else {
            montrerAucunFichier();
        }
    } else {
        montrerAucunFichier();
    }
    // mettre en évidence cette ligne
    setEditing(id);
}

// events
btnAjouter.onclick = () => ouvrirFormulaire(null);
btnCancel.onclick = () => { if (formModal) { formModal.style.display = 'none'; formModal.setAttribute('aria-hidden', 'true'); } setEditing(null); };

btnPickFile.onclick = () => fichierInput.click();
fichierInput.onchange = () => {
    if (fichierInput.files.length === 0) return;
    const file = fichierInput.files[0];
    const params = { maxSize: 5 * 1024 * 1024, extensions: ['pdf'] };
    if (!fichierValide(file, params)) return;

    // Ajouter une ligne cohérente dans le tableau des fichiers associés
    supprimerPlaceholderSiPresent();
    const tr = document.createElement('tr');
    const tdAct = tr.insertCell();
    const tdFic = tr.insertCell();
    tdFic.textContent = file.name;

    // bouton suppression cohérent avec le style existant
    const btn = creerBoutonSuppression(() => {
        tr.remove();
        if (listeFichiers.rows.length === 0) montrerAucunFichier();
    });
    tdAct.appendChild(btn);

    listeFichiers.appendChild(tr);
};

form.onsubmit = function (e) {
    e.preventDefault();
    effacerLesErreurs();
    const id = document.getElementById('infoId').value;
    const dataForm = { table: 'Information', id: id || undefined, titre: document.getElementById('titre').value, type: document.getElementById('type').value, contenu: (tinymce) ? tinymce.get('contenu').getContent() : document.getElementById('contenu').value };

    appelAjax({
        url: '/administration/information/ajax/enregistrer.php', data: dataForm, success: (resp) => {
            const infoId = resp && resp.success ? resp.success : id;
            if (fichierInput.files.length > 0) {
                const fd = new FormData(); fd.append('idInformation', infoId); fd.append('fichier', fichierInput.files[0]);
                appelAjax({ url: '/administration/information/ajax/ajouter.php', data: fd, success: () => location.reload(), error: () => { afficherToast('Enregistré, upload échoué', 'error'); } });
            } else {
                location.reload();
            }
        }
    });
};

// TinyMCE init (comme les autres pages)
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
            editor.on('change', function () { /* synchronisation éventuelle */ });
        }
    });
}
