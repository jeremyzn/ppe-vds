"use strict";

import { appelAjax } from "/composant/fonction/ajax.js";
import { afficherToast, confirmer, afficherSousLeChamp } from "/composant/fonction/afficher";

/* global lesInfos */

const btnAjouter = document.getElementById('btnAjouter');
const formulaire = document.getElementById('formulaire');
const btnAnnuler = document.getElementById('btnAnnuler');
const btnEnregistrer = document.getElementById('btnEnregistrer');
const liste = document.getElementById('liste');
const titre = document.getElementById('titre');
const contenu = document.getElementById('contenu');
const type = document.getElementById('type');
const detailPanel = document.getElementById('detailPanel');
const detailTitre = document.getElementById('detailTitre');
const detailContenu = document.getElementById('detailContenu');
const btnAttach = document.getElementById('btnAttach');
const attachFichiers = document.getElementById('attachFichiers');
const tableDocs = document.getElementById('tableDocs');

let editionId = 0;

btnAjouter.onclick = () => {
    formulaire.style.display = 'block';
    btnAjouter.style.display = 'none';
    resetForm();
};

btnAnnuler.onclick = () => {
    formulaire.style.display = 'none';
    btnAjouter.style.display = 'inline-block';
};

function resetForm() {
    editionId = 0;
    titre.value = '';
    contenu.value = '';
    type.value = 'Publique';
}

function afficherLesInfos(data) {
    liste.innerHTML = '';
    for (const info of data) {
        const card = document.createElement('div');
        card.className = 'card mb-2';
        const header = document.createElement('div');
        header.className = 'card-header';
        header.innerHTML = `<strong>${info.titre}</strong> <span class="badge bg-secondary ms-2">${info.type}</span>`;
        const body = document.createElement('div');
        body.className = 'card-body';
        body.innerHTML = info.contenu.substring(0, 200) + (info.contenu.length > 200 ? '...' : '');
        const foot = document.createElement('div');
        foot.className = 'card-footer';
        foot.innerHTML = `<button class="btn btn-sm btn-outline-primary me-1" data-id="${info.id}" data-action="edit">✎</button>
                           <button class="btn btn-sm btn-outline-danger" data-id="${info.id}" data-action="del">✘</button>`;

        card.appendChild(header);
        card.appendChild(body);
        card.appendChild(foot);
        liste.appendChild(card);
    }

    // wire buttons
    liste.querySelectorAll('button[data-action]').forEach(btn => {
        btn.onclick = (e) => {
            const id = e.currentTarget.getAttribute('data-id');
            const action = e.currentTarget.getAttribute('data-action');
            if (action === 'edit') {
                editInfo(id);
            } else if (action === 'del') {
                confirmer(() => supprimerInfo(id));
            }
        };
    });
}

function showDetail(id) {
    const info = lesInfos.find(i => String(i.id) === String(id));
    if (!info) return;
    editionId = id;
    detailTitre.innerText = `Information : ${info.titre}`;
    // afficher le contenu en HTML pour que les images et la mise en forme soient visibles
    detailContenu.innerHTML = info.contenu || '';
    detailPanel.style.display = 'block';
    // récupérer les documents
    appelAjax({
        url: 'ajax/getdocuments.php',
        data: { idInformation: id },
        success: (docs) => {
            afficherDocs(docs, id);
        }
    });
}

// ouvre le formulaire d'édition et pré-remplit les champs (prend en charge TinyMCE)
function editInfo(id) {
    const info = lesInfos.find(i => String(i.id) === String(id));
    if (!info) return;
    editionId = id;
    titre.value = info.titre || '';
    type.value = info.type || 'Publique';
    if (window.tinymce && tinymce.get('contenu')) {
        tinymce.get('contenu').setContent(info.contenu || '');
    } else {
        contenu.value = info.contenu || '';
    }
    formulaire.style.display = 'block';
    btnAjouter.style.display = 'none';
    // charger les documents associés
    appelAjax({
        url: 'ajax/getdocuments.php',
        data: { idInformation: id },
        success: (docs) => {
            afficherDocs(docs, id);
        }
    });
}

function afficherDocs(docs, idInformation) {
    tableDocs.innerHTML = '';
    for (const d of docs) {
        const tr = document.createElement('tr');
        const tdAction = document.createElement('td');
        const tdFile = document.createElement('td');

        const del = document.createElement('span');
        del.innerText = '✘';
        del.style.color = 'red';
        del.style.cursor = 'pointer';
        del.onclick = () => {
            confirmer(() => {
                appelAjax({
                    url: 'ajax/supprimerdocument.php',
                    data: { id: d.id },
                    success: () => {
                        afficherToast('Document supprimé');
                        showDetail(idInformation);
                    }
                });
            });
        };

        tdAction.appendChild(del);
        const txt = document.createElement('span');
        txt.innerText = d.fichier;
        tdFile.appendChild(txt);

        tr.appendChild(tdAction);
        tr.appendChild(tdFile);
        tableDocs.appendChild(tr);
    }

    // attachement
    btnAttach.onclick = () => attachFichiers.click();
    attachFichiers.onchange = () => {
        if (attachFichiers.files.length > 0) {
            const fd = new FormData();
            fd.append('idInformation', editionId);
            for (const f of attachFichiers.files) fd.append('fichiers[]', f);
            appelAjax({
                url: 'ajax/ajouterdocument.php',
                data: fd,
                success: () => {
                    afficherToast('Documents ajoutés');
                    showDetail(editionId);
                }
            });
        }
    };
}

btnEnregistrer.onclick = () => {
    const fd = new FormData();
    fd.append('table', 'Information');
    fd.append('titre', titre.value);
    // si TinyMCE est initialisé, récupérer son contenu
    let contenuValeur = contenu.value;
    if (window.tinymce && tinymce.get('contenu')) {
        contenuValeur = tinymce.get('contenu').getContent();
    }
    fd.append('contenu', contenuValeur);
    fd.append('type', type.value);

    if (editionId) {
        // si TinyMCE est présent, récupérer le contenu actuel
        let contenuModif = contenu.value;
        if (window.tinymce && tinymce.get('contenu')) {
            contenuModif = tinymce.get('contenu').getContent();
        }
        appelAjax({
            url: '/ajax/modifier.php',
            data: { table: 'Information', id: editionId, lesValeurs: JSON.stringify({ titre: titre.value, contenu: contenuModif, type: type.value }) },
            success: (data) => {
                afficherToast('Information modifiée');
                location.reload();
            }
        });
        return;
    }

    appelAjax({
        url: '/ajax/ajouter.php',
        data: fd,
        success: (data) => {
            afficherToast('Information ajoutée');
            location.reload();
        }
    });
};

function supprimerInfo(id) {
    appelAjax({
        url: '/ajax/supprimer.php',
        data: { table: 'Information', id: id },
        success: (data) => {
            afficherToast('Information supprimée');
            location.reload();
        }
    });
}

// initialisation
afficherLesInfos(lesInfos);
