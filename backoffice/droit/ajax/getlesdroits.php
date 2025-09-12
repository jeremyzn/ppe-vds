<?php
declare(strict_types=1);
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/backoffice/include/autoload.php';



// contrôle de l'existence des paramètres attendus
if (!isset($_POST['idAdministrateur']) || !filter_var($_POST['idAdministrateur'], FILTER_VALIDATE_INT) ) {
    Erreur::envoyerReponse("Requête mal formulée");
}

// Récupération des données
$idAdministrateur = (int) $_POST['idAdministrateur'];

// récupération des droits (repertoire) de cet administrateur
echo json_encode(Administrateur::getLesFonctionsAutorisees($idAdministrateur));
