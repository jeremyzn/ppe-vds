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
        'maxSize' => 2 * 1024 * 1024,
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
    $sql = "SELECT id, titre, contenu, type, auteur, date_creation FROM information ORDER BY date_creation DESC";
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
}
