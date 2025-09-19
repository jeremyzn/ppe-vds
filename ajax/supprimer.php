<?php

require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// vérification de la transmission des données attendues
if (!Std::existe('table', 'id') ) {
    Erreur::envoyerReponse('Tous les paramètres attendus ne sont pas transmis', 'global');
}

// récupération des données
$id = $_POST["id"];
$table = $_POST['table'];

// Réalisation de la suppression
$table = new $table();
$table->delete($_POST['id']);
echo json_encode(['success' => "Opération réalisée avec succès"]);
