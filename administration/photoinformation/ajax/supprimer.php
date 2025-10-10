<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// vérification de la transmission du paramètre
if (!isset($_POST["nomFichier"])) {
    Erreur::envoyerReponse("Le nom du fichier à supprimer n'est pas transmis", 'global');
}

// récupération du nom du fichier
$nomFichier = $_POST['nomFichier'];

// suppression du fichier
$resultat = FichierImage::supprimer($nomFichier);

// renvoi de la liste des fichiers imageou d'une erreur
if (!$resultat['success']) {
    Erreur::envoyerReponse($resultat['message'], 'global');
}
echo json_encode($resultat['fichiers'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);


