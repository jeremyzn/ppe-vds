<?php
// Contrôle de l'existence du paramètre attendu : id
if (!isset($_POST['id'])) {
    Erreur::envoyerReponse("Paramètre manquant", 'global');
}

$id = (int)$_POST['id'];

// vérification de l'existence du classement:
$ligne = Classement::getById($id);
if (!$ligne) {
    Erreur::envoyerReponse("Ce classement: n'existe pas", 'global');
}

// suppression de l'enregistrement en base de données
Classement::supprimer($id);

// suppression du fichier PDF associé
Classement::supprimerFichier($ligne['fichier']);

$reponse = ['success' => "Le classement: a été supprimé"];
echo json_encode($reponse, JSON_UNESCAPED_UNICODE);
