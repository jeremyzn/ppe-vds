<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

$reponse = FichierImage::ajouter();

if ($reponse['success']) {
    echo json_encode(FichierImage::getAll(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} else {
    Erreur::envoyerReponse($reponse['message'], 'fichier');
}
