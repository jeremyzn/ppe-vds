<?php
declare(strict_types=1);

/**
 * Classe InputFile : assure les opérations de téléversement d'un fichier
 * @Author : Guy Verghote
 * @Version : 2025.4
 * @Date : 07/10/2025
 */
class InputFile extends Input
{
    protected array $file;  // Le fichier (passé en paramètre, obligatoire)
    protected $valide;             // Indicateur de validité du fichier

    public string $Mode = 'insert';  // Mode de copie : 'update' ou 'insert'
    public bool $Rename = false;     // Si le fichier sera renommé automatiquement en cas de doublon
    public string $Casse = '';       // Transformation en majuscule ('U'), minuscule ('L') ou laisser tel quel
    public bool $SansAccent = true;  // Retirer accents et caractères spéciaux du nom du fichier
    public array $Extensions = [];   // Extensions acceptées
    public array $Types = [];       // Types MIME acceptés
    public int $MaxSize;            // Taille maximale autorisée
    protected $Repertoire;         // Répertoire de stockage

    /**
     * Constructeur de la classe
     *
     * @param array $lesParametres Tableau de paramètres pour configurer l'objet
     * @param array $fichier Fichier à traiter (doit être un tableau $_FILES['nomFichier'])
     */
    public function __construct( array $fichier, array $lesParametres )
    {
        parent::__construct();
        $this->valide = false;

        // Vérification que le fichier est bien un tableau et contient les bonnes clés
        if (!is_array($fichier) || !isset($fichier['name'], $fichier['tmp_name'], $fichier['error'], $fichier['size'])) {
            Erreur::traiterReponse('Le fichier doit être un tableau valide avec les clés : name, tmp_name, error, size.', 'system');
        }

         // Initialisation des paramètres
        $this->SansAccent = $lesParametres['sansAccent'] ?? true;
        $this->Casse = $lesParametres['casse'] ?? '';
        $this->Extensions = $lesParametres['extensions'] ?? ['pdf'];
        $this->MaxSize = $lesParametres['maxSize'] ?? 1024 * 1024;
        $this->Rename = $lesParametres['rename'] ?? false;
        $this->Require = $lesParametres['require'] ?? true;
        $this->Types = $lesParametres['types'] ?? ["application/force-download", "application/pdf"];
        $this->Repertoire = $_SERVER['DOCUMENT_ROOT'] . ($lesParametres['repertoire'] ?? '');
        $this->file = $fichier;  // Assignation du fichier
        $this->Value = $fichier['name'];
    }

    /**
     * Vérifie la taille, l'extension et le type mime du fichier téléversé.
     * Renseigne l'attribut name de l'objet en fonction de la propriété Rename (même nom ou avec un suffixe si déjà présent)
     *
     * @return bool
     */
    public function checkValidity(): bool
    {
        // Le fichier doit absolument être renseigné
        if (!$this->file || !isset($this->file['name']) || $this->file['error'] !== 0) {
            $this->validationMessage = 'Le fichier est invalide ou n\'a pas été téléchargé correctement.';
            return false;
        }

        // Récupération des informations sur le fichier
        $error = $this->file['error'];
        $size = $this->file['size'];
        $tmpName = $this->file['tmp_name'];

        // Vérification des erreurs de téléchargement
        if ($error !== 0) {
            $message = 'Une erreur est survenue lors du téléchargement';
            switch ($error) {
                case 1:
                    $max = ini_get('upload_max_filesize');
                    $message = "La taille du fichier téléchargé excède la valeur maximale autorisée ($max)";
                    break;
                case 2:
                    $message = 'La taille du fichier téléchargé excède la valeur maximale autorisée dans le formulaire HTML';
                    break;
                case 3:
                    $message = "Le fichier n'a été que partiellement téléchargé.";
                    break;
                case 4:
                    $message = "Aucun fichier n'a été téléchargé.";
                    break;
                case 6:
                    $message = 'Le dossier temporaire est manquant';
                    break;
                case 7:
                    $message = "Échec de l'écriture du fichier sur le disque";
                    break;
            }
            $this->validationMessage = $message;
            return false;
        }

        // Vérification de la taille du fichier
        if ($size > $this->MaxSize) {
            $this->validationMessage = "La taille du fichier ($size) dépasse la taille autorisée ($this->MaxSize)";
            return false;
        }

        // Vérification de l'extension du fichier
        $extension = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->Extensions)) {
            $this->validationMessage = "L'extension du fichier n'est pas acceptée";
            return false;
        }

        // Vérification du type MIME
        $type = mime_content_type($tmpName);
        if (!in_array($type, $this->Types)) {
            $this->validationMessage = "Le type du fichier n'est pas accepté : $type";
            return false;
        }

        // Si la propriété Value est définie, utiliser ce nom, sinon le nom original
        $nomFichier = $this->Value ?? $this->file['name'];

        // Mise en forme du nom de fichier selon la propriété Casse
        if ($this->Casse === 'U') {
            $nomFichier = strtoupper($nomFichier);
        } elseif ($this->Casse === 'L') {
            $nomFichier = strtolower($nomFichier);
        }

        // Traitement des accents et caractères spéciaux dans le nom
        if ($this->SansAccent) {
            $nomFichier = str_replace(
                array('À', 'Á', 'Â', 'ã', 'Ä', 'Å', 'Ç', 'è', 'é', 'ê', 'ë', 'è', 'é'),
                array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'E'),
                $nomFichier
            );
            $nomFichier = preg_replace("/[^a-z0-9. '_-]+/i", ' ', $nomFichier);
        }

        // Vérification de l'unicité du fichier
        if (!$this->Rename) {
            if ($this->Mode === 'insert' && is_file($this->Repertoire . '/' . $nomFichier)) {
                $this->validationMessage = "Ce fichier est déjà présent sur le serveur : $nomFichier";
                return false;
            }
        } else {
            // Renommer en cas de doublon
            $nom = pathinfo($nomFichier, PATHINFO_FILENAME);
            $i = 1;
            while (is_file("$this->Repertoire/$nomFichier")) {
                $nomFichier = "$nom($i).$extension";
                $i++;
            }
        }

        // Sauvegarde du nom de fichier validé
        $this->Value = $nomFichier;
        $this->valide = true;
        return true;
    }


    /**
     * Copie du fichier téléversé sur le serveur sous le nom contenu dans la propriété Value
     * Condition : avoir appelé la méthode checkValidity avant et avoir renseigné la propriété Directory
     * @return bool
     */
    public function copy(): bool
    {
        // le fichier ne peut être copié que s'il a été préalablement vérifié
        if (!$this->valide) {
            $this->validationMessage = " Le fichier doit être contrôlé avant d'être copié";
            return false;
        }

        $nomFichier = $this->Value;
        $tmpName = $this->file['tmp_name'];

        $ok = copy($tmpName, "$this->Repertoire/$nomFichier");
        if (!$ok) {
            $this->validationMessage = "Erreur lors de la copie du fichier vers $this->Repertoire/$nomFichier";
            return false;
        }
        return true;
    }

    /**
     * Suppression du fichier sur le serveur
     * @return bool
     */
    public function del(): bool
    {
        return @unlink($this->Repertoire . '/' . $this->Value);
    }
}
