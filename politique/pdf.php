<?php
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php'; // initialise les classes + session

// chargement du composant Mpdf
require RACINE . '/vendor/autoload.php';

use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;


// récupération des données depuis la base
$contenu = Page::getPolitique();

// initialisation de MPDF
$mpdf = new Mpdf([
    'format' => 'A4',
    'default_font_size' => 12,
    'default_font' => 'dejavusans' // compatible UTF-8
]);

// personnalisation optionnelle de l'en-tête ou du style
$stylesheet = <<<CSS
    body { font-family: DejaVu Sans, sans-serif; }
    h4 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
    p, li { margin-bottom: 8px; }
CSS;

// chargement du contenu dans le PDF
$mpdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
$mpdf->WriteHTML($contenu, HTMLParserMode::HTML_BODY);

// nom du fichier de sortie
$nomFichier = "Politique de sécurité du site valdesomme.fr.pdf";

// envoi direct au navigateur (inline ou attachment)
$mpdf->Output($nomFichier, Destination::INLINE); // ou ATTACHMENT pour forcer le téléchargement
