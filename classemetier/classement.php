<?php

/**
 * Classe gérant les classements déposés par l'administrateur
 * Gestion de la table 'classement' avec les colonnes :
 *  - id : identifiant unique
 *  - date : date du classement
 *  - titre : titre du classement
 *  - fichier : nom du fichier PDF associé
 *  - nbDemande : compteur du nombre de téléchargements/demandes
 *
 * Les fichiers PDF sont stockés dans un répertoire défini par la configuration.
 *
 * Cette classe centralise également les paramètres de configuration liés au stockage
 * et à la validation des fichiers, permettant une utilisation côté serveur et côté client.
 */
class Classement extends Table
{
    /**
     * Configuration centralisée des paramètres liés à la gestion des fichiers
     * et aux contraintes de validation des colonnes.
     *
     * Cette configuration peut être récupérée par getConfig() pour être envoyée au client
     * (ex: validation JS identique à la validation PHP).
     */
    private const CONFIG = [
        // Répertoire relatif de stockage des fichiers PDF, utilisé côté serveur et client
        'repertoire' => '/data/classement',
        // Extensions autorisées pour les fichiers téléversés
        'extensions' => ['pdf'],
        // Types MIME acceptés pour l'upload
        'types' => ['application/force-download', 'application/pdf'],
        // Taille maximale autorisée (en octets)
        'maxSize' => 2000 * 1024,
        // Le fichier est-il obligatoire ?
        'require' => true,
        // Indique si le fichier doit être renommé en cas de doublon
        'rename' => false,
        // Supprimer les accents dans le nom de fichier si true
        'sansAccent' => true,
        // Forcer la casse des noms de fichiers ('L' = minuscule, 'U' = majuscule)
        'casse' => 'L',
        // Extensions acceptées dans les champs input HTML (ex: accept=".pdf")
        'accept' => '.pdf',
        // Label pour l'input de fichier, utilisé dans les formulaires
        'label' => 'Fichier PDF (2 Mo max)',
    ];

    private const DIR = RACINE . self::CONFIG['repertoire'];

    /**
     * Constructeur de la classe
     * Initialise la structure de la table et définit les règles de validation
     * des colonnes via des objets Input* dédiés.
     */
    public function __construct()
    {
        parent::__construct('classement');

        // Validation de la colonne date : obligatoire, max = aujourd'hui
        $input = new InputDate();
        $input->Require = true;
        $input->Max = date('Y-m-d');
        $this->columns['date'] = $input;

        // Validation de la colonne titre : texte obligatoire,
        // pattern autorisant lettres, chiffres, accents, apostrophes, espaces, tirets
        // longueur entre 10 et 100 caractères, suppression des espaces superflus
        $input = new InputText();
        $input->Pattern = "^[0-9A-Za-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ](?:[,' \\-]?[0-9A-Za-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ])*$";
        $input->MinLength = 10;
        $input->MaxLength = 100;
        $input->SupprimerEspaceSuperflu = true;
        $this->columns['titre'] = $input;

        // Validation du fichier uploadé
        // Utilise les paramètres définis dans self::CONFIG pour la configuration
        $input = new InputFile(self::CONFIG);
        // Construction du chemin absolu pour le stockage des fichiers
        $this->columns['fichier'] = $input;

        // Colonnes pouvant être modifiées unitairement (ex: via interface AJAX)
        $this->listOfColumns->Values = ['titre', 'date'];
    }

    /**
     * Récupère tous les classements en base, triés par date décroissante.
     * Ajoute une colonne 'present' indiquant si le fichier physique existe sur le disque.
     *
     * @return array<int, array{
     *     id: int,
     *     titre: string,
     *     date: string,
     *     dateFr: string,
     *     fichier: string,
     *     nbDemande: int,
     *     present: int
     * }>
     */
    public static function getAll(): array
    {
        $sql = <<<SQL
            SELECT id, titre, date, DATE_FORMAT(date, '%d/%m/%Y') AS dateFr, fichier, nbDemande
            FROM classement
            ORDER BY date DESC;
SQL;

        $select = new Select();
        $lesLignes = $select->getRows($sql);

        // Ajout du champ 'present' selon l'existence du fichier sur le disque
        foreach ($lesLignes as &$ligne) {
            $cheminFichier = RACINE . self::CONFIG['repertoire'] . '/' . $ligne['fichier'];
            $ligne['present'] = is_file($cheminFichier) ? 1 : 0;
        }
        unset($ligne); // éviter effet de bord sur la variable par référence

        return $lesLignes;
    }

    /**
     * Récupère les classements publiés dans la dernière semaine
     *
     * @return array<int, array{id: int, titre: string, dateFr: string}>
     */
    public static function getLast(): array
    {
        $sql = <<<SQL
            select id, titre, date_format(date, '%d/%m/%y') as datefr
            from classement
            where date between curdate() - interval 2 week and curdate()
            order by date desc;
SQL;
        $select = new Select();
        return $select->getRows($sql);
    }

    /**
     * Récupère un classement par son identifiant
     *
     * @param int $id Identifiant du classement
     * @return array|null Données du classement ou null si non trouvé
     */
    public static function getById(int $id): ?array
    {
        $sql = <<<SQL
            select id, date, date_format(date, '%d/%m/%y') as datefr, fichier, titre
            from classement
            where id = :id;
SQL;
        $select = new Select();
        return $select->getRow($sql, ['id' => $id]);
    }

    /**
     * Incrémente le compteur de demandes pour un classement donné
     *
     * @param int $id Identifiant du classement
     * @return void
     */
    public static function comptabiliserDemande(int $id): void
    {
        $sql = <<<SQL
            update classement
            set nbDemande = nbDemande + 1
            where id = :id;
SQL;
        $db = Database::getInstance();
        $curseur = $db->prepare($sql);
        $curseur->bindParam('id', $id);
        $curseur->execute();
    }

    /**
     * Récupère la liste des fichiers avec leur nombre de demandes (statistiques)
     *
     * @return array<int, array{nom: string, nb: int}>
     */
    public static function getNbDemande(): array
    {
        $sql = <<<SQL
            select fichier as nom, nbDemande as nb
            from classement
            order by date desc;
SQL;
        $select = new Select();
        return $select->getRows($sql);
    }

    /**
     * Récupère la configuration (utile notamment côté client pour la validation)
     *
     * @return array La configuration des paramètres
     */
    public static function getConfig(): array
    {
        return self::CONFIG;
    }
}
