<?php
// Détection du type de réponse attendu (HTML ou JSON)
if (stripos($_SERVER['SCRIPT_FILENAME'], '/ajax/') !== false) {
    header('Content-Type: application/json; charset=utf-8');
} else {
    header('Content-Type: text/html; charset=utf-8');
}

// Accès aux variables de session
if (session_status() === PHP_SESSION_NONE) session_start();

// Définition d'une constante indiquant la racine du site
define('RACINE', $_SERVER['DOCUMENT_ROOT']);

// Autoload des classes
spl_autoload_register(function ($name) {
    $name = strtolower($name);
    $fichier = RACINE . "/classemetier/$name.php";
    if (is_file($fichier)) {
        require $fichier;
        return;
    }
    $fichier = RACINE . "/classetechnique/$name.php";
    if (is_file($fichier)) {
        require $fichier;
        return;
    }
    Erreur::TraiterReponse("Impossible de charger la classe $name", 'global');
});

