<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/backoffice/include/autoload.php';

// contrôle de l'existence des paramètres attendus
if (!isset($_POST['idAdministrateur']) || !filter_var($_POST['idAdministrateur'], FILTER_VALIDATE_INT) ) {
    Erreur::envoyerReponse("Requête mal formulée");
}

$idAdministrateur = intval($_POST['idAdministrateur']);

// suppression de tous les droits de l'administrateur, il reste cependant considéré comme administrateur
Administrateur::supprimerTousLesDroits($idAdministrateur);
echo json_encode(['success' => "Opération réalisée avec succès"], JSON_UNESCAPED_UNICODE);
