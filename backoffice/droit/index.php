<?php
/**
 *  Ajout ou suppression d'un administrateur : membre qui va recevoir des droits sur les fonctionnalités du menu gérer
 *  Attribution de ses droits
 *
 */
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/backoffice/include/autoload.php';


// récupération des administrateurs et des fonctions
$lesAdministrateurs = json_encode(Administrateur::getLesAdministrateurs());
$lesFonctions = json_encode(Fonction::getAll());

// transmission des données à l'interface
$head = <<<HTML
    <script>
        const lesAdministrateurs = $lesAdministrateurs;
        const lesFonctions = $lesFonctions;
    </script>
HTML;

// chargement de l'interface
require RACINE . "/backoffice/include/interface.php";
