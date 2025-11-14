<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// Vérification des droits
if (empty($_SESSION['membre'])) {
	Erreur::envoyerReponse('Accès refusé', 'global');
}

// Vérification des paramètres
if (!isset($_POST['idInformation']) || !is_numeric($_POST['idInformation'])) {
	Erreur::envoyerReponse('Information non précisée', 'global');
}
$idInformation = (int) $_POST['idInformation'];

if (!isset($_FILES['fichier'])) {
	Erreur::envoyerReponse('Le fichier n\'a pas été transmis', 'global');
}

// Normaliser $_FILES pour supporter l'envoi multiple (name[], multiple)
$uploadedFiles = [];
if (is_array($_FILES['fichier']['name'])) {
	$count = count($_FILES['fichier']['name']);
	for ($i = 0; $i < $count; $i++) {
		$uploadedFiles[] = [
			'name' => $_FILES['fichier']['name'][$i],
			'tmp_name' => $_FILES['fichier']['tmp_name'][$i],
			'error' => $_FILES['fichier']['error'][$i],
			'size' => $_FILES['fichier']['size'][$i],
			'type' => $_FILES['fichier']['type'][$i] ?? ''
		];
	}
} else {
	$uploadedFiles[] = $_FILES['fichier'];
}

// Traitement de chaque fichier
$db = Database::getInstance();
$sql = "INSERT INTO documentinformation (fichier, idInformation, nom_original) VALUES (:fichier, :idInformation, :nom_original)";
$results = [];
foreach ($uploadedFiles as $f) {
	// instanciation et paramétrage d'un objet InputFile pour chaque fichier
	$file = new InputFile($f, Information::getConfig());
	if (!$file->checkValidity()) {
		Erreur::envoyerReponse($file->getValidationMessage(), 'fichier');
	}

	// copie du fichier physique
	if (!$file->copy()) {
		Erreur::envoyerReponse($file->getValidationMessage(), 'fichier');
	}

	// enregistrement en base
	$stmt = $db->prepare($sql);
	$stmt->execute([
		'fichier' => $file->Value,
		'idInformation' => $idInformation,
		'nom_original' => $f['name']
	]);
	$id = (int) $db->lastInsertId();
	$results[] = ['id' => $id, 'fichier' => $file->Value, 'nom_original' => $f['name']];
}

echo json_encode(['success' => true, 'files' => $results], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
