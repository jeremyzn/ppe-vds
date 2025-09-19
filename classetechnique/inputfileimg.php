<?php
declare(strict_types=1);

/**
 * Classe InputFileImg : ajoute à la classe InputFile les opérations spécifiques sur un fichier image
 * Possibilité de définir les dimensions à respecter et de redimensionner (extraire une partie en fonction des dimensions à respecter)
 * Nécessite la bibliothèque Gumlet/ImageResize
 * @Author : Guy Verghote
 * @Version : 2025.2
 * @Date : 09/07/2025
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

    public function __construct($lesParametres)
    {
        parent::__construct($lesParametres);
        $this->Height = $lesParametres['height'] ?? 0;
        $this->Width = $lesParametres['width']?? 0;
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
        }
        // Si seule la largeur est renseignée
        elseif ($this->Width !== 0) {
            if ($width > $this->Width) {
                $this->validationMessage = "La largeur de l'image ($width) dépasse la largeur acceptée ($this->Width)";
                return false;
            }
        }
        // Si seule la hauteur est renseignée
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
    public function copy(): bool
    {
        if (!$this->valide) {
            $this->validationMessage = " Le fichier doit être contrôlé avant d'être copié";
            return false;
        }

        $nomFichier = $this->Value;
        $tmpName = $this->file['tmp_name'];

        // Cas 1 : pas de redimensionnement demandé OU aucune dimension fixée
        if (!$this->Redimensionner || ($this->Width === 0 && $this->Height === 0)) {
            return copy($tmpName, "$this->Directory/$nomFichier");
        }

        try {
            $image = new ImageResize($tmpName);

            if ($this->Width > 0 && $this->Height === 0) {
                // Seulement largeur précisée
                if ($image->getSourceWidth() > $this->Width) {
                    $image->resizeToWidth($this->Width);
                }
            } elseif ($this->Height > 0 && $this->Width === 0) {
                // Seulement hauteur précisée
                if ($image->getSourceHeight() > $this->Height) {
                    $image->resizeToHeight($this->Height);
                }
            } else {
                // Les deux dimensions sont précisées
                $image->resizeToBestFit($this->Width, $this->Height);
            }

            $image->save("$this->Directory/$nomFichier");
            return true;
        } catch (ImageResizeException $e) {
            $this->validationMessage = "Erreur de redimensionnement : " . $e->getMessage();
            return false;
        }
    }

}
