<?php
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// Si l'utilisateur est déja connecté, on le redirige vers son profil
if (isset($_SESSION['membre'])) {
    header("location:/");
    exit;
}

// chargement des données
$titre = "Connexion";

// chargement de l'interface
require RACINE . "/include/interface.php";

