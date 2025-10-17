"use strict";

import { appelAjax } from "/composant/fonction/ajax.js";
import { confirmer, afficherToast } from "/composant/fonction/afficher.js";
import { configurerFormulaire, effacerLesErreurs, fichierValide, creerBoutonSuppression, creerBoutonModification } from "/composant/fonction/formulaire.js";

/* global lesInformations, lesDocumentsInfo, tinymce */

// éléments de l'interface
const lesLignes = document.getElementById('lesLignes');
const nb = document.getElementById('nb');
const btnAjouter = document.getElementById('btnAjouter');
const formContainer = document.getElementById('formContainer');
const formTitle = document.getElementById('formTitle');
const btnCancel = document.getElementById('btnCancel');
const form = document.getElementById('formInfo');
const fichierInput = document.getElementById('fichierModal');
const btnPickFile = document.getElementById('btnPickFile');
const listeFichiers = document.getElementById('listeFichiersAssocies');

configurerFormulaire();

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
    // clear previous
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

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": "&#39;" }[c];
    });
}

function creerLigne(info) {
    const tr = document.createElement('tr');
    tr.id = info.id;

    // créer les cellules dans l'ordre attendu par l'en-tête : ID, Titre, Type, Auteur, Date, Actions
    const tdId = tr.insertCell(); tdId.textContent = info.id;
    const tdTitre = tr.insertCell(); tdTitre.textContent = escapeHtml(info.titre);
    const tdType = tr.insertCell(); tdType.textContent = escapeHtml(info.type);
    const tdAuteur = tr.insertCell(); tdAuteur.textContent = escapeHtml(info.auteur);
    const tdDate = tr.insertCell(); tdDate.textContent = escapeHtml(info.date_creation);

    // cellule actions (dernière)
    const tdAction = tr.insertCell();
    const container = document.createElement('div');
    container.style.display = 'flex';
    container.style.gap = '8px';

    // bouton modifier
    const btnMod = creerBoutonModification(() => ouvrirFormulaire(info.id));
    container.appendChild(btnMod);

    // bouton supprimer
    const supprimer = () => appelAjax({ url: '/administration/information/ajax/supprimer.php', data: { id: info.id }, success: () => tr.remove() });
    const btnSup = creerBoutonSuppression(() => confirmer(supprimer));
    container.appendChild(btnSup);

    tdAction.appendChild(container);

    return tr;
}

// construction initiale du tableau
if (Array.isArray(lesInformations)) {
    for (const info of lesInformations) {
        lesLignes.appendChild(creerLigne(info));
    }
}

// ouvre le formulaire et remplit les champs
function ouvrirFormulaire(id = null) {
    formContainer.style.display = 'block';
    formTitle.innerText = id ? 'Modifier une information' : 'Ajouter une information';

    if (!id) {
        form.reset();
        if (tinymce) tinymce.get('contenu').setContent('');
        listeFichiers.innerHTML = '';
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
        for (const doc of lesDocumentsInfo[id]) {
            const tr = document.createElement('tr');
            const tdAct = tr.insertCell();
            const tdFic = tr.insertCell();
            tdFic.textContent = doc.fichier;
            const btn = creerBoutonSuppression(() => confirmer(() => {
                appelAjax({ url: '/administration/information/ajax/supprimer.php', data: { id: doc.id }, success: () => tr.remove() });
            }));
            tdAct.appendChild(btn);
            listeFichiers.appendChild(tr);
        }
    }
    // highlight this row
    setEditing(id);
}

// events
btnAjouter.onclick = () => ouvrirFormulaire(null);
btnCancel.onclick = () => { formContainer.style.display = 'none'; setEditing(null); };

btnPickFile.onclick = () => fichierInput.click();
fichierInput.onchange = () => {
    if (fichierInput.files.length === 0) return;
    const file = fichierInput.files[0];
    const params = { maxSize: 5 * 1024 * 1024, extensions: ['pdf'] };
    if (!fichierValide(file, params)) return;
    const div = document.createElement('div');
    div.textContent = file.name + ' ';
    const btn = document.createElement('button');
    btn.type = 'button'; btn.textContent = 'Supprimer'; btn.onclick = () => div.remove();
    div.appendChild(btn);
    listeFichiers.appendChild(div);
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
    tinymce.init({ license_key: 'gpl', selector: '#contenu', menubar: false, plugins: 'link lists table code', toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link | code', height: 300, setup: function (editor) { editor.on('change', function () { /* synchronisation éventuelle */ }); } });
}
