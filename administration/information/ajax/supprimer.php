<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// Vérification des droits
if (empty($_SESSION['membre'])) {
	Erreur::envoyerReponse('Accès refusé', 'global');
}

// id du document à supprimer
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
	Erreur::envoyerReponse('Paramètre id manquant', 'global');
}
$id = (int)$_POST['id'];

// récupération du document
$select = new Select();
$doc = $select->getRow('SELECT fichier FROM documentinformation WHERE id = :id', ['id' => $id]);
if (!$doc) {
	Erreur::envoyerReponse('Document introuvable', 'global');
}

$path = RACINE . '/data/documentinformation/' . $doc['fichier'];
if (is_file($path)) {
	@unlink($path);
}

// suppression BD
$db = Database::getInstance();
$stmt = $db->prepare('DELETE FROM documentinformation WHERE id = :id');
$stmt->execute(['id' => $id]);

echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
