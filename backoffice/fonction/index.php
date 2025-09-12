<?php
/**
 *  Ajout ou suppression d'une fonction d'administration
 */

// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/backoffice/include/autoload.php';

// transmission des données à l'interface
$lesFonctions = json_encode(Fonction::getAll());

$head =<<<HTML
    <script>
        const lesFonctions = $lesFonctions;
    </script>
HTML;

// chargement de l'interface
require $_SERVER['DOCUMENT_ROOT'] . "/backoffice/include/interface.php";
