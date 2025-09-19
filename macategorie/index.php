<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// chargement interface
$titre = "Connaitre sa catégorie le jour de la course";
$lesLignes = Categorie::getAll();
$data = json_encode($lesLignes);

$head =<<<HTML

<script>
    const lesCategories = $data;
</script>
HTML;

// chargement de la page
require RACINE . "/include/interface.php";

