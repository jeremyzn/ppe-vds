<?php
declare(strict_types=1);

/**
 * Classe InputFileImg : ajoute à la classe InputFile les opérations spécifiques sur un fichier image
 * Possibilité de définir les dimensions à respecter et de redimensionner (extraire une partie en fonction des dimensions à respecter)
 * Nécessite la bibliothèque Gumlet/ImageResize
 * @Author : Guy Verghote
 * @Version : 2025.3
 * @Date : 07/10/2025
 * ajout paramètre throw à la méthode copy pour indiquer si une exception doit être levée en cas d'erreur
 */

// chargement du composant permettant de redimensionner l'image
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use Gumlet\ImageResize;
use Gumlet\ImageResizeException;

class InputFileImg extends InputFile
{
    // attributs spécifiques
    // la largeur et la hauteur pour une image si besoin
    // La possibilité de mettre en place un redimensionnement automatique de l'image qui dépasserait les dimensions définies

    //  Dimension demandée pour l'image : la hauteur et la largeur en pixel
    // L'image sera redimensionnée selon les dimensions demandées si la propriété $redimensionner est vraie sinon l'image ne devra pas dépasser ces dimensions
    public int $Width;
    public int $Height;

    // Indique si l'image doit absolument respecter les dimensions (false) ou si elle sera automatiquement redimensionnée aux dimensions demandées
    public bool $Redimensionner = false;

    /**
     * Constructeur de la classe
     *
     * @param array $lesParametres Tableau de paramètres pour configurer l'objet
     * @param array $fichier Fichier à traiter (doit être un tableau $_FILES['nomFichier'])
     */
    public function __construct( array $fichier, array $lesParametres )
    {
        parent::__construct($fichier, $lesParametres);

        // Vérification que le fichier est bien un tableau et contient les bonnes clés
        if (!is_array($fichier) || !isset($fichier['name'], $fichier['tmp_name'], $fichier['error'], $fichier['size'])) {
            Erreur::traiterReponse('Le fichier doit être un tableau valide avec les clés : name, tmp_name, error, size.', 'system');
        }

        // Initialisation des paramètres spécifiques
        $this->Height = $lesParametres['height'] ?? 0;
        $this->Width = $lesParametres['width'] ?? 0;
        $this->Redimensionner = $lesParametres['redimensionner'] ?? false;
    }

    public function checkValidity(): bool
    {
        // 1. Vérification parentale : toujours en premier
        if (!parent::checkValidity()) {
            return false;
        }

        // 2. Pas de fichier ou pas de vérification de dimensions nécessaire
        if ($this->file === null || $this->Redimensionner || ($this->Width === 0 && $this->Height === 0)) {
            return true;
        }

        // 3. Récupération des dimensions de l'image
        $imageDimensions = getimagesize($this->file['tmp_name']);
        $width = $imageDimensions[0];
        $height = $imageDimensions[1];

        // 4. Logique de vérification des dimensions (le cœur du problème)

        // Si les deux dimensions sont renseignées (non nulles)
        if ($this->Width !== 0 && $this->Height !== 0) {
            if ($width > $this->Width || $height > $this->Height) {
                $this->validationMessage = "Les dimensions de l'image ($width*$height) dépassent les dimensions acceptées ($this->Width*$this->Height)";
                return false;
            }
        } // Si seule la largeur est renseignée
        elseif ($this->Width !== 0) {
            if ($width > $this->Width) {
                $this->validationMessage = "La largeur de l'image ($width) dépasse la largeur acceptée ($this->Width)";
                return false;
            }
        } // Si seule la hauteur est renseignée
        elseif ($this->Height !== 0) {
            if ($height > $this->Height) {
                $this->validationMessage = "La hauteur de l'image ($height) dépasse la hauteur acceptée ($this->Height)";
                return false;
            }
        }

        // Si toutes les vérifications passent, l'image est valide
        return true;
    }

    /**
     * Copie du fichier téléversé sur le serveur sous le nom contenu dans la propriété Value
     * Condition : avoir appelé la méthode checkValidity avant et avoir renseigné la propriété Directory
     * @return bool
     */
    public function copy(bool $throw = false): bool
    {
        if (!$this->valide) {
            $this->validationMessage = "Le fichier doit être contrôlé avant d'être copié";
            return false;
        }

        $nomFichier = $this->Value;
        $tmpName = $this->file['tmp_name'];
        $cheminDestination = "$this->Repertoire/$nomFichier";

        // Pas de redimensionnement
        if (!$this->Redimensionner || ($this->Width === 0 && $this->Height === 0)) {
            $ok = copy($tmpName, $cheminDestination);
            if (!$ok) {
                $this->validationMessage = "Erreur lors de la copie du fichier vers $this->Repertoire/$nomFichier";
                return false;
            }
            return true;
        }
        // Redimensionnement demandé
            // Utilisation de la bibliothèque ImageResize
            // Gestion des exceptions
            // Trois cas : une seule dimension, deux dimensions
            // Si une seule dimension est définie, l'autre est calculée pour respecter les proportions
            // Si les deux dimensions sont définies, l'image est redimensionnée pour s'adapter au mieux à ces dimensions

            // Tentative de redimensionnement
        try {
            $image = new ImageResize($tmpName);

            if ($this->Width > 0 && $this->Height === 0) {
                if ($image->getSourceWidth() > $this->Width) {
                    $image->resizeToWidth($this->Width);
                }
            } elseif ($this->Height > 0 && $this->Width === 0) {
                if ($image->getSourceHeight() > $this->Height) {
                    $image->resizeToHeight($this->Height);
                }
            } else {
                $image->resizeToBestFit($this->Width, $this->Height);
            }

            $image->save($cheminDestination);
            return true;
        } catch (ImageResizeException $e) {
            $this->validationMessage = "Erreur de redimensionnement : " . $e->getMessage();
            if ($throw) {
                throw new RuntimeException($this->validationMessage, 0, $e);
            }
            return false;
        }
    }
}

