<?php
declare(strict_types=1);

require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// Expected usage: afficherdocumentinformation.php?id=123
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    $_SESSION['erreur']['message'] = 'Identifiant de document manquant';
    header('Location: /erreur');
    exit;
}

// Récupérer les informations du document
$pdo = Database::getInstance();
$stmt = $pdo->prepare('SELECT d.fichier, d.idInformation, i.type FROM documentinformation d JOIN information i ON i.id = d.idInformation WHERE d.id = :id');
$stmt->execute([':id' => $id]);
$doc = $stmt->fetch();
if (!$doc) {
    $_SESSION['erreur']['message'] = 'Document introuvable en base de données.';
    header('Location: /erreur');
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
    // Supprimer l'entrée en base de données car le fichier n'existe plus
    $stmtDelete = $pdo->prepare('DELETE FROM documentinformation WHERE id = :id');
    $stmtDelete->execute([':id' => $id]);
    
    // Journaliser la suppression
    Journal::enregistrer("Suppression automatique du document id=$id (fichier physique introuvable: {$doc['fichier']})", 'evenement');
    
    $_SESSION['erreur']['message'] = 'Fichier PDF introuvable sur le serveur. L\'entrée en base de données a été supprimée.';
    header('Location: /erreur');
    exit;
}

// Journaliser la consultation
Journal::enregistrer("Téléchargement document id=$id, fichier={$doc['fichier']}", 'evenement');

// Déterminer si on force le téléchargement ou l'affichage inline
$forceDownload = isset($_GET['download']) && $_GET['download'] == '1';
$disposition = $forceDownload ? 'attachment' : 'inline';

// Envoyer les en-têtes et afficher le fichier PDF
header('Content-Type: application/pdf');
header('Content-Disposition: ' . $disposition . '; filename="' . basename($fichierPhysique) . '"');
header('Content-Length: ' . filesize($fichierPhysique));
readfile($fichierPhysique);
exit;
