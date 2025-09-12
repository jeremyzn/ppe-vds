<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// récupération des données
$id = $_SESSION['membre']['id'];

// suppression de la photo dans la table membre
Membre::supprimerPhoto($id);

// réponse du serveur : nom du fichier stocké
echo json_encode(['success' => "Votre photo a été supprimée avec succès"], JSON_UNESCAPED_UNICODE);