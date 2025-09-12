<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/backoffice/include/autoload.php';

// contrôle de l'existence des paramètres attendus
if (!isset($_POST['idAdministrateur']) ) {
    Erreur::envoyerReponse("Paramètre manquant", 'global');
}

if (!filter_var($_POST['idAdministrateur'], FILTER_VALIDATE_INT) ) {
    Erreur::envoyerReponse("Paramètre invalide", 'global');
}

$idAdministrateur = (int) $_POST['idAdministrateur'];

// ajout de tous les droits pour cet administrateur,
Administrateur::ajouterTousLesDroits($idAdministrateur);
echo json_encode(['success' => "Opération réalisée avec succès"], JSON_UNESCAPED_UNICODE);
