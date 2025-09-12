<?php
declare(strict_types=1);

/**
 * Classe permettant de journaliser automatiquement tout événement dans des fichiers log.
 * Les journaux sont créés à la demande dans le dossier .log du dossier backoffice
 * Aucun fichier de configuration requis.
 *
 * @author Guy Verghote
 * @version 2025.3
 * @date 14/06/2025
 */
class Journal
{
    /**
     * Nom du dossier de journalisation
     */
    const REPERTOIRE = '/backoffice/.log';

    /**
     * Retourne le chemin absolu vers un fichier journal donné,
     * et crée le dossier de journalisation s’il n’existe pas.
     *
     * @param string $nom Nom du journal (sans extension)
     * @return string Chemin absolu du fichier log
     */
    private static function getChemin(string $nom): string
    {
        // Chemin absolu vers le dossier backoffice
        $racineBackoffice = $_SERVER['DOCUMENT_ROOT'] . '/backoffice';

        // Vérifie que le dossier backoffice existe
        if (!is_dir($racineBackoffice)) {
            mkdir($racineBackoffice, 0775, true);
        }

        // Chemin vers le dossier de log
        $repertoire = $racineBackoffice . '/.log';

        // Crée le dossier .log s’il n’existe pas
        if (!is_dir($repertoire)) {
            mkdir($repertoire, 0775, true);
        }

        return "$repertoire/$nom.log";
    }

    /**
     * Formate une ligne à enregistrer dans un journal.
     *
     * @param string $evenement Texte décrivant l’événement
     * @param string $script Nom du script ayant généré l’événement
     * @param string $ip Adresse IP du client (ou "CLI")
     * @return string Ligne formatée prête à écrire
     */
    private static function formatterLigne(string $evenement, string $script, string $ip): string
    {
        $date = date('d/m/Y H:i:s');
        return "$date\t$evenement\t$script\t$ip\n";
    }

    /**
     * Enregistre un événement dans le journal spécifié.
     * Crée le journal à la volée s’il n’existe pas.
     *
     * Format : date;événement;script;ip
     *
     * @param string $evenement Texte libre décrivant l’événement
     * @param string $journal Nom du journal sans extension (par défaut : 'evenement')
     */
    public static function enregistrer(string $evenement, string $journal = 'evenement'): void
    {
        $fichier = self::getChemin($journal);
        $script = $_SERVER['SCRIPT_NAME'] ?? 'CLI';
        $ip = self::getIp();
        $ligne = self::formatterLigne($evenement, $script, $ip);

        $file = fopen($fichier, 'a');
        if ($file && flock($file, LOCK_EX)) {
            fwrite($file, $ligne);
            flock($file, LOCK_UN);
        }
        fclose($file);
    }

    /**
     * Retourne tous les événements d’un journal donné, du plus récent au plus ancien.
     * Chaque ligne est convertie en tableau indexé : [date, événement, script, ip]
     *
     * @param string $journal Nom du journal (par défaut : 'evenement')
     * @return array<int, array{0:string,1:string,2:string,3:string}>
     */
    public static function getLesEvenements(string $journal = 'evenement'): array
    {
        $fichier = self::getChemin($journal);
        if (!is_file($fichier)) {
            return []; // Aucun événement à afficher
        }
        // On lit le fichier en inversant l'ordre des lignes
        return file($fichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    /**
     * Supprime le journal spécifié (le fichier .log correspondant).
     *
     * @param string $journal Nom du journal sans extension
     */
    public static function supprimer(string $journal): void
    {
        $fichier = self::getChemin($journal);
        if (is_file($fichier)) {
            unlink($fichier);
        }
    }

    /**
     * Retourne une liste des journaux existants.
     * Tableau associatif : [ 'erreur' => 'Journal erreur', ... ]
     *
     * @return array<string, string> Liste des journaux disponibles
     */
    public static function getListe(): array
    {
        $repertoire = $_SERVER['DOCUMENT_ROOT'] . self::REPERTOIRE;
        // Récupération de tous les fichiers .log dans le répertoire
        $fichiers = glob($repertoire . '/*.log');
        // On extrait le nom de chaque fichier sans l'extension
        $liste = [];
        foreach ($fichiers as $fichier) {
            $nomComplet = basename($fichier); // ex: "erreur.log"
            $nomSansExt = pathinfo($nomComplet, PATHINFO_FILENAME); // ex: "erreur"
            $liste[$nomSansExt] = "Journal $nomSansExt";
        }
        return $liste;
    }

    /**
     * Retourne la meilleure estimation de l’adresse IP du client (ou "CLI" si en mode console).
     *
     * @return string Adresse IP ou "CLI"
     */
    public static function getIp(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['HTTP_CLIENT_IP']
            ?? $_SERVER['REMOTE_ADDR']
            ?? 'CLI';
    }
}
