<?php

require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// chargement des données
$titre = "Mon profil";
$data = json_encode((Membre::getById($_SESSION['membre']['id'])));


// transmission des données à l'interface
$head = <<<HTML
    <script>
        const data = $data;

    </script>
HTML;

require RACINE . "/include/interface.php";