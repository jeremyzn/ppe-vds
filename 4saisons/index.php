<?php
declare(strict_types=1);

require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// chargement des données
$titre = "Les 4 saisons";
$data = json_encode(Page::get4Saisons());

// transmission des données à l'interface
$head = <<<HTML
    <script>
        const data = $data
    </script>
HTML;

// chargement de l'interface
require RACINE . "/include/interface.php";


