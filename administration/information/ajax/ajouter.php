<?php
/**
 * Upload de documents PDF pour une information
 * Supporte l'envoi multiple de fichiers
 * Les fichiers sont stockés dans /data/documentinformation/
 */

require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// ============================================================================
// CONTRÔLES DE SÉCURITÉ
// ============================================================================

if (empty($_SESSION['membre'])) {
	Erreur::envoyerReponse('Accès refusé', 'global');
}

if (!isset($_POST['idInformation']) || !is_numeric($_POST['idInformation'])) {
	Erreur::envoyerReponse('Information non précisée', 'global');
}
$idInformation = (int) $_POST['idInformation'];

// ============================================================================
// DÉTECTION DES FICHIERS UPLOADÉS
// ============================================================================

$filesKey = null;
if (isset($_FILES['fichier'])) {
	$filesKey = 'fichier';
} elseif (isset($_FILES['fichier[]'])) {
	$filesKey = 'fichier[]';
}

if ($filesKey === null) {
	Erreur::envoyerReponse('Le fichier n\'a pas été transmis', 'global');
}

// ============================================================================
// NORMALISATION DES FICHIERS (support upload multiple)
// ============================================================================

$uploadedFiles = [];
if (is_array($_FILES[$filesKey]['name'])) {
	$count = count($_FILES[$filesKey]['name']);
	for ($i = 0; $i < $count; $i++) {
		if ($_FILES[$filesKey]['error'][$i] === UPLOAD_ERR_NO_FILE) {
			continue;
		}
		$uploadedFiles[] = [
			'name' => $_FILES[$filesKey]['name'][$i],
			'tmp_name' => $_FILES[$filesKey]['tmp_name'][$i],
			'error' => $_FILES[$filesKey]['error'][$i],
			'size' => $_FILES[$filesKey]['size'][$i],
			'type' => $_FILES[$filesKey]['type'][$i] ?? ''
		];
	}
} else {
	$uploadedFiles[] = $_FILES[$filesKey];
}

if (empty($uploadedFiles)) {
	Erreur::envoyerReponse('Aucun fichier valide n\'a été transmis', 'global');
}

// ============================================================================
// TRAITEMENT ET ENREGISTREMENT DES FICHIERS
// ============================================================================

$results = [];

foreach ($uploadedFiles as $f) {
	// Validation du fichier (extension, taille, type MIME)
	$file = new InputFile($f, Information::getConfig());
	if (!$file->checkValidity()) {
		Erreur::envoyerReponse($file->getValidationMessage(), 'fichier');
	}

	// Copie vers le répertoire de destination
	if (!$file->copy()) {
		Erreur::envoyerReponse($file->getValidationMessage(), 'fichier');
	}

	// Insertion en base de données via la classe métier
	$id = Information::addDocument($file->Value, $idInformation, $f['name']);
	$results[] = ['id' => $id, 'fichier' => $file->Value, 'nom_original' => $f['name']];
}

echo json_encode(['success' => true, 'files' => $results], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
