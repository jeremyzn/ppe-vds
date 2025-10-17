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
$idInformation = (int)$_POST['idInformation'];

if (!isset($_FILES['fichier'])) {
	Erreur::envoyerReponse('Le fichier n\'a pas été transmis', 'global');
}

// instanciation et paramétrage d'un objet InputFile
$file = new InputFile($_FILES['fichier'], Information::getConfig());
if (!$file->checkValidity()) {
	Erreur::envoyerReponse($file->getValidationMessage(), 'fichier');
}

// copie du fichier physique
if (!$file->copy()) {
	Erreur::envoyerReponse($file->getValidationMessage(), 'fichier');
}

// enregistrement en base
$db = Database::getInstance();
$sql = "INSERT INTO documentinformation (fichier, idInformation, nom_original) VALUES (:fichier, :idInformation, :nom_original)";
$stmt = $db->prepare($sql);
$stmt->execute([
	'fichier' => $file->Value,
	'idInformation' => $idInformation,
	'nom_original' => $_FILES['fichier']['name']
]);
$id = (int)$db->lastInsertId();

echo json_encode(['success' => true, 'id' => $id, 'fichier' => $file->Value], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
