<?php

// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// récupération de la configuration
$config = Partenaire::getConfig();

// Gestion du fichier (clé attendue : 'fichier')
$file = null;
if (isset($_FILES['fichier']) && isset($_FILES['fichier']['error']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
    $file = new InputFileImg($_FILES['fichier'], $config);
    if (!$file->checkValidity()) {
        Erreur::envoyerReponse($file->getValidationMessage(), 'global');
    }
} else {
    // si le fichier est obligatoire côté config -> erreur
    if (!empty($config['require'])) {
        Erreur::envoyerReponse("Le fichier n'a pas été transmis", 'global');
    }
}

// création d'un objet Partenaire pour vérifier les champs
$partenaire = new Partenaire();

// Les données ont-elles été transmises ?
if (!$partenaire->donneesTransmises()) {
    Erreur::envoyerReponse("Toutes les données attendues ne sont pas transmises", 'global');
}

// Toutes les données sont-elles valides ?
if (!$partenaire->checkAll()) {
    Erreur::envoyerReponse("Certaines données transmises ne sont pas valides", 'global');
}

// Si un fichier est présent, alimenter la colonne 'fichier'
if ($file !== null) {
    $partenaire->setValue('fichier', $file->Value);
}

// Ajout dans la table du partenaire
$partenaire->insert();

// Récupération de l'identifiant inséré
$id =  $partenaire->getLastInsertId();

// si fichier présent, copier physiquement
if ($file !== null) {
    $ok = $file->copy();
    if (!$ok) {
        // suppression de l'enregistrement pour cohérence
        $partenaire->delete($id);
        Erreur::envoyerReponse("L'ajout a échoué car le fichier n'a pas pu être téléversé", 'global');
    } else {
        // mise à jour du logo dans la table partenaire (uniquement si copie OK)
        Partenaire::majLogo($id, $file->Value);
    }
}


$reponse = ['success' => $id];
echo json_encode($reponse, JSON_UNESCAPED_UNICODE);