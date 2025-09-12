<?php
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// transmission des données à l'interface
$titre = "Annuaire des membres";
$lesMembres = json_encode(Membre::getLesMembres());
$head = <<<HTML
    <script>
        const lesMembres = $lesMembres;
    </script>
HTML;

// chargement de l'interface
require RACINE . "/include/interface.php";
