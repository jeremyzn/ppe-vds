<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// vérification de la transmission  du paramètre
if (!isset($_POST["nomFichier"])) {
    Erreur::envoyerReponse("Le paramètre nomFichier n'est pas transmis", 'global');
}

// récupération du nom du fichier
$nomFichier = $_POST['nomFichier'];

// récupération du nom du fichier
$nomFichier = $_POST['nomFichier'];

$resultat = FichierImage::supprimer($nomFichier);

if ($resultat['success']) {
    echo json_encode(FichierImage::getAll(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} else {
    Erreur::envoyerReponse($resultat['message'], 'global');
}

