<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/backoffice/include/autoload.php';

// vérification des données attendues
if (!isset($_POST['idAdministrateur']) || !filter_var($_POST['idAdministrateur'], FILTER_VALIDATE_INT) ) {
    Erreur::envoyerReponse("Requête mal formulée");
}

if (!isset($_POST['repertoire']) ) {
    Erreur::envoyerReponse("Requête mal formulée");
}

// récupération des données transmises
$idAdministrateur = (int) $_POST["idAdministrateur"];
$repertoire = $_POST["repertoire"];

// demande d'ajout d'un droit pour un administrateur
Administrateur::ajouterDroit($idAdministrateur, $repertoire);

// réponse du serveur
echo json_encode(['success' => "Opération réalisée avec succès"], JSON_UNESCAPED_UNICODE);

