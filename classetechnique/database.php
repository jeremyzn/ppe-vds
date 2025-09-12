<?php
declare(strict_types=1);
/**
 * Classe permettant de se connecter à la base de données et d'obtenir un objet PDO pour réaliser les requêtes
 * Nécessite un fichier de configuration database.php stocké dans le répertoire /.config du projet
 * Le fichier de configuration doit retourner un tableau associatif dont les clés sont les paramètres de connexion
 * @Author : Guy Verghote
 * @Version : 2025.4
 * @Date : 28/08/2025
 * changement du mode sql afin de refuser les dates invalides
 */

class Database
{
    // attribut privé pour stocker l'objet PDO assurant la connexion à la base de données
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (is_null(self::$instance)) {
            // Récupération des paramètres de configuration
            $lesParametres = self::getLesParametres();
            $host = $lesParametres['host'];
            $database = $lesParametres['database'];
            $user = $lesParametres['user'];
            $password = $lesParametres['password'];
            $port = $lesParametres['port'];
            $charset = $lesParametres['charset'];
            try {
                $chaine = "mysql:host=$host;dbname=$database;port=$port;charset=$charset";
                $db = new PDO($chaine, $user, $password);
                $db->setAttribute(PDO::ATTR_ERRMODE, $lesParametres['errmode']);
                $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $lesParametres['fetchmode']);
                $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $db->exec("SET sql_mode = 'STRICT_ALL_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ONLY_FULL_GROUP_BY'");
                self::$instance = $db;
            } catch (PDOException $e) {
                $msg = substr($e->getMessage(), strrpos($e->getMessage(), ']') + 1);
                Erreur::traiterReponse('Accès à la base de données impossible : ' . $msg,);
            }
        }
        return self::$instance;
    }

    /**
     * Retourne dans un tableau les paramètres de connexion stockés dans le fichier de configuration de la base de données
     * @return array
     */
    public static function getLesParametres(): array
    {
        $configFile = $_SERVER['DOCUMENT_ROOT']  . "/.config/database.php";
        // Vérification de l'existence du fichier de configuration
        if (!is_file($configFile)) {
            Erreur::traiterReponse("Fichier de configuration de la base de données introuvable : $configFile");
        }
        $lesParametres = require $configFile;
        // Vérification du type de données retourné
        if (!is_array($lesParametres)) {
            Erreur::traiterReponse('Fichier de configuration de la base de données incorrect');
        }

        // Vérification de la présence des clés obligatoires
        foreach (['host', 'database', 'user', 'password'] as $key) {
            if (!isset($lesParametres[$key])) {
                Erreur::traiterReponse("Clé de configuration manquante : $key");
            }
        }
        // Alimentation des paramètres ayant des valeurs par défaut
        $lesParametres['port'] = $lesParametres['port'] ?? 3306;
        $lesParametres['charset'] = $lesParametres['charset'] ?? 'utf8';
        $lesParametres['errmode'] = $lesParametres['errmode'] ?? PDO::ERRMODE_EXCEPTION;
        $lesParametres['fetchmode'] = $lesParametres['fetchmode'] ?? PDO::FETCH_ASSOC;
        return $lesParametres;
    }
}