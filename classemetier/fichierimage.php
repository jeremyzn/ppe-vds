<?php

class FichierImage
{
    /**
     * Configuration des fichiers image intégrée dans une information
     */
    private const CONFIG = [
        'repertoire' => '/data/photoinformation',
        'extensions' => ["jpg", "png", "webp", "avif"],
        'types' => ["image/pjpeg", "image/jpeg", "x-png", "image/png", "image/webp", "image/avif", "image/heif"],
        'maxSize' => 300 * 1024,
        'require' => true,
        'rename' => true,
        'sansAccent' => true,
        'accept' => '.jpg, .png, .webp, .avif',
        'redimensionner' => true,
        'height' => 0, // 0 pour ne pas redimensionner
        'width' => 350,
        'label' => 'Fichiers jpg, png, webp et avif acceptés (300 Ko max)',
    ];

    private const DIR = RACINE . self::CONFIG['repertoire'];

    // ------------------------------------------------------------------------------------------------
    // Méthodes concernant les opérations de consultation
    // ------------------------------------------------------------------------------------------------

    /**
     * Renvoie les paramètres de configuration des fichiers image
     * @return array<string, mixed>
     */
    public static function getConfig(): array
    {
        return self::CONFIG;
    }

    /**
     * Retourne tous les fichiers PDF contenu dans le répertoire de stockage
     * @return array
     */
    public static function getAll(): array
    {
        return Fichier::getLesFichiers(self::DIR, self::CONFIG['extensions']);
    }

    /**
     * Copie le fichier PDF transmis par $_FILES dans le répertoire de stockage
     * @return array|null
     */
    public static function ajouter(): array
    {
        // instanciation et paramétrage d'un objet InputFile : y compris le $_FILES[]
        $file = new InputFileImg(self::CONFIG);
        // vérifie la validité du fichier en teant compte des paramètres de configuration comme hauteur et largeur
        if ($file->checkValidity()) {
            // copie du fichier en prenant en compte les paramètres de configuration afin de procéder éventuellement au redimensionnement
            if ($file->copy()) {
                return [
                    'success' => true,
                    'message' => "Le fichier a été téléversé avec succès"
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Le fichier n'a pas pu être téléversé"
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => $file->getValidationMessage()
            ];
        }
    }

    /**
     * Supprime le fichier image dont le nom est transmis en paramètre
     * @param string $nomFichier
     * @return array
     */
    public static function supprimer(string $nomFichier): array
    {
        return Fichier::supprimer($nomFichier, self::DIR, self::CONFIG['extensions']);
    }

}

