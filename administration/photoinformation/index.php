<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// récupération des paramètres du téléversement
$lesParametres = json_encode(FichierImage::getConfig(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

// récupération des fichiers PDF
$lesFichiers = json_encode(FichierImage::getAll(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$head = <<<EOD
<script>
     const lesFichiers = $lesFichiers;
     const lesParametres = $lesParametres;
</script>
EOD;

// chargement de l'interface
require RACINE . "/include/interface.php";
