<?php
declare(strict_types=1);
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// vérification des paramètres
if (!isset($_POST['idInformation'])) {
    Erreur::envoyerReponse('Identifiant d\'information manquant', 'global');
}

$idInformation = (int)$_POST['idInformation'];

if (!isset($_FILES['fichiers'])) {
    Erreur::envoyerReponse('Aucun fichier transmis', 'global');
}

$repertoire = $_SERVER['DOCUMENT_ROOT'] . '/data/documentinformation';
if (!is_dir($repertoire)) {
    if (!mkdir($repertoire, 0755, true)) {
        Erreur::envoyerReponse('Impossible de créer le répertoire de stockage', 'global');
    }
}

$uploaded = [];
$errors = [];

// Taille max recommandée (ex: 10MB)
$tailleMax = 10 * 1024 * 1024;

foreach ($_FILES['fichiers']['error'] as $index => $error) {
    $originalName = $_FILES['fichiers']['name'][$index] ?? '';
    if ($error !== UPLOAD_ERR_OK) {
        // Messages d'erreur plus explicites
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => "dépasse la limite du serveur (upload_max_filesize dans php.ini)",
            UPLOAD_ERR_FORM_SIZE => "dépasse la limite du formulaire",
            UPLOAD_ERR_PARTIAL => "n'a été que partiellement téléversé",
            UPLOAD_ERR_NO_FILE => "n'a pas été téléversé",
            UPLOAD_ERR_NO_TMP_DIR => "répertoire temporaire manquant",
            UPLOAD_ERR_CANT_WRITE => "échec d'écriture sur le disque",
            UPLOAD_ERR_EXTENSION => "bloqué par une extension PHP"
        ];
        $errorMsg = $errorMessages[$error] ?? "erreur inconnue (code $error)";
        $errors[] = "Fichier '$originalName' : $errorMsg";
        continue;
    }

    $tmp = $_FILES['fichiers']['tmp_name'][$index];
    if (!is_uploaded_file($tmp)) {
        $errors[] = "Fichier '$originalName' invalide ou introuvable";
        continue;
    }

    // contrôle taille
    if ($_FILES['fichiers']['size'][$index] > $tailleMax) {
        $tailleEnMo = round($_FILES['fichiers']['size'][$index] / (1024 * 1024), 2);
        $limiteEnMo = round($tailleMax / (1024 * 1024), 2);
        $errors[] = "Fichier '$originalName' trop volumineux ({$tailleEnMo} Mo, limite: {$limiteEnMo} Mo)";
        continue;
    }

    // contrôle mime
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);
    if ($mime !== 'application/pdf') {
        $errors[] = "Fichier '$originalName' n'est pas un PDF (mime=$mime)";
        continue;
    }

    // sécuriser le nom original et construire un nom lisible
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    $base = pathinfo($originalName, PATHINFO_FILENAME);
    $safeBase = preg_replace('/[^A-Za-z0-9_ -]/', '_', $base);
    $safeBase = preg_replace('/[\s]+/', '_', $safeBase);
    $timestamp = date('Ymd-His');
    $candidate = $safeBase . '_' . $timestamp . '.' . $ext;
    $dest = $repertoire . DIRECTORY_SEPARATOR . $candidate;
    $i = 1;
    // si collision, ajouter un suffixe incrémental lisible
    while (is_file($dest)) {
        $candidate = $safeBase . '_' . $timestamp . '_' . $i . '.' . $ext;
        $dest = $repertoire . DIRECTORY_SEPARATOR . $candidate;
        $i++;
    }
    $unique = $candidate; // nom final utilisé en base

    if (!move_uploaded_file($tmp, $dest)) {
        $errors[] = "Échec du déplacement pour '$originalName'";
        continue;
    }

    // insert en base
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare('INSERT INTO documentinformation (fichier, idInformation) VALUES (:fichier, :idInfo)');
    $ok = $stmt->execute([':fichier' => $unique, ':idInfo' => $idInformation]);
    if ($ok) {
        $lastId = $pdo->lastInsertId();
        $uploaded[] = $lastId;
        // journaliser
        if (class_exists('Journal')) {
            Journal::enregistrer("Téléversement document id=$lastId, fichier=$unique", 'evenement');
        }
    } else {
        // si échec DB, supprimer le fichier physique
        @unlink($dest);
        $errors[] = "Échec insertion DB pour '$originalName'";
    }
}

if (empty($uploaded)) {
    Erreur::envoyerReponse(implode(' ; ', $errors ?: ['Aucun fichier n\'a été accepté']), 'global');
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => true, 'uploaded' => $uploaded], JSON_UNESCAPED_UNICODE);
