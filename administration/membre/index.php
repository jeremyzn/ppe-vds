<?php

require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// chargement des données utilisées par l'interface
$titre = "Liste des membres";
$data = json_encode(Membre::getAll());

$head =<<<HTML
<script >
    const  data = $data;
</script>
HTML;


// chargement interface
require RACINE . '/include/interface.php';