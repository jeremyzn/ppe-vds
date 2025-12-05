<?php
/**
 * Enregistrement d'une information (ajout ou modification)
 * Reçoit : titre, contenu, type (Publique|Privée), id (optionnel pour modification)
 * Retourne : JSON { success: id } ou { error: ... }
 */

require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// ============================================================================
// CONTRÔLES DE SÉCURITÉ
// ============================================================================

if (empty($_SESSION['membre'])) {
    Erreur::envoyerReponse('Accès refusé', 'global');
}

if (!Std::existe('table', 'titre', 'contenu', 'type')) {
    Erreur::envoyerReponse('Paramètres manquants', 'global');
}

$tableName = $_POST['table'];
if ($tableName !== 'Information' && $tableName !== 'information') {
    Erreur::envoyerReponse('Table non autorisée', 'global');
}

// ============================================================================
// PRÉPARATION DES DONNÉES
// ============================================================================

$info = new Information();

if (isset($_POST['titre']))
    $info->setValue('titre', $_POST['titre']);
if (isset($_POST['contenu']))
    $info->setValue('contenu', $_POST['contenu']);

// Validation du type (enum: Publique ou Privée)
$allowed = ['Publique', 'Privée'];
if (!isset($_POST['type']) || !in_array($_POST['type'], $allowed, true)) {
    Erreur::envoyerReponse('Type invalide', 'type');
}
$info->setValue('type', $_POST['type']);

// Récupération de l'auteur depuis la session
$auteur = '';
if (isset($_SESSION['membre']['prenom']) || isset($_SESSION['membre']['nom'])) {
    $prenom = $_SESSION['membre']['prenom'] ?? '';
    $nom = $_SESSION['membre']['nom'] ?? '';
    $auteur = trim($prenom . ' ' . $nom);
}
$info->setValue('auteur', $auteur ?: ($_SESSION['membre']['login'] ?? ''));

// ============================================================================
// MODIFICATION (si id présent)
// ============================================================================

if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
    $id = (int) $_POST['id'];
    $lesValeurs = [];
    foreach (['titre', 'contenu', 'type', 'auteur'] as $col) {
        $lesValeurs[$col] = $info->getColonne($col)->Value;
    }
    $info->update($id, $lesValeurs);
    echo json_encode(['success' => $id], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================================
// AJOUT (nouveau)
// ============================================================================

if (!$info->donneesTransmises() || !$info->checkAll()) {
    echo json_encode(['error' => $info->getLesErreurs()], JSON_UNESCAPED_UNICODE);
    exit;
}

$info->insert();
echo json_encode(['success' => $info->getLastInsertId()], JSON_UNESCAPED_UNICODE);
