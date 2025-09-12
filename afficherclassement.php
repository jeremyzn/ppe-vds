<?php
/**
 * affichage d'un classement avec comptabilisation
 */


require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// vérification du paramètre attendu :
if (!isset($_GET['id']) || empty($_GET['id'])) {
    Erreur::afficherReponse("Le classement n'est pas précisé", 'global');
}

// récupération du paramètre attendu
$id = $_GET['id'];

// contrôle de la validité du paramètre
if (!preg_match('/^[0-9]+$/', $id)) {
    Erreur::bloquerVisiteur();
}

// récupération du classement correspondant
$classement = Classement::getById($id);

// le classement doit être présent dans la table classement
if (!$classement) {
    Erreur::afficherReponse("Le classement demandé n'existe pas", 'global');
}

$id = $classement['id'];
$date = $classement['date'];
$titre = $classement['titre'];
$fichier = $classement['fichier'];


// Le classement doit être présent dans le répertoire /data/classement (fort probable)
$fichier = RACINE . "/data/classement/" . $classement['fichier'];
if (!is_file($fichier)) {
    Erreur::afficherReponse("Le classement demandé '$titre' n'a pas été trouvé.", 'global');
}

// comptabilisation de la demande au niveau du classement lui-même
Classement::comptabiliserDemande($id);

// Transmission sécurisée du fichier PDF
// On ne force pas le téléchargement du fichier

$nomFichier = $date . ' ' . $titre . '.pdf';

header('Content-Type: application/pdf');
header("Content-Disposition: inline; filename=\"$nomFichier\"");
header('Content-Length: ' . filesize($fichier));
readfile($fichier);
exit;


