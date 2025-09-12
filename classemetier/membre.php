<?php
declare(strict_types=1);

// définition de la table membre : id, login, password, nom, prenom, email, telephone, photo, autMail

class Membre extends Table
{

    /**
     * Configuration intégrée pour l'upload des photos
     */
    private const CONFIG = [
        'repertoire' => '/data/photomembre',
        'extensions' => ["jpg", "png"],
        'types' => ["image/pjpeg", "image/jpeg", "x-png", "image/png"],
        'maxSize' => 200 * 1024,
        'require' => false,
        'rename' => true,
        'sansAccent' => true,
        'redimensionner' => true,
        'height' => 200,
        'width' => 200,
        'accept' => '.jpg, .png',
    ];

    public const DIR = RACINE . self::CONFIG['repertoire'];

    public function __construct()
    {
        parent::__construct('membre');

        // seuls les colonnes pouvant être modifiées par l'administrateur sont définies

        // nom
        $input = new inputText();
        $input->Require = true;
        $input->Casse = 'U';
        $input->SupprimerAccent = true;
        $input->SupprimerEspaceSuperflu = true;
        $input->Pattern = "^[A-Z]( ?[A-Z]+)*$";
        $input->MaxLength = 30;
        $this->columns['nom'] = $input;

        // prenom
        // prenom
        $input = new inputText();
        $input->Require = true;
        $input->Casse = 'U';
        $input->SupprimerAccent = true;
        $input->SupprimerEspaceSuperflu = true;
        $input->Pattern = "^[A-Z]( ?[A-Z]+)*$";
        $input->MaxLength = 30;
        $this->columns['prenom'] = $input;

        //  email
        $input = new inputEmail();
        $input->Require = true;
        $input->MaxLength = 100;
        $this->columns['email'] = $input;

        // fichier photo
        $input = new InputFile(self::CONFIG);
        $input->Require = false;
        $this->columns['photo'] = $input;
    }

    // ------------------------------------------------------------------------------------------------
    // Méthodes statiques relatives aux opérations de consultation
    // ------------------------------------------------------------------------------------------------

    /**
     * Renvoie la configuration du logo des partenaires
     * @return array<string, mixed>
     */
    public static function getConfig(): array
    {
        return self::CONFIG;
    }

    public static function getAll(): array
    {
        // Récupération des paramètres du téléversement
        $sql = <<<SQL
        Select id, concat(nom, ' ' , prenom) as nomPrenom, nom, prenom, email, 
               ifnull(photo, 'Non renseignée') as photo,  login,
               if(autMail, 'Oui', 'Non') as afficherMail,  
               ifnull(telephone, 'Non renseigné') as telephone
        From membre
        Order by nom, prenom;
SQL;
        $select = new Select();
        return $select->getRows($sql);
    }

    /**
     * Récupère les membres pour affichage dans l'annuaire
     * @return array
     */
    public static function getLesMembres(): array
    {
        $sql = <<<SQL
            Select nom, prenom,  
            if(autMail, email, 'Non communiqué') as mail,  
            ifnull(telephone, 'Non renseigné') as telephone,
            ifnull(photo, 'Non renseignée') as photo 
            From membre
            order by nom, prenom;
SQL;
        $select = new Select();
        $lesLignes = $select->getRows($sql);

        // Vérification de l'existence physique du fichier photo
        foreach ($lesLignes as &$ligne) {
            $file = self::DIR  . '/' . $ligne['photo'];

            // Si la photo est renseignée et existe sur le disque
            if ($ligne['photo'] !== 'Non renseignée' && is_file($file)) {
                $ligne['photo'] = self::CONFIG['repertoire'] . '/' . $ligne['photo'];
            } else {
                $ligne['photo'] = 'Non trouvée';
            }
        }

        return $lesLignes;
    }

     /**
     * Récupère les données d'un membre à partir de son login
     * @param string $login
     * @return array|false
     */
    public static function getByLogin(string $login): array|false
    {
        $sql = <<<SQL
            Select id, login, email, nom, prenom
            from membre 
            where login = :login;
SQL;
        $select = new Select();
        return $select->getRow($sql, ['login' => $login]);
    }

    /**
     * Récupère les données d'un membre à partir de son id
     * @param int $id
     * @return array|false
     */
    public static function getById($id)
    {
        $sql = <<<SQL
             Select id, nom, prenom, email, login, telephone, autMail
             From membre
             where id = :id;
SQL;
        $select = new Select();
        return $select->getRow($sql, ['id' => $id]);
    }

    /**
     *  Récupère la photo d'un utilisateur
     * @param int $id
     * @return
     */
    public static function getPhoto($id)
    {
        // récupération des informations sur l'étudiant
        $sql = <<<SQL
            select id, photo
            from membre
            where id = :id
           
SQL;
        $select = new Select();
        $ligne = $select->getRow($sql, ['id' => $id]);

        // Ajout de la colonne 'present' selon la présence d'une photo existante sur le disque
        if (!$ligne) {
            Erreur::traiterReponse("Ce membre n'existe pas", "global");
        }
        $ligne['present'] = !empty($ligne['photo']) && is_file(self::DIR . '/' . $ligne['photo']) ? 1 : 0;
        return $ligne;
    }


    /**
     *  Modifie la photo d'un utilisateur
     * @param int $id
     * @param string $nomFichier
     * @return void
     */
    public static function modifierPhoto(int $id, string $nomFichier): void
    {
        try {
            $sql = <<<SQL
            update membre 
            set photo = :nomFichier
            where id = :id
SQL;
            $db = Database::getInstance();
            $curseur = $db->prepare($sql);
            $curseur->bindValue('id', $id);
            $curseur->bindValue('nomFichier', $nomFichier);
            $curseur->execute();
        } catch (Exception $e) {
            Erreur::traiterReponse($e->getMessage());
        }
    }

    /**
     *  Supprime la photo d'un utilisateur
     *  Condition :
     * @param int $id
     * @return void
     */
    public static function supprimerPhoto($id)
    {
        // vérifier l'existence de l'utilisateur
        $ligne = self::getPhoto($id);

        // vérifier que l'utilisateur a une photo
        if (empty($ligne['photo'])) {
            Erreur::traiterReponse("L'utilisateur $id n'a pas de photo à supprimer", 'global');
        }

        // suppression de la photo dans la base de données
        $sql = <<<SQL
            update membre 
            set photo = null
            where id = :id; 
SQL;
        $db = Database::getInstance();
        $curseur = $db->prepare($sql);
        $curseur->bindValue('id', $id);
        try {
            $curseur->execute();
            // Si la photo est présente dans le répertoire il faut la supprimer
            if ($ligne['present']) {
                @unlink(self::DIR . '/' . $ligne['photo']);
            }
        } catch (Exception $e) {
            Erreur::traiterReponse($e->getMessage());
        }
    }

    // ------------------------------------------------------------------------------------------------
    // Méthodes relatives aux opérations liées à la connexion
    // ------------------------------------------------------------------------------------------------

    /**
     *  Vérifie si un password est correct
     * @param int $id
     * @param string $password
     * @return bool
     */
    public static function verifierPassword(int $id, string $password): bool
    {
        $sql = <<<SQL
                 Select password from membre 
                 where id = :id;
SQL;
        $select = new Select();
        $ligne = $select->getRow($sql, ['id' => $id]);
        return $ligne && $ligne['password'] === hash('sha256', $password);
    }

    /**
     * Mémorise la connexion d'un utilisateur
     * @param array $membre
     * @return void
     */
    public static function connexion(array $membre): void
    {
        // mémorisation de la connexion dans une variable de session
        $_SESSION['membre'] = $membre;
    }

    /**
     * Déconnexion de l'utilisateur
     * @return void
     */
    public static function deconnexion() {
        // supprimer la variable de session membre
        unset($_SESSION['membre']);
    }

}