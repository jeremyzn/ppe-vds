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

if (!Information::deleteDocument($id)) {
	Erreur::envoyerReponse('Document introuvable', 'global');
}

echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
