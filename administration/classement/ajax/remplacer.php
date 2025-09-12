<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';


// contrôle de l'existence des paramètres attendus
if (!isset($_FILES['fichier']) || !isset($_POST['nomFichier'])) {
    Erreur::envoyerReponse("Paramètre manquant", 'global');
}

// Récupération des paramètres du téléversement
$lesParametres = Classement::getConfig();

// instanciation et paramétrage d'un objet InputFile
$file = new InputFile($lesParametres);
$file->Value = $_POST['nomFichier'];

// ici le fichier existe déjà, il faut passer en mode 'update' pour autoriser son remplacement
$file->Mode = 'update';

// contrôle de l'objet
if ($file->checkValidity()) {
    // copie du fichier
    $file->copy();
    echo json_encode(['success' => 'Le fichier a été remplacé']);
} else {
    echo json_encode(['error' => $file->getValidationMessage()]);
}