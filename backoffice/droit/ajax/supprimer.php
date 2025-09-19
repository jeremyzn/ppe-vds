<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/backoffice/include/autoload.php';

// vérification des données attendues
if (!isset($_POST['idAdministrateur']) ) {
    Erreur::envoyerReponse("Paramètre manquant", 'global');
}

if (!filter_var($_POST['idAdministrateur'], FILTER_VALIDATE_INT) ) {
    Erreur::envoyerReponse("Paramètre invalide", 'global');
}

if (!isset($_POST['repertoire']) ) {
    Erreur::envoyerReponse("Requête mal formulée");
}

// récupération des données transmises
$idAdministrateur = intval($_POST["idAdministrateur"]);
$repertoire = $_POST["repertoire"];

// demande de suppression du droit d'accès
Administrateur::supprimerDroit($idAdministrateur, $repertoire);

// réponse du serveur
echo json_encode(['success' => "Opération réalisée avec succès"], JSON_UNESCAPED_UNICODE);
