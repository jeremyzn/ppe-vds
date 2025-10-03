<?php
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';
$titre = "Suppression d'un document";

// Création de l'instance
$document = new Document();
// Appel de la méthode d'instance
$LesDocuments = json_encode($document->getAll());

$head = <<<EOD
    <script>
        const LesDocuments = $LesDocuments;
    </script>
EOD;

require RACINE . "/include/interface.php";
