<?php
// File: administration/document/ajax/supprimer.php
// Contrôle de l'existence du paramètre attendu : id
if (!isset($_POST['id'])) {
    Erreur::envoyerReponse("Paramètre manquant", 'global');
}

$id = (int)$_POST['id'];

// vérification de l'existence du document
$ligne = Document::getById($id);
if (!$ligne) {
    Erreur::envoyerReponse("Ce document n'existe pas", 'global');
}

// suppression de l'enregistrement en base de données
$ok = Document::supprimer($id);
if (!$ok) {
    Erreur::envoyerReponse("La suppression en base a échoué", 'global');
}

// suppression du fichier PDF associé (tentative, non bloquante)
Document::supprimerFichier($ligne['fichier']);

$reponse = ['success' => "Le document a été supprimé"];
echo json_encode($reponse, JSON_UNESCAPED_UNICODE);
