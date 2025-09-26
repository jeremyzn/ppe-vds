<?php
declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

if (!isset($_POST['id'])) {
    Erreur::envoyerReponse('Identifiant du document manquant', 'global');
}

$id = (int)$_POST['id'];
$pdo = Database::getInstance();
$stmt = $pdo->prepare('SELECT fichier FROM documentinformation WHERE id = :id');
$stmt->execute([':id' => $id]);
$row = $stmt->fetch();
if (!$row) {
    Erreur::envoyerReponse('Document introuvable', 'global');
}

$repertoire = $_SERVER['DOCUMENT_ROOT'] . '/data/documentinformation';
$fichier = $row['fichier'];
$chemin = $repertoire . '/' . $fichier;
if (is_file($chemin)) {
    @unlink($chemin);
    if (class_exists('Journal')) {
        Journal::enregistrer("Suppression fichier $fichier pour document id=$id", 'evenement');
    }
}

$stmt = $pdo->prepare('DELETE FROM documentinformation WHERE id = :id');
$stmt->execute([':id' => $id]);

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
