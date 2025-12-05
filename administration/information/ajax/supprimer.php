<?php
/**
 * Suppression d'un document PDF associé à une information
 * Supprime le fichier physique ET l'enregistrement en base
 */

require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// ============================================================================
// CONTRÔLES DE SÉCURITÉ
// ============================================================================

if (empty($_SESSION['membre'])) {
	Erreur::envoyerReponse('Accès refusé', 'global');
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
	Erreur::envoyerReponse('Paramètre id manquant', 'global');
}
$id = (int) $_POST['id'];

// ============================================================================
// RÉCUPÉRATION ET SUPPRESSION
// ============================================================================

$select = new Select();
$doc = $select->getRow('SELECT fichier FROM documentinformation WHERE id = :id', ['id' => $id]);
if (!$doc) {
	Erreur::envoyerReponse('Document introuvable', 'global');
}

// Suppression du fichier physique
$path = RACINE . '/data/documentinformation/' . $doc['fichier'];
if (is_file($path)) {
	@unlink($path);
}

// Suppression en base de données
$db = Database::getInstance();
$stmt = $db->prepare('DELETE FROM documentinformation WHERE id = :id');
$stmt->execute(['id' => $id]);

echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
