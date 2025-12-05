<?php
// contrôle de l'existence des paramètres attendus
if (!isset($_FILES['fichier'], $_POST['nomFichier'])) {
    Erreur::envoyerReponse('Paramètre manquant', 'global');
}

// Récupération des paramètres du téléversement
$lesParametres = Document::getConfig();

// instanciation et paramétrage d'un objet InputFile
$file = new InputFile($_FILES['fichier'], $lesParametres);
// on force le nom du fichier à celui déjà existant
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