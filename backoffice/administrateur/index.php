<?php
/**
 *  Ajout ou suppression d'un administrateur : membre qui va recevoir des droits sur les fonctionnalités du menu gérer
 *  Attribution de ses droits
 *
 */
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/backoffice/include/autoload.php';

// récupération des administrateurs, des membres et des options du menu vertical
$lesMembres = json_encode(Administrateur::getLesMembres());
$lesAdministrateurs = json_encode(Administrateur::getLesAdministrateurs());

// transmission des données à l'interface
$head = <<<HTML
    <script src="/composant/autocomplete/autocomplete.min.js"></script>
    <link rel="stylesheet" href="/composant/autocomplete/autocomplete.css">   
    <script>
        const lesMembres = $lesMembres;
        const lesAdministrateurs = $lesAdministrateurs;
    </script>
HTML;

// chargement de l'interface
require RACINE . "/backoffice/include/interface.php";
