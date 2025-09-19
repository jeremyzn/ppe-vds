<?php
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// récupération des séances
$data =  json_encode(Epreuve::getAll());

// transmission des données à l'interface
$head = <<<HTML
       <script src="/composant/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        const lesEpreuves = $data;
    </script>
HTML;


// chargement de l'interface
require RACINE . "/include/interface.php";