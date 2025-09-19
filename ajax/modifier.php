<?php
declare(strict_types=1);
// Modification d'un enregistrement dans une table possédant une clé primaire non composée
// Paramètres attendus
//     table : nom de la table concerné
//     id : valeur de la clé primaire permettant d'identifieer l'enregistrement concernée
//     lesValeurs : des autres champs obligatoires de la table (modifiées ou non)


require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// vérification de la transmission des données attendues
if (!Std::existe('table', 'id', 'lesValeurs') ) {
    Erreur::envoyerReponse('Tous les paramètres attendus ne sont pas transmis', 'global');
}

// récupération des données 
$id = $_POST["id"];
$lesValeurs = json_decode($_POST['lesValeurs'], true);
$table = $_POST['table'];

// Contrôle sur le nom de la classe
if (! class_exists($table)) {
    Erreur::envoyerReponse("La classe $table n'existe pas.", 'global');
}

// vérification des valeurs
if (!is_array($lesValeurs) || empty($lesValeurs)) {
    Erreur::envoyerReponse("Aucune donnée modifiée n'a été transmise", 'global');
}

// Réalisation de la modification
$table = new $table();
$table->update($id, $lesValeurs);

echo json_encode(['success' => "Opération réalisée avec succès"]);
