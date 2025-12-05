<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';


$photo = json_encode(Membre::getPhoto($_SESSION['membre']['id']));
$lesParametres = json_encode(Membre::getConfig());

// transmission des données à l'interface
$head = <<<HTML
     <script>
        const photo = $photo;
        const lesParametres = $lesParametres;
     </script>
HTML;

// chargement de l'interface
require RACINE . "/include/interface.php";
