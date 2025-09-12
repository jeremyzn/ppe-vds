<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/backoffice/include/autoload.php';


// Vérification du paramètre attendu : id
if (!isset($_POST['id']) || !filter_var($_POST['id'], FILTER_VALIDATE_INT)) {
    Erreur::envoyerReponse("Requête mal formulée");
}

// récupération des données transmises
$id = intval($_POST["id"]);

// demande de suppression de l'administrateur
Administrateur::supprimerAdministrateur($id);

// réponse du serveur : message de succès
echo json_encode(['success' => "Opération réalisée avec succès"], JSON_UNESCAPED_UNICODE);
