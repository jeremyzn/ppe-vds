<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// Contrôle sur le fichier téléversé
if (!isset($_FILES['fichier'])) {
    Erreur::envoyerReponse("Le fichier n'a pas été transmis", 'global');
}

// instanciation et paramétrage d'un objet InputFile
$file = new InputFileImg($_FILES['fichier'], FichierImage::getConfig());

$resultat = FichierImage::ajouter($file);

if (!$resultat['success']) {
    Erreur::envoyerReponse($resultat['message'], 'global');
}

echo json_encode($resultat['fichiers'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);



