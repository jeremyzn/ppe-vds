<?php
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// chargement interface
$lesLignes = Categorie::getAll();
$data = json_encode($lesLignes);

$head =<<<HTML
<script src="/composant/html2pdf/html2pdf.bundle.min.js"></script>
<script>
    const lesCategories = $data;
</script>
HTML;

// chargement de la page
require RACINE . "/include/interface.php";

