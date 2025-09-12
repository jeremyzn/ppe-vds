'use strict';

// Version 2025.3
// Date version : 05/08/2025
// Correction de l'affichage sous le champ afin de prendre en compte le conteneur parent
// Attribution d'une couleur de fond rouge par défaut et d'un style italic sur genererMessage

// Variables persistantes (au niveau du module) utilisées pour la modale de confirmation
let modal, btnOui, btnNon, confirmationMessage;


/**
 * Génération d'un message dans une mise en forme bootstrap (class='alert-dismissible')
 * Nécessite le composant bootstrap avec la partie js !!!
 * @param {string} texte à afficher.
 * @param {string} couleur de fond : vert, rouge ou orange
 * @return {string} Chaîne au format HTML
 */

export function genererMessage(texte, couleur = 'rouge') {
    // Ne pas transformer un message x_debug
    if (texte.startsWith('<br />')) {
        return texte;
    }
    // détermination de la classe bootstrap à utiliser en fonction de la couleur choisie
    let code = '#fb7e7b'; // par défaut
    if (couleur === 'vert') {
        code = '#1FA055';
    } else if (couleur === 'rouge') {
        code = '#fb7e7b';
    } else if (couleur === 'orange') {
        code = '#FF7415';
    }
    return `
            <div class="alert alert-dismissible fade show" 
                 style="color:white; background-color:${code}; font-size: 0.8rem; font-style: italic" 
                 role="alert">
                 ${texte}
                 <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>`;
}

/**
 * Affiche un toast personnalisé sur l'écran.
 * @param message
 * @param type
 * @param position
 * @param duration
 */
export function afficherToast(message, type = 'success', position = 'bottom-center', duration = 1500) {
    // Création du conteneur général s’il n’existe pas déjà
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.position = 'fixed';
        container.style.zIndex = 9999;
        container.style.pointerEvents = 'none';
        document.body.appendChild(container);
    }

    // Réinitialisation des positions
    container.style.top = '';
    container.style.bottom = '';
    container.style.left = '';
    container.style.right = '';
    container.style.transform = '';

    // Application de la position demandée
    switch (position) {
        case 'top-left':
            container.style.top = '1rem';
            container.style.left = '1rem';
            break;
        case 'top-right':
            container.style.top = '1rem';
            container.style.right = '1rem';
            break;
        case 'bottom-left':
            container.style.bottom = '1rem';
            container.style.left = '1rem';
            break;
        case 'bottom-right':
            container.style.bottom = '1rem';
            container.style.right = '1rem';
            break;
        case 'top-center':
            container.style.top = '1rem';
            container.style.left = '50%';
            container.style.transform = 'translateX(-50%)';
            break;
        case 'bottom-center':
            container.style.bottom = '1rem';
            container.style.left = '50%';
            container.style.transform = 'translateX(-50%)';
            break;
        case 'center':
            container.style.top = '50%';
            container.style.left = '50%';
            container.style.transform = 'translate(-50%, -50%)';
            break;
        default:
            console.warn(`Position "${position}" inconnue, positionnement par défaut à "bottom-right".`);
            container.style.bottom = '1rem';
            container.style.right = '1rem';
            break;
    }

    // Définition des styles par type
    const backgroundColors = {
        success: '#20ef51',
        error: '#ef2b3d',
        warning: '#fec105',
        info: '#47d9f4'
    };

    const textColors = {
        success: '#155724',
        error: '#721c24',
        warning: '#856404',
        info: '#0c5460'
    };

    // Création du toast
    const toast = document.createElement('div');
    toast.innerHTML = message;
    toast.style.background = backgroundColors[type] || backgroundColors.info;
    toast.style.color = textColors[type] || textColors.info;
    toast.style.borderRadius = '5px';
    toast.style.padding = '10px 15px';
    toast.style.marginTop = '8px';
    toast.style.boxShadow = '0 2px 6px rgba(0,0,0,0.2)';
    toast.style.fontFamily = 'sans-serif';
    toast.style.fontSize = '0.95rem';
    toast.style.maxWidth = '300px';
    toast.style.pointerEvents = 'auto';
    toast.style.opacity = '1';
    toast.style.transition = 'opacity 0.5s ease';

    container.appendChild(toast);

    // Fermeture automatique
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 500);
    }, duration);
}

/**
 * Affiche un message sous la balise input spécifiée.
 * si cette dernière n'existe pas ou si elle n'a pas de message de validation, le message est affiché dans la div avec l'id 'msg' ou dans une fenêtre modale
 * Si l'élément input est dans un conteneur avec la classe 'password-wrapper', le message sera affiché sous ce conteneur.
 * Si l'élément input est dans un conteneur avec la classe 'champ', le message sera affiché sous ce conteneur.
 *
 * @param {string} idInput - L'ID de l'élément d'entrée.
 * @param {string | null} message - Le message personnalisé (facultatif).
 */
export function afficherSousLeChamp(idInput, message = null) {
    const input = document.getElementById(idInput);

    if (!input) {
        const msg = document.getElementById('msg');
        if (msg) {
            msg.innerText = message;
        } else {
            messageBox(message, 'error');
        }
        console.warn(`afficherSousLeChamp : L'élément d'entrée ${idInput} n'existe pas.`);
        return;
    }

    // Utiliser le message de validation natif si non fourni
    if (message === null) {
        message = input.validationMessage;
    }

    // Déterminer le conteneur principal .champ
    const parent = input.parentElement;
    let baseRecherche = input;

    if (parent) {
        if (parent.classList.contains('password-wrapper')) {
            baseRecherche = parent;
            if( parent.parentElement && parent.parentElement.classList.contains('champ')) {
                baseRecherche = parent.parentElement;
            }
        } else if (parent.classList.contains('champ')) {
            baseRecherche = parent;
        }
    }

    // Chercher un .messageErreur à partir de baseRecherche
    let nextElement = baseRecherche.nextElementSibling;
    let trouve = false;

    while (nextElement && !trouve) {
        if (nextElement.classList.contains('messageErreur')) {
            nextElement.innerText = message;
            trouve = true;
        } else {
            nextElement = nextElement.nextElementSibling;
        }
    }

    // Si pas trouvé, créer la div messageErreur
    if (!trouve) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'messageErreur';
        messageDiv.innerText = message;

        baseRecherche.insertAdjacentElement('afterend', messageDiv);
    }
}

/**
 * Affiche le message dans une boîte de dialogue avec fermeture par l'utilisateur
 * @param {string} message message à afficher
 * @param {string} type : error, success alert, warning, info
 */
export function messageBox(message, type = 'success') {
    // Supprimer toute modale précédente
    document.getElementById('avertir-modal')?.remove();

    // Couleurs selon type
    const couleurs = {
        success: '#d4edda',
        error: '#f8d7da',
        warning: '#fff3cd',
        info: '#d1ecf1'
    };

    const textes = {
        success: '#155724',
        error: '#721c24',
        warning: '#856404',
        info: '#0c5460'
    };

    // Créer fond semi-transparent
    const overlay = document.createElement('div');
    overlay.id = 'avertir-modal';
    overlay.style.position = 'fixed';
    overlay.style.top = 0;
    overlay.style.left = 0;
    overlay.style.width = '100vw';
    overlay.style.height = '100vh';
    overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
    overlay.style.display = 'flex';
    overlay.style.alignItems = 'center';
    overlay.style.justifyContent = 'center';
    overlay.style.zIndex = 9999;

    // Créer la boîte de message
    const box = document.createElement('div');
    box.style.backgroundColor = couleurs[type] || couleurs.info;
    box.style.color = textes[type] || textes.info;
    box.style.padding = '20px';
    box.style.borderRadius = '8px';
    box.style.minWidth = '300px';
    box.style.maxWidth = '600px';
    box.style.boxShadow = '0 0 10px rgba(0,0,0,0.3)';
    box.style.fontFamily = 'sans-serif';

    // Texte
    const contenu = document.createElement('div');
    contenu.innerHTML = message;
    box.appendChild(contenu);

    // Bouton
    const bouton = document.createElement('button');
    bouton.textContent = 'OK';
    bouton.style.marginTop = '15px';
    bouton.style.float = 'right';
    bouton.style.padding = '6px 12px';
    bouton.style.backgroundColor = '#ccc';
    bouton.style.border = 'none';
    bouton.style.borderRadius = '4px';
    bouton.style.cursor = 'pointer';

    bouton.onclick = () => overlay.remove();

    box.appendChild(bouton);
    overlay.appendChild(box);
    document.body.appendChild(overlay);
}


/**
 * Affiche un message puis redirige automatiquement
 * @param {string} message
 * @param {string} url
 * @param {number} delay en ms
 */
export function retournerVers(message, url, delay = 2000) {
    afficherToast(message, 'success');
    setTimeout(() => location.href = url, delay);
}

/**
 * Affiche un message, attend confirmation manuelle, puis redirige
 * @param {string} message
 * @param {string} url
 */
export function retournerVersApresConfirmation(message, url) {
    messageBox(message, 'info');
    const bouton = document.querySelector('#avertir-modal button');
    if (bouton) {
        bouton.onclick = () => {
            document.getElementById('avertir-modal')?.remove();
            location.href = url;
        };
    }
}

/**
 * Affiche un message d'erreur dans une boîte de dialogue et restaure la valeur du champ
 * @param {HTMLElement} input
 * @param {string} message
 * @param {?string} oldValue
 */
export function corriger(input, message = input.validationMessage, oldValue = null) {
    messageBox(message, 'error');
    if (input.type === 'checkbox') {
        input.checked = !input.checked;
    } else if (input.hasAttribute('data-old')) {
        input.value = input.dataset.old;
    } else {
        input.value = oldValue ?? '';
    }
}

/**
 * Affiche une modale de confirmation.
 * @param {Function} callback - Fonction à exécuter si l'utilisateur confirme.
 * @param {string} message - Message à afficher dans la modale.
 */
export function confirmer(callback, message = 'Confirmer votre demande ?') {
    // Création de la modale si elle n'existe pas encore
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'confirmationModal';
        modal.style = `
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(0,0,0,0.6);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        `;

        const contenu = document.createElement('div');
        contenu.style = `
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 400px;
            text-align: center;
        `;

        confirmationMessage = document.createElement('p');
        confirmationMessage.id = 'confirmationMessage';

        const boutons = document.createElement('div');
        boutons.style.marginTop = '20px';

        btnOui = document.createElement('button');
        btnOui.id = 'btnConfirmer';
        btnOui.className = 'btn btn-success me-2';
        btnOui.textContent = 'Oui';

        btnNon = document.createElement('button');
        btnNon.id = 'btnAnnuler';
        btnNon.className = 'btn btn-danger';
        btnNon.textContent = 'Non';

        boutons.appendChild(btnOui);
        boutons.appendChild(btnNon);
        contenu.appendChild(confirmationMessage);
        contenu.appendChild(boutons);
        modal.appendChild(contenu);
        document.body.appendChild(modal);
    }

    // Mise à jour du message
    confirmationMessage.textContent = message;

    // Affiche la modale
    modal.style.display = 'flex';

    // Nettoyage et réassignation des événements
    btnOui.onclick = null;
    btnNon.onclick = null;

    btnOui.onclick = () => {
        modal.style.display = 'none';
        if (typeof callback === 'function') callback();
    };

    btnNon.onclick = () => {
        modal.style.display = 'none';
    };
}


// ------------------------------------------------------------
// Fonctions pour afficher et fermer une fenêtre modale de chargement
// ------------------------------------------------------------

/**
 * Affiche une fenêtre modale de chargement avec un message "Veuillez patienter..."
 * @returns {Promise<bootstrap.Modal>}
 */
export async function afficherVeuillezPatienter() {
    // Vérifier si la fenêtre modale existe déjà
    var modal = document.getElementById('modalVeuillezPatienter');
    if (!modal) {
        // Créer la fenêtre modale dynamiquement
        modal = document.createElement('div');
        modal.setAttribute('id', 'modalVeuillezPatienter');
        modal.setAttribute('class', 'modal fade');
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-labelledby', 'exampleModalLabel');
        modal.setAttribute('aria-hidden', 'true');
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body text-center">
                        <p>Veuillez patienter...</p>
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Chargement en cours...</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        // Ajouter la fenêtre modale au DOM
        document.body.appendChild(modal);
    }
    // Afficher la fenêtre modale
    const modalInstance = new bootstrap.Modal(modal, {backdrop: 'static', keyboard: false});
    modalInstance.show();
    return modalInstance;
}

/**
 * Ferme la fenêtre modale "Veuillez patienter"
 * @param modalInstance
 * @returns {Promise<void>}
 */
export async function fermerVeuillezPatienter(modalInstance) {
    // Cacher la fenêtre modale
    if (modalInstance) {
        modalInstance.hide();
        // modalInstance.dispose(); // Supprimer l'instance de la fenêtre modale
    }
}


/**
 * Affiche un compteur animé, personnalisable via un objet de configuration.
 *
 * @param {Object} options - Objet de configuration du compteur
 * @param {HTMLElement} options.conteneur - Élément HTML dans lequel afficher le compteur
 * @param {number} options.valeurFinale - Valeur numérique à atteindre
 * @param {number} [options.duree=1000] - Durée totale de l’animation en millisecondes
 * @param {function} [options.format] - Fonction pour formater la valeur affichée (ex: nombre → €)
 * @param {Object} [options.styles] - Styles CSS personnalisables
 */
export function afficherCompteur({
                                 conteneur,
                                  valeurFinale,
                                  duree = 1000,
                                  format = null,
                                  styles = {}
                              }) {
    // Sécurité : vérification de la cible
    if (!conteneur || !(conteneur instanceof HTMLElement)) {
        throw new Error("Un élément HTML valide doit être fourni.");
    }

    const debut = performance.now(); // Horodatage de départ
    const depart = 0; // Valeur initiale

    // Appliquer les styles par défaut + ceux fournis
    Object.assign(conteneur.style, {
        opacity: '0',
        transform: 'scale(1)',
        transition: 'opacity 0.4s ease-in, transform 0.2s ease-out',
        fontWeight: 'bold',
        fontSize: '30px',
        color: 'black',
        textAlign: 'center',
        width : '150px',
        ...styles // surcharge les valeurs par défaut si données
    });

    /**
     * Fonction d’easing pour une animation fluide (accélération/décélération)
     * t ∈ [0,1]
     */
    function easing(t) {
        return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t; // courbe "easeInOutQuad"
    }

    /**
     * Fonction appelée à chaque frame (~60 fps)
     */
    function step(timestamp) {
        const tempsEcoule = timestamp - debut;
        let progression = Math.min(tempsEcoule / duree, 1); // entre 0 et 1
        const facteur = easing(progression);

        let valeurActuelle = depart + (valeurFinale - depart) * facteur;
        let affichage = Math.floor(valeurActuelle);

        // Appliquer le formatage si une fonction est fournie
       conteneur.innerText = format ? format(affichage) : affichage;

        // Apparition en fondu
        if (progression >= 0.05 &&conteneur.style.opacity !== '1') {
           conteneur.style.opacity = '1';
        }

        // Zoom dynamique
        if (progression < 0.5) {
            const scale = 1 + 0.3 * (1 - Math.abs(0.5 - progression) * 2);
           conteneur.style.transform = `scale(${scale})`;
        } else {
           conteneur.style.transform = 'scale(1)';
        }

        // Animation encore en cours ?
        if (progression < 1) {
            requestAnimationFrame(step);
        } else {
            // Fin propre
           conteneur.innerText = format ? format(valeurFinale) : valeurFinale;
           conteneur.style.transform = 'scale(1)';
        }
    }

    requestAnimationFrame(step);
}