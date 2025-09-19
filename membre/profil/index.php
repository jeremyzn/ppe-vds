<?php

require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// chargement des données
$titre = "Mon profil";
$data = json_encode((Membre::getById($_SESSION['membre']['id'])));
$lesOptions = file_get_contents(RACINE . '/membre/.config/menuhorizontal.json');

// transmission des données à l'interface
$head = <<<HTML
    <script>
        const data = $data;
        const lesOptions = $lesOptions;
    </script>
HTML;

require RACINE . "/include/interface.php";