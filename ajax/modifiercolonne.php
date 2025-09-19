<?php

require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// vérification de la transmission des données attendues
if (!Std::existe('table', 'id', 'colonne', 'valeur') ) {
    Erreur::envoyerReponse('Tous les paramètres attendus ne sont pas transmis', 'global');
}

// récupération des données ; ne pas encoder avec htmlspecialchar sinon pb avec checkvalidity (' par exemple)
$id = $_POST["id"];
$valeur = $_POST["valeur"];
$colonne = $_POST["colonne"];
$table = $_POST['table'];

// Contrôle sur le nom de la classe
if (!class_exists($table)) {
    Erreur::envoyerReponse("La classe $table n'existe pas.", 'global');
}

// Réalisation de la modification en mode colonne
$table = new $table();

$table->modifierColonne($colonne, $valeur, $id);
echo json_encode(['success' => "Opération réalisée avec succès"]);
