<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// Contrôle de l'existence du paramètre attendu : id
if (!isset($_POST['id'])) {
    Erreur::envoyerReponse("Paramètre manquant", 'global');
}

$id = $_POST['id'];

$partenaire = Partenaire::getById($id);

// s'assurer qu'il y a bien un nom de fichier avant d'appeler la suppression de fichier
if (!empty($partenaire['fichier'])) {
    Partenaire::supprimerFichier($partenaire['fichier']);
}
// suppression de l'enregistrement
Partenaire::supprimer($id);




$reponse = ['success' => "Le classement: a été supprimé"];
echo json_encode($reponse, JSON_UNESCAPED_UNICODE);
