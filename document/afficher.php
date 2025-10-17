<?php
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';
// vérification du paramètre attendu
if (!isset($_GET['id']) || empty($_GET['id'])) {
    Erreur::afficherReponse("Le document n'est pas précisé", 'global');
}
// récupération du paramètre attendu
$id = $_GET['id'];
// contrôle de la validité du paramètre
if (!preg_match('/^[0-9]+$/', $id)) {
    Erreur::bloquerVisiteur();
}
// récupération du document correspondant
$document = Document::getById($id);
// le document doit être présent dans la table document
if (!$document) {
    Erreur::afficherReponse("Le document demandé n'existe pas", 'global');
}
$id = $document['id'];
$titre = $document['titre'];
$fichier = $document['fichier'];

// Le document doit être présent dans le répertoire de stockage.
// Il faut récupérer ce répertoire dans la configuration
$repertoire = Document::getConfig()['repertoire'];
$uri = RACINE . "$repertoire/$fichier";
if (!is_file($uri)) {
    Erreur::afficherReponse("Le document demandé '$titre' n'a pas été trouvé.", 'global');
}
// afficher le document à l'écran
header('Content-Type: application/pdf');
header("Content-Disposition: inline; filename=\"$fichier\"");
header('Content-Length: ' . filesize($uri));
readfile($uri);
exit;