<?php
declare(strict_types=1);

/**
 * Classe InputFile : assure les opérations de téléversement d'un fichier
 * @Author : Guy Verghote
 * @Version : 2025.3
 * @Date : 22/07/2025
 * // Ajout de l'accesseur getExtension pour récupérer l'extension du fichier téléversé
 */
class InputFile extends Input
{
    // un fichier uploadé est représenté par
    //  une propriété Require indiquant si le téléversement est obligatoire
    //  un attribut File contenant le tableau $_FILES[] associé
    //     Ce tableau comprend les clés suivantes : error, tmp_name, size, name
    //  une propriété Rename permettant d'ajouter un suffixe au nom du fichier s'il est déjà présent sur le serveur
    //  un attribut name contenant le nom du fichier sur le serveur
    //  une propriété SansAccent permettant de retirer les accents et les caractères spéciaux dans le nom du fichier
    //  une propriété Casse permettant de mettre le nom du fichier en majuscule 'U', en minuscule 'L' ou le laisser tel quel

    // un attribut validateMessage contenant le message d'erreur suite à l'application de la méthode checkValidity

    // Afin de faire le contrôle, il faut en tant que propriété :
    //  un tableau des extensions acceptées
    //  un tableau des types MIME acceptés
    //  la taille maximale autorisée pour le fichier
    //  le répertoire de stockage sur le serveur

    // Enfin pour être certain que le fichier a été contrôlé un booléen valide permet de savoir si la méthode checkvalidity a été appelée avec succès

    // Tableau $_Files[] associé
    protected array | null $file;

    // Indique le mode de copie du fichier : 'update' : le fichier est remplacé s'il existe déjà, 'insert' : le fichier est ajouté s'il n'existe pas
    public string $Mode = 'insert';

    // Indique si le fichier sera automatiquement renommé (true) par l'ajout d'un suffixe si le nom existe déjà
    // ou s'il doit conserver son nom (false) et dans ce cas le téléversement sera refusé si ce nom existe déjà sur le serveur
    public bool $Rename = false;

    // Indique s'il faut mettre le nom du fichier en majuscule 'U', en minuscule 'L' ou le laisser tel quel
    public string $Casse = '';

    // Indique si les accents seront retirés et si les caractères autres que les lettres, les chiffres, le point, l'espace et le tiret doivent être remplacés par un espace
    public bool $SansAccent = true;

    // Nom et chemin du fichier temporaire sur le serveur

    // Tableau des extensions acceptées
    public array $Extensions = [];

    // tableau des types mimes acceptés (décrit de façon standard la nature et le format du fichier)
    // le type Mime est transmis dans l'entête de la réponse envoyé par le serveur
    // Le type mime est déterminé par le serveur selon sa configuration
    public array $Types = [];

    // Taille maximale autorisée pour le fichier téléversé en octets
    public int $MaxSize;

    // Répertoire sur le serveur dans lequel le fichier téléchargé sera copié
    protected $Directory;

    // Drapeau indiquant si l'objet est valide et peut donc être copié
    // membre initialisé dans le constructeur, modifiée dans la méthode checkValidity et utilisé par sécurité dans la méthode copy
    protected $valide;

    public function __construct(array $lesParametres = [])
    {
        parent::__construct();
        $this->valide = false;
        $this->SansAccent = $lesParametres['sansAccent'] ?? true;
        $this->Casse = $lesParametres['casse'] ?? '';
        $this->Extensions = $lesParametres['extensions'] ?? ['pdf'];
        $this->MaxSize = $lesParametres['maxSize'] ?? 1024 * 1024;
        $this->Rename = $lesParametres['rename'] ?? false;
        $this->Require = $lesParametres['require'] ?? true;
        $this->Types = $lesParametres['types'] ?? ["application/force-download", "application/pdf"];
        $this->Value = null;
        $inputName = $lesParametres['inputName'] ?? 'fichier';
        $this->file = $_FILES[$inputName] ?? null;
        if (defined('RACINE') && isset($lesParametres['repertoire'])) {
            $this->Directory = RACINE . $lesParametres['repertoire'];
        } elseif (isset($lesParametres['directory'])) {
            $this->Directory = $lesParametres['directory'];
        } else {
            throw new InvalidArgumentException("Répertoire de destination non défini.");
        }
    }

    public function fichierTransmis(): bool
    {
        return $this->file !== null;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getExtension(): string {
        if ($this->file === null) {
            return '';
        }
        return strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
    }

    /**
     * Vérifie la taille, l'extension et le type myme mine du fichier téléversé
     * renseigne l'attribut name de l'objet en fonction de la propriété Rename (même nom ou avec un suffixe si déjà présent)
     *
     * @return bool
     */
    public function checkValidity(): bool
    {
        // L'objet doit forcément être renseigné
        if ($this->file === null) {
            if (!$this->Require) {
                return true;
            } else {
                $this->validationMessage = 'Veuillez déposer ou sélectionner un fichier ';
                return false;
            }
        }

        // récupération des informations sur le fichier téléversé
        $error = $this->file['error'];
        $size = $this->file['size'];
        $tmpName = $this->file['tmp_name'];

        // détection d'une erreur lors de la transmission
        if ($error !== 0) {
            $message = 'Une erreur est survenue lors du téléchargement';
            if ($error === 1) {
                $max = ini_get('upload_max_filesize');
                $message = "La taille du fichier téléchargé excède la valeur maximale autorisée $max";
            } elseif ($error === 2) {
                $message = 'La taille du fichier téléchargé excède la valeur maximale autorisé dans le formulaire HTML';
            } elseif ($error === 3) {
                $message = "Le fichier n'a été que partiellement téléchargé.";
            } elseif ($error === 4) {
                $message = "Aucun fichier n'a été téléchargé.";
            } elseif ($error == 6) {
                $message = 'Le dossier temporaire est manquant';
            } elseif ($error == 7) {
                $message = "Échec de l'écriture du fichier sur le disque";
            }
            $this->validationMessage = $message;
            return false;
        }
        // vérification de la taille
        if ($size > $this->MaxSize) {
            $this->validationMessage = "La taille du fichier ($size) dépasse la taille autorisée ($this->MaxSize)";
            return false;
        }

        // vérification de l'extension
        $extension = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->Extensions)) {
            $this->validationMessage = "L'extension du fichier n'est pas acceptée";
            return false;
        }
        // contrôle du type mime du fichier
        $type = mime_content_type($tmpName);

        if (!in_array($type, $this->Types)) {
            $this->validationMessage = "Le type du fichier n'est pas accepté : $type";
            return false;
        }

        // Si la propriété Value (nom à donner au fichier) n'est pas renseignée, elle prend la valeur du nom du fichier téléversé
        if ($this->Value === null || $this->Value === '') {
            $nomFichier = $this->file['name'];
        } else {
            // c'est l'application qui a défini le nom que devra porter le fichier sur le serveur (ex : nom contenant un horodatage)
            // dans ce cas la propriété SansAccent doit forcément être égale à faux ainsi que la propriété Rename (le fichier ne peut pas être renommé pour garantir son unicité sur le serveur)
            $this->SansAccent = false;
            $this->Rename = false;
            $nomFichier = $this->Value;
        }

        // mise en forme demandée
        if ($this->Casse === 'U') {
            $nomFichier = strtoupper($nomFichier);
        } elseif ($this->Casse === 'L') {
            $nomFichier = strtolower($nomFichier);
        }

        // si on demande de retirer accents et autres caractères à problème
        if ($this->SansAccent) {

            // Remplacement des caractères accentués par les lettres non accentuées correspondantes
            $nomFichier = str_replace(
                array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ'),
                array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y'),
                $nomFichier
            );

            // remplacement par des espaces des caractères non alphabétiques
            $nomFichier = preg_replace("/([^a-z0-9. '_-]+)/i", ' ', $nomFichier);
            // mise en minuscule
            // $nomFichier = strtolower($nomFichier);
        }

        // contrôle de l'unicité
        // Si la propriété Rename est fausse le fichier ne doit pas déjà être présent
        // Si la propriété Rename est vraie un suffixe sera ajouté en cas de doublon

        if ($this->Rename) {
            // Ajout éventuel d'un suffixe sur le nom du fichier en cas de doublon
            $nom = pathinfo($nomFichier, PATHINFO_FILENAME);
            $i = 1;
            while (is_file("$this->Directory/$nomFichier")) {
                $nomFichier = "$nom($i).$extension";
                $i++;
            }
        } else {
            //  le fichier ne doit pas déjà être présent dans le répertoire
            if ($this->Mode === 'insert' && is_file($this->Directory . '/' . $nomFichier)) {
                $this->validationMessage = "Ce fichier est déjà présent sur le serveur : $nomFichier";
                return false;
            }

        }

        // mémorisation du nouveau nom
        $this->Value = $nomFichier;
        // mémorisation du succès de l'opération de contrôle
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

        copy($tmpName, "$this->Directory/$nomFichier");
        return true;
    }

    /**
     * Suppression du fichier sur le serveur
     * @return bool
     */
    public function del(): bool
    {
        return @unlink($this->Directory . '/' . $this->Value);
    }
}
