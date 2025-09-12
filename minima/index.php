<?php
// Chargement des classes
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

$titreFonction = 'Minima pour les Championnats de France';

// Encodage des minimas en JSON
$minimaRoute = json_encode(Minima::getRouteMinima());
$minima5km = json_encode(Minima::getMinima5km());

$head = <<<HTML
    <script src="/composant/html2pdf/html2pdf.bundle.min.js"></script>
    <script>
        const lesMinimasRoute = $minimaRoute;
        const lesMinimas5km = $minima5km;
    </script>
HTML;

// Chargement de la page d'interface
require RACINE . '/include/interface.php';
