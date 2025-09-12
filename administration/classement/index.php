<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// chargement des données utilisées par l'interface
$titre = "Gestions des classements";
// Récupération des paramètres du téléversement
$lesParametres = json_encode(Classement::getConfig());

$lesClassements= json_encode(Classement::getAll());

$head = <<<HTML
    <script>
        let lesClassements= $lesClassements;
        let lesParametres = $lesParametres;
    </script>
HTML;

// chargement interface
require RACINE . '/include/interface.php';