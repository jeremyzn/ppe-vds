<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// chargement des données utilisées par l'interface
$titre = "Ajout d'un partenaire";
// Récupération des paramètres du téléversement
$lesParametres = json_encode(Partenaire::getConfig());

$head =<<<HTML
    <script>
         const lesParametres = $lesParametres;
    </script>
HTML;

// affichage de l'interface
require RACINE . '/include/interface.php';