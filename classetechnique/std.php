<?php
declare(strict_types=1);
/**
 * Classe Std : Classe statique permettant l'affichage le contrôle et la conversion des données
 * @Author : Guy Verghote
 * @Version : 2025.1
 */

class Std
{
    /**
     * Vérifie l'existence des variables passées par POST ou GET
     * Accepte un nombre variable de paramètres qui représentent les variables dont il faut vérifier l'existence
     * Exemple d'appel : if (!Std::existe('id', 'nom', 'prenom')) {...}
     * @return bool vrai si toutes les clés existent dans le tableau
     */
    public static function existe(): bool
    {
        foreach (func_get_args() as $champ) {
            if (!isset($_REQUEST[$champ])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Suppression des espaces superflus à l'intérieur et aux extrémités d'une chaine.
     * @param string $valeur Chaîne à transformer
     * @return string
     */
    public static function supprimerEspace(string $valeur): string
    {
        // return preg_replace("#[[:space:]]{2,}#", " ", trim($valeur));
        return preg_replace('/ {2,}/', ' ', trim($valeur));
    }

    /**
     * @param string $valeur
     * @return string
     */
    public static function supprimerAccent(string $valeur): string
    {
        // Problème : une apostrophe vient se placer devant les lettres ayant perdu leur accent
        // return iconv('UTF-8', 'ASCII//TRANSLIT', $valeur);
        $lesAccents = [
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ø' => 'O', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ø' => 'o', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'Ý' => 'Y', 'ý' => 'y',
            'ÿ' => 'y', 'Ç' => 'C', 'ç' => 'c', 'Ñ' => 'N', 'ñ' => 'n',
            'Æ' => 'AE', 'æ' => 'ae', 'ß' => 'ss', 'Þ' => 'TH', 'þ' => 'th', 'Ð' => 'DH', 'ð' => 'dh'];
        return strtr($valeur, $lesAccents);
    }

    /**
     * Conversion d'une chaine de format jj/mm/aaaa au format aaaa-mm-jj
     * @param string $date au format jj/mm/aaaa
     * @return string Chaîne au aaaa-mm-jj
     */
    public static function encoderDate(string $date): string
    {
        // pour éviter l'usage d'une structure conditionnelle la fonction str_pad offre le moyen d'ajouter éventuellement un 0
        $lesElements = explode('/', $date);
        $mois = str_pad($lesElements[1], 2, '0', STR_PAD_LEFT);
        $jour = str_pad($lesElements[0], 2, '0', STR_PAD_LEFT);
        return "$lesElements[2]-$mois-$jour";
    }

    /**
     * Conversion d'une chaine de format aaaa-mm-jj au format jj/mm/aaaa
     * @param string $date au aaaa-mm-jj
     * @return string au format jj/mm/aaaa
     */
    public static function decoderDate(string $date): string
    {
        return substr($date, 8) . '/' . substr($date, 5, 2) . '/' . substr($date, 0, 4);
    }

    /**
     * Conversion d'une chaine de format hh:mm:ss au format hh:mm
     * @param string $temps au format hh:mm:ss
     * @return string au format hh:mm
     */
    public static function dateFrValide(string $valeur): bool
    {
        $correct = preg_match('`^(\d{2})/(\d{2})/(\d{4})$`', $valeur, $tdebut);
        if ($correct) {
            $an = intval($tdebut[3]);
            $mois = intval($tdebut[2]);
            $jour = intval($tdebut[1]);
            $correct = checkdate($mois, $jour, $an) && ($an > 1900);
        }
        return $correct;
    }

    /**
     * Vérifie si une date est valide au format aaaa-mm-jj
     * @param string $valeur
     * @return bool
     */
    public static function dateMysqlValide(string $valeur): bool
    {
        $correct = preg_match('`^(\d{4})-(\d{2})-(\d{2})$`', $valeur, $tdebut);
        if ($correct) {
            $an = intval($tdebut[1]);
            $mois = intval($tdebut[2]);
            $jour = intval($tdebut[3]);
            $correct = checkdate($mois, $jour, $an) && ($an > 1900);
        }
        return $correct;
    }

    /**
     * Vérifie si une url est valide et si elle existe
     * @param string $valeur
     * @return bool
     */
    public static function urlValide(string $valeur): bool
    {
        // $correct = preg_match("`((http://|https://)?(www.)?(([a-zA-Z0-9-]){2,}\.){1,4}([a-zA-Z]){2,6}(/([a-zA-Z-_/.0-9#:?=&;,]*)?)?)`", $valeur);
        $correct = preg_match("`(https?://)?(www.)?(([a-zA-Z0-9-]){2,}\.){1,4}([a-zA-Z]){2,6}(/([a-zA-Z-_/.0-9#:?=&;,]*)?)?`", $valeur);
        if (!$correct) {
            return false;
        }
        // vérification de l'existence réelle de cette url
        $f = @fopen($valeur, "r");
        if ($f) {
            fclose($f);
            $correct = true;
        } else {
            $correct = false;
        }
        return $correct;
    }

    /**
     * Vérifie si une adresse email est valide
     * @param string $valeur
     * @return bool
     */
    public static function emailValide(string $valeur): bool
    {
        // return  preg_match("/^[0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-_.]?[0-9a-z])*\.[a-z]{2,4}$/i", $valeur);
        // nouvelle solution à l'aide de la fonction filter_var
        $correct = filter_var($valeur, FILTER_VALIDATE_EMAIL);
        if (!$correct) {
            return false;
        }
        // vérification de l'existence du domaine
        $domaine = substr(strrchr($valeur, "@"), 1);
        if (!checkdnsrr($domaine, "MX")) {
            return false; // Le domaine ou les enregistrements MX n'existent pas
        }
        return true;
    }

    /**
     * Vérifie si un mot de passe est valide
     * Au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial
     * @param string $valeur
     * @param int $longueur
     * @return bool
     */
    public static function passwordValide(string $valeur, int $longueur = 8): bool
    {
        return preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[_\W]).+$/", $valeur) === 1
            && (strlen($valeur) >= $longueur);
    }

    /**
     * Vérifie si un code postal est valide
     * @param string $valeur
     * @return bool
     */
    public static function codePostalValide(string $valeur): bool
    {
        return preg_match('/^(?:0[1-9]|[1-8]\d|9[0-8])\d{3}$/', $valeur) === 1;
    }

    /**
     * Vérifie si un numéro de téléphone mobile est valide
     * @param string $valeur
     * @return bool
     */
    public static function mobileValide(string $valeur): bool
    {
        return preg_match("/^0[67]\d{8}$/", $valeur) === 1;
    }

    /**
     * Vérifie si un numéro de téléphone fixe est valide
     * @param string $valeur
     * @return bool
     */
    public static function fixeValide(string $valeur): bool
    {
        return preg_match("/^0[1-59]\d{8}$/", $valeur) === 1;
    }

    /**
     * Vérifie si un temps au format hh:mm:ss ou h:m:s  est valide
     * @param string $valeur
     * @return bool
     */
    public static function tempsValide(string $valeur): bool
    {
        return preg_match("/^(\d|0\d|1\d|2[0-3]):(\d|[0-5]\d):(\d|[0-5]\d)$/", $valeur);
    }

    /**
     * Vérifie si un nom est valide
     * @param string $valeur
     * @return bool
     */
    public static function nomValide(string $valeur): bool
    {
        return preg_match("/^[a-z]+([' -]?[a-z]+)*$/i", $valeur);
    }

    /**
     * Vérifie si un nom avec accent est valide
     * @param string $valeur
     * @return bool
     */
    public static function nomAvecAccentValide(string $valeur): bool
    {
        return preg_match("/^[a-zàáâãäåòóôõöøèéêëçìíîïùúûüÿñ]+([ '-]?[a-zàáâãäåòóôõöøèéêëçìíîïùúûüÿñ]+)*$/i", $valeur);
    }

    public static function nombreEntierValide($valeur): bool
    {
        if (is_int($valeur)) {
            return true;
        }

        if (is_string($valeur)) {
            return preg_match('/^-?\d+$/', $valeur) === 1;
        }
        return false;
    }

    public static function nombreReelValide($valeur): bool
    {
        if (!is_numeric($valeur)) {
            return false;
        }
        return filter_var($valeur, FILTER_VALIDATE_FLOAT) !== false;
    }

    /**
     * Retourne dans un tableau les fichiers d'un répertoire
     * l'extension des fichiers doit correspondre aux extensions passées en paramètre
     * @param string $rep Nom du répertoire
     * @param array $lesExtensions tableau des extensions cherchées
     * @param string $order critère de tri a pour croissant et d pour décroissant
     * @return array tableau des fichiers récupérés
     */
    public static function getLesFichiers(string $rep, array $lesExtensions = [], string $order = "a"): array
    {
        $liste = array();
        $lesFichiers = scandir($rep);
        foreach ($lesFichiers as $fichier) {
            if ($fichier != "." && $fichier != "..") {
                if (is_dir($rep . '/' . $fichier)) {
                    continue;
                }
                if($lesExtensions === []){
                    $liste[] = $fichier;
                    continue;
                }
                $extension = strtolower(pathinfo($fichier, PATHINFO_EXTENSION));
                if (in_array($extension, $lesExtensions)) {
                    $liste[] = $fichier;
                }
            }
        }

        natcasesort($liste);
        if (strtolower($order) !== "a") {
            $liste = array_reverse($liste);
        }
        // problème le tableau n'est plus numérique mais associatif
        return array_values($liste); // Réindexer le tableau
    }

}
