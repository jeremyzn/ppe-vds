<?php
/**
 * Affichage sécurisé d'un document PDF
 * 
 * - Vérifie l'existence du document
 * - Contrôle l'accès (documents privés réservés aux membres connectés)
 * - Journalise les téléchargements
 * - Nettoie automatiquement les enregistrements orphelins
 */

require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// ============================================================================
// VALIDATION DU PARAMÈTRE
// ============================================================================

if (!isset($_GET['id']) || empty($_GET['id'])) {
    Erreur::afficherReponse("Le document n'est pas précisé", 'global');
}

$id = $_GET['id'];

if (!preg_match('/^[0-9]+$/', $id)) {
    Erreur::bloquerVisiteur();
}

// ============================================================================
// RÉCUPÉRATION DU DOCUMENT
// ============================================================================

$document = Information::getDocumentById((int) $id);

if (!$document) {
    Erreur::afficherReponse("Le document demandé n'existe pas", 'global');
}

$idDoc = $document['id'];
$fichier = $document['fichier'];
$nomOriginal = $document['nom_original'];
$idInformation = $document['idInformation'];

// ============================================================================
// CONTRÔLE D'ACCÈS (documents privés)
// ============================================================================

$information = Information::getType((int) $idInformation);

if (!$information) {
    Erreur::afficherReponse("L'information associée au document n'existe plus.", 'global');
}

if ($information['type'] === 'Privée' && empty($_SESSION['membre'])) {
    Erreur::afficherReponse("Ce document est réservé aux membres du club. Veuillez vous connecter.", 'global');
}

// ============================================================================
// VÉRIFICATION DU FICHIER PHYSIQUE
// ============================================================================

$cheminFichier = RACINE . "/data/documentinformation/" . $fichier;
if (!is_file($cheminFichier)) {
    // Nettoyage automatique : suppression de l'enregistrement orphelin
    Journal::enregistrer("Suppression automatique du document id=$idDoc (fichier physique introuvable: $fichier)");

    Information::deleteDocumentOrphelin($idDoc);

    Erreur::afficherReponse("Le document demandé n'a pas été trouvé.", 'global');
}

// ============================================================================
// ENVOI DU FICHIER
// ============================================================================

Journal::enregistrer("Téléchargement document id=$idDoc, fichier=$fichier");

$nomAffichage = $nomOriginal ?: $fichier;
if (!preg_match('/\.pdf$/i', $nomAffichage)) {
    $nomAffichage .= '.pdf';
}

header('Content-Type: application/pdf');
header("Content-Disposition: inline; filename=\"$nomAffichage\"");
header('Content-Length: ' . filesize($cheminFichier));
readfile($cheminFichier);
exit;
