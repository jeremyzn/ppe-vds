<?php
/**
 * Affichage d'un document d'information
 * Gère le téléchargement sécurisé des documents PDF associés aux informations
 */

require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// Vérification du paramètre attendu
if (!isset($_GET['id']) || empty($_GET['id'])) {
    Erreur::afficherReponse("Le document n'est pas précisé", 'global');
}

// Récupération du paramètre attendu
$id = $_GET['id'];

// Contrôle de la validité du paramètre
if (!preg_match('/^[0-9]+$/', $id)) {
    Erreur::bloquerVisiteur();
}

// Récupération du document correspondant depuis la base de données
$select = new Select();
$document = $select->getRow('SELECT id, fichier, nom_original, idInformation FROM documentinformation WHERE id = :id', ['id' => $id]);

// Le document doit être présent dans la table documentinformation
if (!$document) {
    Erreur::afficherReponse("Le document demandé n'existe pas", 'global');
}

$idDoc = $document['id'];
$fichier = $document['fichier'];
$nomOriginal = $document['nom_original'];

// Le document doit être présent dans le répertoire /data/documentinformation
$cheminFichier = RACINE . "/data/documentinformation/" . $fichier;
if (!is_file($cheminFichier)) {
    // Log de l'erreur et suppression automatique de l'enregistrement en base
    Journal::enregistrer("Suppression automatique du document id=$idDoc (fichier physique introuvable: $fichier)");
    
    $db = Database::getInstance();
    $stmt = $db->prepare('DELETE FROM documentinformation WHERE id = :id');
    $stmt->execute(['id' => $idDoc]);
    
    Erreur::afficherReponse("Le document demandé n'a pas été trouvé.", 'global');
}

// Log de la demande de téléchargement
Journal::enregistrer("Téléchargement document id=$idDoc, fichier=$fichier");

// Transmission sécurisée du fichier PDF
// On affiche le document dans le navigateur (inline) plutôt que de forcer le téléchargement

// Utiliser le nom original si disponible, sinon utiliser le nom du fichier
$nomAffichage = $nomOriginal ?: $fichier;

// S'assurer que le nom se termine par .pdf
if (!preg_match('/\.pdf$/i', $nomAffichage)) {
    $nomAffichage .= '.pdf';
}

header('Content-Type: application/pdf');
header("Content-Disposition: inline; filename=\"$nomAffichage\"");
header('Content-Length: ' . filesize($cheminFichier));
readfile($cheminFichier);
exit;
