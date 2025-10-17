<?php
// enregistrement (ajout/modification) d'une information depuis l'administration
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// droits
if (empty($_SESSION['membre'])) {
    Erreur::envoyerReponse('Accès refusé', 'global');
}

// on s'attend à titre, contenu, type (Publique|Privée) et éventuellement id
if (!Std::existe('table', 'titre', 'contenu', 'type')) {
    Erreur::envoyerReponse('Paramètres manquants', 'global');
}

$tableName = $_POST['table'];
if ($tableName !== 'Information' && $tableName !== 'information') {
    Erreur::envoyerReponse('Table non autorisée', 'global');
}

// création dynamique d'un objet Information
$info = new Information();

// alimentation
// titre, contenu, type
if (isset($_POST['titre'])) $info->setValue('titre', $_POST['titre']);
if (isset($_POST['contenu'])) $info->setValue('contenu', $_POST['contenu']);

// validation du type (enum)
$allowed = ['Publique', 'Privée'];
if (!isset($_POST['type']) || !in_array($_POST['type'], $allowed, true)) {
    Erreur::envoyerReponse('Type invalide', 'type');
}
$info->setValue('type', $_POST['type']);

// auteur depuis la session (nom + prénom si disponibles)
$auteur = '';
if (isset($_SESSION['membre']['prenom']) || isset($_SESSION['membre']['nom'])) {
    $prenom = $_SESSION['membre']['prenom'] ?? '';
    $nom = $_SESSION['membre']['nom'] ?? '';
    $auteur = trim($prenom . ' ' . $nom);
}
$info->setValue('auteur', $auteur ?: ($_SESSION['membre']['login'] ?? ''));

// si id présent => update
if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
    $id = (int)$_POST['id'];
    // construire le tableau des valeurs à mettre à jour
    $lesValeurs = [];
    foreach (['titre','contenu','type','auteur'] as $col) {
        $lesValeurs[$col] = $info->getColonne($col)->Value;
    }
    $info->update($id, $lesValeurs);
    echo json_encode(['success' => $id], JSON_UNESCAPED_UNICODE);
    exit;
}

// sinon insert
if (!$info->donneesTransmises() || !$info->checkAll()) {
    echo json_encode(['error' => $info->getLesErreurs()], JSON_UNESCAPED_UNICODE);
    exit;
}

$info->insert();
echo json_encode(['success' => $info->getLastInsertId()], JSON_UNESCAPED_UNICODE);
