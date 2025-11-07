<?php
declare(strict_types=1);


require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';


// Chargement des données
$titre = "Site du VDS";
// Chargement des derniers classements présents dans le répertoire 'data/classement'
$lesClassements = json_encode(Classement::getAll());

// Prochaine édition des 4 saisons
$prochaineEdition = json_encode(Epreuve::getProchaineEpreuve());

// Informations publiques (et privées si membre connecté)
$rawInfos = Information::getAll();
$lesInfos = [];
foreach ($rawInfos as $info) {
    if ($info['type'] === 'Publique' || isset($_SESSION['membre'])) {
        $info['documents'] = Information::getDocuments((int) $info['id']);
        $lesInfos[] = $info;
    }
}
$lesInformations = json_encode($lesInfos, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

// transmission des données à l'interface

$head = <<<HTML
    <script>
        const prochaineEdition = $prochaineEdition;
        const lesClassements = $lesClassements;
        const lesInformations = $lesInformations;
    </script>
HTML;

// chargement de l'interface
require RACINE . "/include/interface.php";


