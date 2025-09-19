<?php

require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// alimentation de l'interface
$titre = 'Mentions légales';
$data = json_encode(Page::getMentions());

$head = <<<HTML
    <script src="/composant/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        const data = $data;
    </script>
HTML;

// chargement de l'interface
require RACINE . '/include/interface.php';