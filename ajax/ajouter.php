<?php
declare(strict_types=1);

require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// Vérification si 'table' est transmise et est une chaîne de caractères valide
if (!isset($_POST['table']) || !is_string($_POST['table']) || empty($_POST['table'])) {
    Erreur::envoyerReponse("La table n'est pas transmise ou est invalide.", 'global');
}

// récupération des données
$table = $_POST['table'];

// création d'une instanciation dynamique de classe : déclenche spl_autoload_register
$table = new $table();
// Ajout dans la table en vérifiant que tous les champs sont corrects
if (!$table->donneesTransmises()) {
    $reponse = ['error' => $table->getLesErreurs()];
} elseif (!$table->checkAll()) {
    $reponse = ['error' => $table->getLesErreurs()];
} else {
    $table->insert();
    $reponse = ['success' => $table->getLastInsertId()];
}
header('Content-Type: application/json; charset=utf-8');
echo json_encode($reponse, JSON_UNESCAPED_UNICODE);
