<?php
declare(strict_types=1);

require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// Expected usage: afficherdocumentinformation.php?id=123
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
    echo 'Identifiant de document manquant';
    exit;
}

// Récupérer les informations du document
$pdo = Database::getInstance();
$stmt = $pdo->prepare('SELECT d.fichier, d.idInformation, i.type FROM documentinformation d JOIN information i ON i.id = d.idInformation WHERE d.id = :id');
$stmt->execute([':id' => $id]);
$doc = $stmt->fetch();
if (!$doc) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    echo '<div style="color:red;font-weight:bold">Document introuvable en base de données.</div>';
    echo '<a href="javascript:history.back()" style="display:block;margin-top:1em">Retour</a>';
    exit;
}

// Si l'information est privée, vérifier que le membre est connecté
if ($doc['type'] === 'Privée') {
    if (!isset($_SESSION['membre'])) {
        // redirection vers la page de connexion
        header('Location: /connexion');
        exit;
    }
}

$repertoire = $_SERVER['DOCUMENT_ROOT'] . '/data/documentinformation';
$fichierPhysique = $repertoire . '/' . $doc['fichier'];

if (!is_file($fichierPhysique)) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    echo '<div style="color:red;font-weight:bold">Fichier PDF introuvable sur le serveur.</div>';
    echo '<a href="javascript:history.back()" style="display:block;margin-top:1em">Retour</a>';
    exit;
}

// Journaliser la consultation
Journal::enregistrer("Téléchargement document id=$id, fichier={$doc['fichier']}", 'evenement');

// Envoyer les en-têtes et afficher le fichier PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($fichierPhysique) . '"');
header('Content-Length: ' . filesize($fichierPhysique));
readfile($fichierPhysique);
exit;
