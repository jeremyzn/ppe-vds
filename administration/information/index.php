<?php
/**
 * Administration des informations
 * Point d'entrée de la gestion des informations (CRUD)
 * Prépare les données JSON pour le front-end (tableau + documents)
 */

require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

$titre = "Gestion des informations";

// Récupération de toutes les informations (pour le tableau)
$lesInformations = json_encode(Information::getAll(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

// Préparation de la map des documents par information (clé = id information)
$docsMap = [];
foreach (Information::getAll() as $info) {
    $docsMap[$info['id']] = Information::getDocuments((int) $info['id']);
}
$lesDocumentsInfo = json_encode($docsMap, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

// Injection des données JavaScript + chargement de TinyMCE
$head = <<<HTML
<script>
    let lesInformations = $lesInformations;
    let lesDocumentsInfo = $lesDocumentsInfo;
</script>
<script src="/composant/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
HTML;

require RACINE . '/include/interface.php';