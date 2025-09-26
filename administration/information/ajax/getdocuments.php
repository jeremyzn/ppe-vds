<?php
declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

if (!isset($_POST['idInformation'])) {
    Erreur::envoyerReponse('Identifiant d\'information manquant', 'global');
}

$id = (int)$_POST['idInformation'];
$pdo = Database::getInstance();
$stmt = $pdo->prepare('SELECT id, fichier FROM documentinformation WHERE idInformation = :id');
$stmt->execute([':id' => $id]);
$rows = $stmt->fetchAll();

header('Content-Type: application/json; charset=utf-8');
echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
