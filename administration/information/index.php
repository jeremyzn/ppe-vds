<?php
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

$titre = "Gestion des informations";

// Récupération des informations pour l'interface (format JSON pour le front)
$data = json_encode(Information::getAll(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$head = <<<HTML
<script>
    const data = $data;
</script>
HTML;

// chargement de l'interface (header/footer et injection de $head)
require RACINE . '/include/interface.php';