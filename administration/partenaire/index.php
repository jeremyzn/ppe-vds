<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// chargement des données utilisées par l'interface
$titre = "Gestion des partenaires";
// Récupération des paramètres du téléversement
$lesParametres = json_encode(Partenaire::getConfig());

$lesPartenaires = json_encode(Partenaire::getAll());

$head = <<<HTML
    <script>
        let lesPartenaires = $lesPartenaires;
        let lesParametres = $lesParametres;
    </script>
HTML;

// chargement interface
require RACINE . '/include/interface.php';