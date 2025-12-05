<?php

/**
 * Classe Document
 *
 * Gère les enregistrements de documents PDF dans la table `document`, avec les champs `id`, `titre` et `fichier`.
 * - Imposе la présence d’un fichier PDF téléversé à la création d’un document.
 * - Valide le titre via des contraintes (longueur, pattern, nettoyage d’espaces).
 * - Configure les règles pour le fichier (répertoire, format, taille, type MIME, etc.).
 * - Fournit un constructeur pour initialiser la structure des colonnes.
 * - Offre deux méthodes statiques :
 *   • getConfig() : retourne la configuration de téléversement des fichiers PDF.
 *   • getAll() : récupère tous les documents, triés par titre, et ajoute un indicateur `present`
 *              précisant si le fichier PDF existe bien sur le disque.
 */


class Document extends Table
{
    /**
     * Configuration des fichiers pdf associés aux enregistrements
     */
    private const CONFIG = [
        'repertoire' => '/data/document',
        'extensions' => ['pdf'],
        'types' => ["application/pdf"],
        'maxSize' => 1024 * 1024,
        'require' => true,
        'rename' => false,
        'sansAccent' => false,
        'accept' => '.pdf',
        'label' => 'Fichier PDF (1 Mo max)'
    ];

    private const DIR = RACINE . self::CONFIG['repertoire'];

    /**
     * Constructeur de la classe Document
     * Initialise les colonnes de la table document
     */
    public function __construct()
    {
        // appel du contructeur de la classe parent


        // le titre du document doit être renseigné
        // commencer par une lettre ou un chiffre
        // se terminer par une lettre ou un chiffre ou ! . ?
        // contenir entre 10 et 70 caractères
        $input = new InputText();
        $input->Pattern = "^[a-zA-ZÀ-ÿçÇ0-9]([ '\-]?[a-zA-ZÀ-ÿçÇ0-9]*)*$";
        $input->MinLength = 10;
        $input->MaxLength = 100;
        $input->SupprimerEspaceSuperflu = true;
        $this->columns['titre'] = $input;

        // nom du fichier pdf
        $input = new InputText();
        $input->Require = false; // le fichier est obligatoire
        $this->columns['fichier'] = $input;

        //liste des types de fichiers
        $input = new InputList();
        $input->Require = false;
        $input->Values = ['C', 'S', 'M', 'P'];
        $this->columns['type'] = $input;

        // Définition des colonnes pouvant être modifiée unitairement
        $this->listOfColumns->Values = ['titre',];
    }


    // ------------------------------------------------------------------------------------------------
    // Méthodes concernant les opérations de consultation
    // ------------------------------------------------------------------------------------------------

    /**
     * Renvoie la configuration du logo des partenaires
     * @return array<string, mixed>
     */
    public static function getConfig(): array
    {
        return self::CONFIG;
    }

    /**
     * Retourne tous les enregistrements de la table document
     * @return array
     */
    public static function getAll(): array
    {
        $sql = "Select id, titre,type, fichier  from document order by titre;";
        $select = new Select();
        $lesLignes = $select->getRows($sql);
        // ajout d'une colonne permettant de vérifier l'existence du logo
        foreach ($lesLignes as &$ligne) {
            $chemin = self::DIR . '/' . $ligne['fichier'];
            $ligne['present'] = is_file($chemin) ? 1 : 0;
        }
        return $lesLignes;
    }

    public static function getVisible(): array|false
    {
        $select = new Select();
        if (isset($_SESSION['membre'])) {
            // utilisateur connecté : tous les documents
            $sql = "SELECT id, titre, type, fichier FROM document ORDER BY titre;";
        } else {
            // non connecté : exclure les documents réservés aux membres
            $sql = "SELECT id, titre, type, fichier FROM document WHERE type not in ('Membre')  ORDER BY titre;";
        }
        $lesLignes = $select->getRows($sql);
        foreach ($lesLignes as &$ligne) {
            $chemin = self::DIR . '/' . $ligne['fichier'];
            $ligne['present'] = is_file($chemin) ? 1 : 0;
        }
        return $lesLignes;
    }

    /**
     * Récupère les informations d’un document par son ID
     *
     * @param int $id
     * @return array{id: int, type: string, fichier: string, titre: string}|null
     */
    public static function getById(int $id): array|false
    {
        $sql = <<<SQL
         select id, fichier, titre from document where id = :id;
        SQL;
        $select = new Select();
        return $select->getRow($sql, ['id' => $id]);
    }

    // ------------------------------------------------------------------------------------------------


    // Récupère un objet PDO ; adapte selon votre projet

    public static function supprimer(int $id): bool
    {
        $pdo = self::pdo();
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('DELETE FROM documents WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $deleted = ($stmt->rowCount() > 0);
            if ($deleted) {
                $pdo->commit();
                return true;
            } else {
                $pdo->rollBack();
                return false;
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }
    }

    protected static function pdo(): PDO
    {
        if (class_exists('Database') && method_exists('Database', 'getInstance')) {
            return Database::getInstance();
        }
        if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
            return $GLOBALS['pdo'];
        }
        throw new RuntimeException('PDO non disponible');
    }

    public static function supprimerFichier(?string $fichier): bool
    {
        if (empty($fichier)) {
            return false;
        }

        // chemins candidates (adapter si nécessaire)
        $candidates = [
            $fichier,
            __DIR__ . '/../../uploads/' . $fichier,
            __DIR__ . '/../../../uploads/documents/' . $fichier,
            __DIR__ . '/../../../public/uploads/' . $fichier,
        ];

        if (defined('DOCUMENT_UPLOAD_DIR')) {
            $candidates[] = rtrim(DOCUMENT_UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fichier;
        }

        foreach ($candidates as $path) {
            if (file_exists($path) && is_file($path)) {
                try {
                    return unlink($path);
                } catch (Throwable $e) {
                    return false;
                }
            }
        }

        return false;
    }
}