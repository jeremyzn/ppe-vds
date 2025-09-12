<?php
declare(strict_types=1);

/**
 * Classe Table : représente une table SQL
 * Cette classe est une classe abstraite donc non instanciable.
 * Elle met en facteur tous les attributs et toutes les méthodes communes aux classes dérivées
 * @Author : Guy Verghote
 * @Version : 2025.4
 * @Date : 07/07/2025
 */
abstract class Table
{
    // objet PDO pour réaliser l'ensemble des requêtes sur la table $tableName de la base de donnée $database
    private PDO $db;

    // nom de la table : sera défini dans la classe dérivée
    private string $tableName;

    // nom de la colonne de clé primaire par défaut id (sera défini dans la classe dérivée si la clé primaire n'est pas id)
    protected string $idName = 'id';

    // Colonnes composant la structure de la table (à l'exception de l'identifiant)
    // Tableau associatif clé : nom de la colonne, valeur : un objet Input (contient la valeur et les règles de validations)
    // sera défini dans la classe dérivée
    protected array $columns;

    // Objet InputList contenant les colonnes modifiables en mode colonne
    // sera défini dans la classe dérivée
    protected InputList $listOfColumns;

    //  tableau d'objet Erreur alimenté par les méthodes de gestion
    private array $lesErreurs = [];

    // propriété statique contenant la valeur de l'identifiant de la dernière ligne insérée lorsque l'identifiant est un champ auto-incrémenté
    // alimenté par la méthode lastInsertId de la classe PDO qui retourne une chaîne ou faux
    private bool|string $lastInsertId = false;

    /**
     * Constructeur
     * @param string $nomTable nom de la table gérée
     */
    protected function __construct(string $nomTable)
    {
        $this->tableName = $nomTable;
        $this->columns = [];
        $this->listOfColumns = new InputList();
        $this->db = Database::getInstance();
    }

    /*
    -------------------------------------------------------------------------------------------------------------------
    Les accesseurs sur les attributs des objets
    --------------------------------------------------------------------------------------------------------------------
    */

    /**
     * Retourne l'objet Input associé à la clé $colonne du tableau columns
     * @param string $colonne Nom de la colonne de la table
     * @return Input
     */
    public function getColonne(string $colonne): Input
    {
        return $this->columns[$colonne];
    }

    /**
     * Retourne le tableau des erreurs
     * @return array
     */
    public function getLesErreurs() : array
    {
        return $this->lesErreurs;
    }

    // accesseur en lecture sur la dernière valeur générée d'un champ auto-increment

    /**
     * Retourne la valeur de l'identifiant de la dernière ligne insérée
     * @return bool|string
     */
    public function getLastInsertId() : bool|string
    {
        return $this->lastInsertId;
    }

    /*
   -------------------------------------------------------------------------------------------------------------------
   Les méthodes privées permettant la génération dynamique des requêtes insert et update
   --------------------------------------------------------------------------------------------------------------------
   */

    /**
     * Alimente les paramètres de la requête et l'exécute
     * En cas d'erreur, la méthode est interrompue et le message d'erreur est envoyé
     * @param string $sql Requête SQL paramétrée de type insert ou update à exécuter
     */
    private function prepareAndExecute(string $sql) : void
    {
        try {
            $curseur = $this->db->prepare($sql);
            // alimentation des paramètres de la requête
            foreach ($this->columns as $cle => $input) {
                if ($input->Value === null) {
                    continue;
                }
                $curseur->bindValue($cle, $input->Value);
            }
            $curseur->execute();
        } catch (Exception $e) {
            Erreur::traiterReponse($sql . " : " . $e->getMessage());
        }
    }

    /*
    -------------------------------------------------------------------------------------------------------------------
    Les méthodes publiques sur les objets : utilisable dans les classes dérivées
    --------------------------------------------------------------------------------------------------------------------
    */

    /**
     * Vérifie que toutes les données à transmettre sont bien transmises
     * Les erreurs sont conservées dans le tableau des erreurs
     * Alimente la valeur des objets Input composant la table à partir des données transmises dans le tableau $_POST
     * Contrôle que tous les objets Input obligatoires ont bien une valeur
     * @return bool
     */
    public function donneesTransmises(): bool
    {
        // Alimente les objets Input à l'aide du tableau $_POST
        foreach ($_POST as $cle => $valeur) {
            $valeur = trim($valeur);
            if ($valeur !== '' && isset($this->columns[$cle])) {
                $this->columns[$cle]->Value = $valeur;
            }
        }

        // Vérification que toutes les colonnes (input) obligatoires sont bien renseignées
        $ok = true;
        foreach ($this->columns as $cle => $input) {
            // s'il s'agit d'un fichier, on vérifie qu'il a bien été transmis
            if ($input instanceof inputfile) {
                if ($input->Require && !$input->fichierTransmis()) {
                    $this->lesErreurs[$cle] = "Veuillez renseigner ce champ.";
                    $ok = false;
                }
                // s'il s'agit d'un champ de type input, on vérifie qu'il a bien été renseigné s'il est obligatoire
            } elseif ($input->Require && $input->Value === null) {
                $this->lesErreurs[$cle] = "Veuillez renseigner ce champ.";
                $ok = false;
            }
        }
        return $ok;
    }

    /**
     * Contrôle la valeur attribuée à chaque colonne à partir des règles de validation associées à chaque colonne
     * Les erreurs sont conservées dans le tableau des erreurs
     * @return bool
     */
    public function checkAll(): bool
    {
        // Parcourt chaque objet Input et appelle sa méthode checkValidity pour vérifier la conformité de sa valeur
        // en cas de non-conformité, le message d'erreur est conservé dans le tableau des erreurs
        $correct = true;
        foreach ($this->columns as $cle => $input) {
            if (!$input->checkValidity()) {
                $this->lesErreurs[$cle] = $input->getValidationMessage();
                $correct = false;
            }
        }
        return $correct;
    }

    /**
     * Ajoute un enregistrement dans une table et éventuellement le fichier associé
     */
    public function insert() : void
    {
        // génération de la requête insert
        $set = "";
        foreach ($this->columns as $cle => $input) {
            // une colonne dont la valeur de l'objet input associé est renseignée doit recevoir cette valeur
            if ($input->Value !== null) {
                $set .= "$cle = :$cle, ";
                // une colonne acceptant la valeur null reçoit cette valeur si l'objet input associé n'a pas été renseigné
            }
        }
        // suppression de la dernière virgule
        $set = substr($set, 0, -2);

        $sql = "insert into $this->tableName set $set";

        $this->prepareAndExecute($sql);

        // si on trouve une colonne fichier de type InputFile, un fichier est transmis, il est copié
        if (isset($this->columns['fichier']) && $this->columns['fichier'] instanceof inputfile) {
            $this->columns['fichier']->copy();
        }
        // on renseigne la valeur de l'attribut lastInsertID au cas où (on ne peut pas savoir si l'identifiant est un compteur)
        $this->lastInsertId = $this->db->lastInsertId();
    }

    /**
     * Supprime l'enregistrement dont la valeur de la clé primaire est passé en paramètre
     * Si l'enregistrement est associé à un fichier le fichier sera aussi supprimé
     * La valeur de la clé primaire est préalablement contrôlée
     * En cas d'erreur la méthode est interrompue et un message d'erreur est renvoyé
     * @param int|string $id valeur de la clé primaire (entier ou chaine de caractères)
     */
    public function delete(int|string $id) : void
    {
        try {
            // vérification de l'id et récupération éventuelle du nom du fichier associé à l'enregistrement
            if (isset($this->columns['fichier'])) {
                $sql = <<<SQL
                select fichier from  $this->tableName
                where $this->idName = :id;
SQL;
            } else {
                $sql = <<<SQL
                Select 1 from  $this->tableName
                where $this->idName = :id;
SQL;
            }
            $curseur = $this->db->prepare($sql);
            $curseur->bindValue('id', $id);
            $curseur->execute();
            $ligne = $curseur->fetch();
            $curseur->closeCursor();
            if (!$ligne) {
                Erreur::traiterReponse("Enregistrement inexistant.", 'global');
            }

            // suppression
            $sql = <<<SQL
            delete from  $this->tableName
            where $this->idName = :id;
SQL;

            $curseur = $this->db->prepare($sql);
            $curseur->bindValue('id', $id);

            $curseur->execute();

            // si une colonne fichier existe et qu'elle est associée à un objet InputFile, il faut supprimer ce fichier
            if (isset($ligne['fichier']) && $this->columns['fichier'] instanceof inputfile) {
                $this->columns['fichier']->Value = $ligne['fichier'];
                $this->columns['fichier']->del();
            }

        } catch (Exception $e) {
            Erreur::traiterReponse($e->getMessage());
        }
    }

    /**
     * Vérifie l'existence d'un enregistrement dans une table
     * @param $id
     * @return bool
     */
    protected function existe($id): bool
    {
        $sql = <<<SQL
                Select 1 
                from  $this->tableName
                where $this->idName = :id;
SQL;
        $curseur = $this->db->prepare($sql);
        $curseur->bindValue('id', $id);
        $curseur->execute();
        $ligne = $curseur->fetch();
        $curseur->closeCursor();
        return (bool)$ligne;
    }

    /**
     *  Modifie un enregistrement dans une table
     *  En cas d'erreur la méthode est interrompue et un message d'erreur est renvoyé : plusieurs clés possibles
     * @param int|string $id valeur de la clé primaire
     * @param array $lesValeurs tableau associatif des nouvelles valeurs
     */
    public function update(int|string $id, array $lesValeurs) : void
    {
        // Alimentation de la valeur des objets Input concernés
        foreach ($lesValeurs as $cle => $valeur) {
            if (!isset($this->columns[$cle])) {
                Erreur::traiterReponse("Requête mal formulée : colonne $cle inexistante.", 'global');
            } else {
                $this->columns[$cle]->Value = $valeur;
            }
        }

        // Génération de la clause Set et contrôle des valeurs modifiées
        $erreur = false;
        $set = "";
        foreach ($this->columns as $cle => $input) {
            if ($input->Value !== null) {
                if ($input->checkValidity()) {
                    $set .= "$cle = :$cle, ";
                } else {
                    $this->lesErreurs[$cle] = $input->getValidationMessage();
                    $erreur = true;
                }
            }
        }
        if ($erreur) {
            echo json_encode(['error' => $this->lesErreurs], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $set = substr($set, 0, -2);

        if (!self::existe($id)) {
            Erreur::traiterReponse("Enregistrement inexistant.", 'global');
        }

        //  Requête de mise à jour
        $sql = <<<SQL
            update  $this->tableName
             set $set
             where $this->idName = '$id';
SQL;
        $this->prepareAndExecute($sql);
    }


    /**
     * Modifie la valeur d'une colonne d'un enregistrement
     * En cas d'erreur la méthode est interrompue et un message d'erreur est renvoyé : plusieurs clés possibles
     * @param string $colonne Nom de la colonne à modifier
     * @param string|int $valeur Nouvelle valeur de la colonne
     * @param string|int $id Valeur de la clé primaire
     */
    public function modifierColonne(string $colonne, string|int $valeur, string|int $id) : void
    {
        try {
            // contrôle sur la colonne : La colonne doit faire partie des colonnes modifiables de la table
            $this->listOfColumns->Value = $colonne;
            if (!$this->listOfColumns->checkValidity()) {
                Erreur::traiterReponse("La colonne $colonne n'est pas modifiable.", 'global');
            }

            // contrôle de l'identifiant
            if (!self::existe($id)) {
                Erreur::traiterReponse("L'enregistrement à modifier n'existe pas.", 'global');
            }

            // contrôle de la valeur à l'aide de l'objet input associé dans la classe
            $input = $this->columns[$colonne];
            $input->Value = $valeur;
            if (!$input->checkValidity()) {
                Erreur::traiterReponse("La valeur pour la colonne $colonne n'est pas acceptée : " . $input->getValidationMessage(), 'global');
            }
            // modification dans la base
            // réalisation de la modification

            $sql = <<<SQL
            update  $this->tableName
             set $colonne= :valeur
             where $this->idName = :id;
SQL;
            $curseur = $this->db->prepare($sql);
            $curseur->bindValue('valeur', $valeur);
            $curseur->bindValue('id', $id);

            $curseur->execute();
        } catch (Exception $e) {
            Erreur::traiterReponse($e->getMessage());
        }
    }

    /**
     * Modifie la valeur d'une colonne d'un enregistrement
     * En cas d'erreur la méthode est interrompue et un message d'erreur est renvoyé
     * @param string $colonne Nom de la colonne à modifier
     * @param string|int $id Valeur de la clé primaire
     */
    public function setNull(string $colonne, string|int $id) : void
    {
        try {
            // contrôle de la colonne
            if (!isset($this->columns[$colonne])) {
                Erreur::traiterReponse("La colonne $colonne n'existe pas.", 'global');
            }

            // contrôle de l'identifiant
            $sql = <<<SQL
	        select 1
            from  $this->tableName
            where $this->idName = :id;
SQL;
            $curseur = $this->db->prepare($sql);
            $curseur->bindValue('id', $id);
            $curseur->execute();
            $ligne = $curseur->fetch();
            $curseur->closeCursor();
            if (!$ligne) {
                Erreur::traiterReponse("L'enregistrement à modifier n'existe pas.", 'global');
            }

            // modification dans la base
            // réalisation de la modification

            $sql = <<<SQL
            update  $this->tableName
             set $colonne= null
             where $this->idName = :id;
SQL;
            $curseur = $this->db->prepare($sql);
            $curseur->bindValue('id', $id);

            $curseur->execute();
        } catch (Exception $e) {
            Erreur::traiterReponse($e->getMessage());
        }
    }
}