<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/backoffice/include/autoload.php';

// transmission des données à l'interface
$lesEvenements = json_encode(Journal::getLesEvenements('erreur'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$head =<<<HTML
    <script>
        const lesEvenements = $lesEvenements;
    </script>
HTML;

// chargement de l'interface
require $_SERVER['DOCUMENT_ROOT'] . "/backoffice/include/interface.php";
