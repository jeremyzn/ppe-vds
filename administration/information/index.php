<?php
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

$titre = "Gestion des informations";

// Récupération des informations pour l'interface (format JSON pour le front)
$lesInformations = json_encode(Information::getAll(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

// préparer les documents pour chaque information afin de les injecter côté client
$docsMap = [];
foreach (Information::getAll() as $info) {
    $docsMap[$info['id']] = Information::getDocuments((int)$info['id']);
}
$lesDocumentsInfo = json_encode($docsMap, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$head = <<<HTML
<script>
    let lesInformations = $lesInformations;
    let lesDocumentsInfo = $lesDocumentsInfo;
</script>
<script src="/composant/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
HTML;

// chargement de l'interface (header/footer et injection de $head)
require RACINE . '/include/interface.php';