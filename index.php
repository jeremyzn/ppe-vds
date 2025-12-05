<?php
declare(strict_types=1);


require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';


// Chargement des données
$titre = "Site du VDS";
// Chargement des derniers classements présents dans le répertoire 'data/classement'
$lesClassements = json_encode(Classement::getAll());

$prochaineEdition = json_encode(Epreuve::getProchaineEpreuve());

$lesDocuments = json_encode(Document::getVisible());

// récupération du contenu de la page mentions légales et de la politique de confidentialité
// pour l'affichage dans le pied de page
$mention = Page::getMentions();
$politique = Page::getPolitique();


// transmission des données à l'interface
$head = <<<HTML
    <script>
        const prochaineEdition = $prochaineEdition;
        const lesClassements = $lesClassements;
        const lesDocuments = $lesDocuments;
    </script>
HTML;

// chargement de l'interface
require RACINE . "/include/interface.php";


