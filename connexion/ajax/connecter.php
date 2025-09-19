<?php

require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// Vérification de la transmission des données attendues
if (!Std::existe('login', 'password')) {
    Erreur::envoyerReponse("Tous les paramètres attendus n'ont pas été transmis", 'global');
}

// récupération des données
$login = $_POST["login"];
$password = $_POST["password"];

// vérification du login
if (!preg_match('/^[a-zA-Z]{2,}$/', $login)) {
    Erreur::envoyerReponse('Nom d’utilisateur et/ou mot de passe incorrect.', 'global');
} else {
    $membre = Membre::getByLogin($login);
    if (!$membre) {
        Erreur::envoyerReponse('Nom d’utilisateur et/ou mot de passe incorrect.', 'global');
    }
}

// vérification du mot de passe
if (!Membre::verifierPassword($membre['id'], $password)) {
    Erreur::envoyerReponse('Nom d’utilisateur et/ou mot de passe incorrect.', 'global');
}

// Mémorisation de la connexion
Membre::connexion($membre);

// Vers quelle page faut-il être redirigé ?
if(isset($_SESSION['url'])) {
    $url = $_SESSION['url'];
    unset($_SESSION['url']);
} else {
    $url = '/';
}

$reponse = ['success' => $url];
echo json_encode($reponse, JSON_UNESCAPED_UNICODE);

