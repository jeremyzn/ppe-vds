<?php
declare(strict_types=1);

require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// chargement des données
$data = json_encode(Page::getClub());

// transmission des données à l'interface
$head = <<<HTML
    <script>
        const data = $data
    </script>
HTML;

// chargement de l'interface
require RACINE . "/include/interface.php";


