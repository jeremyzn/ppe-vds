<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/backoffice/include/autoload.php';


if (!isset($_POST['id']) ) {
    Erreur::envoyerReponse("Paramètre manquant", 'global');
}

if (!filter_var($_POST['id'], FILTER_VALIDATE_INT) ) {
    Erreur::envoyerReponse("Paramètre invalide", 'global');
}

// récupération des données transmises
$id = intval($_POST["id"]);

Administrateur::ajouterAdministrateur($id);
echo json_encode(['success' => "Opération réalisée avec succès"], JSON_UNESCAPED_UNICODE);
