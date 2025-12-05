<?php
declare(strict_types=1);

class Partenaire extends Table
{

    private const CONFIG = [
        'repertoire' => '/data/partenaire',
        'extensions' => ["jpg", "png", "webp", "avif"],
        'types' => ["image/pjpeg", "image/jpeg", "x-png", "image/png", "image/webp", "image/avif"],
        'maxSize' => 150 * 1024,
        'require' => false,
        'rename' => true,
        'sansAccent' => true,
        'redimensionner' => false,
        'height' => 150,
        'width' => 350,
        'accept' => '.jpg, .png',
        'label' => '(150 Ko max, jpg ou png)',
    ];

    public function __construct()
    {
        parent::__construct('partenaire');

        // nom
        $input = new InputText();
        $input->Require = true;
        $input->MinLength = 2;
        $this->columns['nom'] = $input;

        // url
        $input = new InputUrl();
        $input->Require = false;
        $this->columns['url'] = $input;

        // définition des colonnes modifiables en mode colonne
        $this->listOfColumns->Values = ['nom', 'url'];
    }
    private const DIR = RACINE . '/data/partenaire/';


    public static function getConfig(): array
    {
        return self::CONFIG;
    }
    public static function getAll(): array
    {
        $sql = "SELECT id, nom, url, fichier FROM partenaire ORDER BY nom";
        $select = new Select();
        return $select->getRows($sql);
    }

    public static function getById(int $id): ?array
    {
        $sql = "SELECT id, nom, url, fichier FROM partenaire WHERE id = :id";
        $select = new Select();
        return $select->getRow($sql, ['id' => $id]);
    }

    /**
     * Supprime un partenaire
     * @param int $id
     * @return bool
     */
    public static function supprimer(int $id): void
    {
        $db = Database::getInstance();
        $sql = "delete from partenaire where id = :id;";
        $cmd = $db->prepare($sql);
        $cmd->bindValue('id', $id);
        try {
            $cmd->execute();
        } catch (Exception $e) {
            Erreur::traiterReponse($e->getMessage());
        }
    }

    public static function supprimerFichier(?string $fichier): void
    {
        // si pas de nom de fichier, rien à faire
        if ($fichier === null || $fichier === '') {
            return;
        }

        $chemin = self::DIR . '/' . $fichier;
        if (is_file($chemin)) {
            unlink($chemin);
        }
    }

    public static function majLogo(int $id, string $logo): void
    {
        $sql = "update partenaire set fichier = :logo where id = :id;";
        $db = Database::getInstance();
        $cmd = $db->prepare($sql);
        $cmd->bindValue('id', $id);
        $cmd->bindValue('logo', $logo);
        try {
            $cmd->execute();
        } catch (Exception $e) {
            Erreur::traiterReponse($e->getMessage());
        }
    }


}
