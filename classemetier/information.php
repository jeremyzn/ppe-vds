<?php
declare(strict_types=1);

/**
 * Classe métier Information - gère la table `information` et fournit les opérations
 * conformes au pattern des autres classes métier (extends Table)
 */
class Information extends Table
{
    private const CONFIG = [
        'repertoire' => '/data/documentinformation',
        'extensions' => ['pdf'],
        'types' => ['application/pdf'],
        'maxSize' => 5 * 1024 * 1024,
        'require' => false,
        'rename' => true,
        'sansAccent' => true,
    ];

    public const DIR = RACINE . self::CONFIG['repertoire'];

    public function __construct()
    {
        parent::__construct('information');

        // titre
        $input = new inputText();
        $input->Require = true;
        $input->MaxLength = 255;
        $this->columns['titre'] = $input;

        // (chapeau retiré - champ non requis par le cahier des charges)

        // contenu (rich text)
        $input = new InputTextarea();
        $input->Require = true;
        $input->AcceptHtml = true;
        $this->columns['contenu'] = $input;

        // type
        $input = new InputList();
        $input->Require = true;
        $input->Value = "Publique";
        // valeurs acceptées (doivent correspondre aux options du select)
        $input->Values = ["Publique", "Privée"];
        $this->columns['type'] = $input;

        // auteur
        $input = new InputText();
        $input->Require = false;
        $this->columns['auteur'] = $input;
    }

    /**
     * Renvoie la configuration pour l'upload des documents
     * @return array
     */
    public static function getConfig(): array
    {
        return self::CONFIG;
    }

    /**
     * Renvoie la liste des informations pour affichage (backoffice)
     * @return array
     */
    public static function getAll(): array
    {
        // inclure le contenu pour permettre le préremplissage du formulaire d'édition en backoffice
        $sql = "SELECT id, titre, contenu, type, auteur, date_creation, date_modif FROM information ORDER BY date_creation DESC";
        $select = new Select();
        return $select->getRows($sql);
    }

    /**
     * Récupère une information complète par id
     * @param int $id
     * @return array|false
     */
    public static function getById(int $id)
    {
        $sql = "SELECT * FROM information WHERE id = :id";
        $select = new Select();
        return $select->getRow($sql, ['id' => $id]);
    }

    /**
     * Récupère les documents associés à une information
     * @param int $idInformation
     * @return array
     */
    public static function getDocuments(int $idInformation): array
    {
        $sql = "SELECT id, fichier, nom_original, date_upload FROM documentinformation WHERE idInformation = :idInformation";
        $select = new Select();
        return $select->getRows($sql, ['idInformation' => $idInformation]);
    }

    /**
     * Supprime une information et tous les documents associés (fichiers physiques + enregistrements)
     * @param int|string $id
     */
    public function delete(int|string $id): void
    {
        $db = Database::getInstance();

        // Récupérer les documents associés
        $stmt = $db->prepare('SELECT id, fichier FROM documentinformation WHERE idInformation = :idInformation');
        $stmt->execute(['idInformation' => $id]);
        $docs = $stmt->fetchAll();

        // Supprimer les fichiers physiques et leurs enregistrements
        foreach ($docs as $doc) {
            $path = self::DIR . '/' . $doc['fichier'];
            if (is_file($path)) {
                @unlink($path);
            }
            $del = $db->prepare('DELETE FROM documentinformation WHERE id = :id');
            $del->execute(['id' => $doc['id']]);
        }

        // Supprimer l'enregistrement d'information
        parent::delete($id);
    }

    // =========================================================================
    // MÉTHODES POUR LA GESTION DES DOCUMENTS
    // =========================================================================

    /**
     * Récupère un document par son id
     * @param int $id
     * @return array|false
     */
    public static function getDocumentById(int $id)
    {
        $sql = "SELECT id, fichier, nom_original, idInformation FROM documentinformation WHERE id = :id";
        $select = new Select();
        return $select->getRow($sql, ['id' => $id]);
    }

    /**
     * Récupère le type d'une information (Publique/Privée)
     * @param int $id
     * @return array|false
     */
    public static function getType(int $id)
    {
        $sql = "SELECT type FROM information WHERE id = :id";
        $select = new Select();
        return $select->getRow($sql, ['id' => $id]);
    }

    /**
     * Supprime l'enregistrement d'un document en base de données
     * @param int $id
     */
    public static function supprimerEnregistrement(int $id): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM documentinformation WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /**
     * Supprime un document (fichier physique + enregistrement)
     * @param int $id
     * @return bool true si le document a été supprimé, false si non trouvé
     */
    public static function supprimerDocument(int $id): bool
    {
        $doc = self::getDocumentById($id);
        if (!$doc) {
            return false;
        }

        // Suppression du fichier physique
        $path = self::DIR . '/' . $doc['fichier'];
        if (is_file($path)) {
            @unlink($path);
        }

        // Suppression en base de données
        self::supprimerEnregistrement($id);
        return true;
    }

    /**
     * Ajoute un document pour une information
     * @param string $fichier Nom du fichier stocké
     * @param int $idInformation ID de l'information
     * @param string $nomOriginal Nom original du fichier
     * @return int ID du document créé
     */
    public static function ajouterDocument(string $fichier, int $idInformation, string $nomOriginal): int
    {
        $db = Database::getInstance();
        $sql = "INSERT INTO documentinformation (fichier, idInformation, nom_original) VALUES (:fichier, :idInformation, :nom_original)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'fichier' => $fichier,
            'idInformation' => $idInformation,
            'nom_original' => $nomOriginal
        ]);
        return (int) $db->lastInsertId();
    }
}
