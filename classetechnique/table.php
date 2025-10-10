<?php
declare(strict_types=1);

/**
 * Classe Table : représente une table SQL
 * Cette classe est une classe abstraite donc non instanciable.
 * Elle met en facteur tous les attributs et toutes les méthodes communes aux classes dérivées
 * @Author : Guy Verghote
 * @Version : 2025.6
 * @Date : 06/10/2025
 * Correction de la gestion des fichiers lors de la modification
 */
abstract class Table
{
    private PDO $db;
    private string $tableName;
    protected string $idName = 'id';
    protected array $columns;
    protected InputList $listOfColumns;
    private array $lesErreurs = [];
    private bool|string $lastInsertId = false;

    protected function __construct(string $nomTable)
    {
        $this->tableName = $nomTable;
        $this->columns = [];
        $this->listOfColumns = new InputList();
        $this->db = Database::getInstance();
    }

    public function getColonne(string $colonne): Input
    {
        return $this->columns[$colonne];
    }

    public function getLesErreurs(): array
    {
        return $this->lesErreurs;
    }

    public function getLastInsertId(): bool|string
    {
        return $this->lastInsertId;
    }

    public function setValue(string $colonne, mixed $valeur): void
    {
        if (isset($this->columns[$colonne])) {
            $this->columns[$colonne]->Value = $valeur;
        }
    }

    private function prepareAndExecute(string $sql): void
    {
        try {
            $curseur = $this->db->prepare($sql);
            foreach ($this->columns as $cle => $input) {
                if ($input->Value === null) {
                    continue;
                }
                $curseur->bindValue($cle, $input->Value);
            }
            $curseur->execute();
        } catch (Throwable $e) {
            Erreur::traiterReponse($sql . " : " . $e->getMessage());
        }
    }

    /**
     * Alimente les valeurs des objets Input à partir de $_POST
     * Vérifie que tous les champs obligatoires sont renseignés
     */
    public function donneesTransmises(): bool
    {
        // Alimentation depuis $_POST
        foreach ($_POST as $cle => $valeur) {
            $valeur = trim($valeur);
            if ($valeur !== '' && isset($this->columns[$cle])) {
                $this->columns[$cle]->Value = $valeur;
            }
        }

        // Vérification des champs obligatoires
        $ok = true;
        foreach ($this->columns as $cle => $input) {
            if ($input->Require && $input->Value === null) {
                $this->lesErreurs[$cle] = "Veuillez renseigner ce champ.";
                $ok = false;
            }
        }
        return $ok;
    }

    public function checkAll(): bool
    {
        $correct = true;
        foreach ($this->columns as $cle => $input) {
            if (!$input->checkValidity()) {
                $this->lesErreurs[$cle] = $input->getValidationMessage();
                $correct = false;
            }
        }
        return $correct;
    }

    public function insert(): void
    {
        $set = "";
        foreach ($this->columns as $cle => $input) {
            if ($input->Value !== null) {
                $set .= "$cle = :$cle, ";
            }
        }
        $set = substr($set, 0, -2);
        $sql = "insert into $this->tableName set $set";

        $this->prepareAndExecute($sql);
        $this->lastInsertId = $this->db->lastInsertId();
    }

    public function delete(int|string $id): void
    {
        try {
            // Suppression de l'enregistrement
            $sql = "DELETE FROM $this->tableName WHERE $this->idName = :id";
            $curseur = $this->db->prepare($sql);
            $curseur->bindValue('id', $id);
            $curseur->execute();
        } catch (Throwable $e) {
            Erreur::traiterReponse($e->getMessage());
        }
    }

    protected function existe($id): bool
    {
        $sql = "SELECT 1 FROM $this->tableName WHERE $this->idName = :id";
        $curseur = $this->db->prepare($sql);
        $curseur->bindValue('id', $id);
        $curseur->execute();
        $ligne = $curseur->fetch();
        $curseur->closeCursor();
        return (bool)$ligne;
    }

    public function update(int|string $id, array $lesValeurs): void
    {
        if (!$this->existe($id)) {
            Erreur::traiterReponse("Enregistrement inexistant.", 'global');
        }

        // 1. Alimenter les valeurs depuis $_POST
        foreach ($lesValeurs as $cle => $valeur) {
            if (!isset($this->columns[$cle])) {
                Erreur::traiterReponse("Requête mal formulée : colonne $cle inexistante.", 'global');
            }
            $this->columns[$cle]->Value = $valeur;
        }

        // 2. Validation de toutes les valeurs
        $erreur = false;
        $set = "";

        foreach ($this->columns as $cle => $input) {
            if ($input->Value !== null) {
                if (!$input->checkValidity()) {
                    $this->lesErreurs[$cle] = $input->getValidationMessage();
                    $erreur = true;
                } else {
                    $set .= "$cle = :$cle, ";
                }
            }
        }

        if ($erreur) {
            echo json_encode(['error' => $this->lesErreurs], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (empty($set)) {
            Erreur::traiterReponse("Aucune modification à effectuer.", 'global');
        }

        $set = substr($set, 0, -2);

        // 3. Mise à jour en base de données
        $sql = "UPDATE $this->tableName SET $set WHERE $this->idName = :id";

        try {
            $curseur = $this->db->prepare($sql);
            foreach ($this->columns as $cle => $input) {
                if ($input->Value !== null) {
                    $curseur->bindValue($cle, $input->Value);
                }
            }
            $curseur->bindValue('id', $id);
            $curseur->execute();
        } catch (Throwable $e) {
            Erreur::traiterReponse($e->getMessage());
        }
    }

    public function modifierColonne(string $colonne, string|int $valeur, string|int $id): void
    {
        try {
            $this->listOfColumns->Value = $colonne;
            if (!$this->listOfColumns->checkValidity()) {
                Erreur::traiterReponse("La colonne $colonne n'est pas modifiable.", 'global');
            }

            if (!$this->existe($id)) {
                Erreur::traiterReponse("L'enregistrement à modifier n'existe pas.", 'global');
            }

            $input = $this->columns[$colonne];
            $input->Value = $valeur;
            if (!$input->checkValidity()) {
                Erreur::traiterReponse("La valeur pour la colonne $colonne n'est pas acceptée : " . $input->getValidationMessage(), 'global');
            }

            $sql = "UPDATE $this->tableName SET $colonne = :valeur WHERE $this->idName = :id";
            $curseur = $this->db->prepare($sql);
            $curseur->bindValue('valeur', $valeur);
            $curseur->bindValue('id', $id);
            $curseur->execute();
        } catch (Throwable $e) {
            Erreur::traiterReponse($e->getMessage());
        }
    }

    public function setNull(string $colonne, string|int $id): void
    {
        try {
            if (!isset($this->columns[$colonne])) {
                Erreur::traiterReponse("La colonne $colonne n'existe pas.", 'global');
            }

            if (!$this->existe($id)) {
                Erreur::traiterReponse("L'enregistrement à modifier n'existe pas.", 'global');
            }

            $sql = "UPDATE $this->tableName SET $colonne = null WHERE $this->idName = :id";
            $curseur = $this->db->prepare($sql);
            $curseur->bindValue('id', $id);
            $curseur->execute();
        } catch (Throwable $e) {
            Erreur::traiterReponse($e->getMessage());
        }
    }
}