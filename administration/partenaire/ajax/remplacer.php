<?php

// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// vérification du paramètre id
if (!isset($_POST['id']) || empty($_POST['id'])) {
    Erreur::envoyerReponse("L'identifiant du partenaire n'est pas transmis", 'global');
}
$id = (int) $_POST['id'];


// récupération de la configuration
$config = Partenaire::getConfig();

// Gestion du fichier (clé attendue : 'fichier')
$file = null;
if (isset($_FILES['fichier']) && isset($_FILES['fichier']['error']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
    $file = new InputFileImg($_FILES['fichier'], $config);
    if (!$file->checkValidity()) {
        Erreur::envoyerReponse($file->getValidationMessage(), 'global');
    }
} else {
    Erreur::envoyerReponse("Le fichier n'a pas été transmis", 'global');
}

// Récupération des infos actuelles du partenaire (pour suppression ancien logo)
$ancienPartenaire = Partenaire::getById($id);
if (!$ancienPartenaire) {
    Erreur::envoyerReponse("Partenaire introuvable", 'global');
}

// Copie physique du nouveau fichier
if ($file !== null) {
    $ok = $file->copy();
    if (!$ok) {
        Erreur::envoyerReponse("Le remplacement a échoué car le fichier n'a pas pu être téléversé", 'global');
    } else {
        // mise à jour du logo dans la table partenaire
        Partenaire::majLogo($id, $file->Value);

        // suppression de l'ancien fichier si présent
        if (!empty($ancienPartenaire['fichier'])) {
            Partenaire::supprimerFichier($ancienPartenaire['fichier']);
        }
    }
}

$reponse = ['success' => 'Le logo a été remplacé'];
echo json_encode($reponse, JSON_UNESCAPED_UNICODE);