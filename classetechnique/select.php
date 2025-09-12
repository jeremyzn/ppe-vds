<?php
declare(strict_types=1);

/**
 * Classe permettant de gérer toutes les requêtes de consultation de la base de données
 *
 * @Author : Guy Verghote
 * @Version : 2025.3
 * @Date : 08/07/2025
 */
class Select
{
    const MSG_ERREUR = "Erreur SQL : ";

    // attribut privé pour stocker l'objet PDO assurant la connexion à la base de données
    private PDO $db;

    /**
     * Constructeur d'un objet Select
     * Initialise l'attribut privé $db (objet PDO) en appelant la méthode getInstance de la classe Database
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retourne dans un tableau numérique, le résultat d'une requête SQL retournant plusieurs lignes
     * chaque ligne étant un tableau associatif (clé = nom colonne, valeur = valeur correspondante)
     *
     * @param string $sql La requête SQL à exécuter
     * @param array $lesParametres Paramètres de la requête (associatif)
     * @return array
     */
    public function getRows(string $sql, array $lesParametres = []): array
    {
        $curseur = $this->executer($sql, $lesParametres);
        $lesLignes = $curseur->fetchAll();
        $curseur->closeCursor();
        return $lesLignes;
    }

    /**
     * Retourne une seule ligne sous forme de tableau associatif
     * ou false si aucun enregistrement ne correspond
     *
     * @param string $sql La requête SQL à exécuter
     * @param array $lesParametres Paramètres de la requête (associatif)
     * @return array|false
     */
    public function getRow(string $sql, array $lesParametres = [])
    {
        $curseur = $this->executer($sql, $lesParametres);
        $ligne = $curseur->fetch();
        $curseur->closeCursor();
        return $ligne;
    }

    /**
     * Retourne une seule valeur (champ unique d’un enregistrement unique)
     * ou false si aucun enregistrement ne correspond
     *
     * @param string $sql La requête SQL à exécuter
     * @param array $lesParametres Paramètres de la requête (associatif)
     * @return mixed
     */
    public function getValue(string $sql, array $lesParametres = [])
    {
        $curseur = $this->executer($sql, $lesParametres);
        $valeur = $curseur->fetchColumn();
        $curseur->closeCursor();
        return $valeur;
    }

    /**
     * Méthode privée centralisant l'exécution des requêtes SQL (avec ou sans paramètres)
     * Gère la préparation, le binding des paramètres, et l’exécution sécurisée
     * En cas d’erreur, une réponse formatée est envoyée et le script est arrêté.
     *
     * @param string $sql La requête SQL
     * @param array $lesParametres Paramètres associés
     * @return PDOStatement
     */
    private function executer(string $sql, array $lesParametres = []): PDOStatement
    {
        try {
            if ($lesParametres === []) {
                $curseur = $this->db->query($sql);
            } else {
                $curseur = $this->db->prepare($sql);
                $curseur->execute($lesParametres);
            }
            return $curseur;
        } catch (PDOException $e) {
            Erreur::traiterReponse(self::MSG_ERREUR . $e->getMessage(), 'system');
            // Code inaccessible, mais nécessaire pour respecter la signature de la méthode
            return false;
        }
    }
}
