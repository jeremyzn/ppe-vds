<?php
declare(strict_types=1);

/**
 * Classe statique de gestion des accès applicatifs en fonction du rôle (membre / administrateur)
 * et du contexte d'exécution (répertoire, appel AJAX, etc.).
 *
 * Date : 30/08/2025
 * Auteur : Guy. Verghote
 */
class ControleAcces
{
    /**
     * Vérifie l'accès au répertoire courant selon le rôle
     * Appel à faire dans le script de contrôle
     */
    public static function verifier(): void
    {
        $path = dirname($_SERVER['PHP_SELF']);
        $segments = explode('/', trim($path, '/'));
        $repertoire = $segments[0] ?? '';
        $dernierChemin = end($segments);

        if ($repertoire === 'administration') {
            self::verifierAccesAdministration();
        } elseif ($repertoire === 'membre') {
            self::verifierAccesMembre();
        }

        // Gestion du jeton CSRF
        self::gererJeton($dernierChemin);
    }

    /**
     * Vérifie que l'utilisateur est connecté et est un administrateur autorisé
     */
    public static function verifierAccesAdministration(): void
    {
        if (!isset($_SESSION['membre'])) {
            // Redirection vers la page de connexion
            self::seConnecter();
            // Erreur::traiterReponse("Vous devez être connecté pour accéder à cette fonctionnalité", 'global');
        }

        $idMembre = $_SESSION['membre']['id'];

        if (!Administrateur::estUnAdministrateur($idMembre)) {
            Erreur::traiterReponse("Vous devez être administrateur pour accéder à cette fonctionnalité", 'global');
        }

        // Récupération du répertoire courant
        $repertoire = basename(dirname($_SERVER['PHP_SELF']));

        // Accès autorisé pour les répertoires fixes + ceux spécifiquement autorisés
        $repertoiresLibres = ['administration', 'ajax'];
        if (!in_array($repertoire, $repertoiresLibres) && !Administrateur::peutAdministrer($idMembre, $repertoire)) {
            Erreur::traiterReponse("Vous n'avez pas les droits pour accéder au répertoire : $repertoire", 'global');
        }
    }

    /**
     * Vérifie que le membre est connecté (utilisé pour l’espace membre)
     */
    public static function verifierAccesMembre(): void
    {
        if (!isset($_SESSION['membre'])) {
            // Redirection vers la page de connexion
            self::seConnecter();
            // Erreur::traiterReponse("Vous devez être connecté pour accéder à votre espace", 'global');
        }
    }

    /**
     * Crée ou vérifie un jeton CSRF en fonction du contexte (formulaire / ajax)
     * @param string $dernierChemin
     * @return void
     */
    private static function gererJeton(string $dernierChemin): void
    {
        if ($dernierChemin === 'ajax') {
            Jeton::verifier();
        } else {
            $ajax = dirname($_SERVER['SCRIPT_FILENAME']) . '/ajax';
            if (is_dir($ajax)) {
                Jeton::creer();
            }
        }
    }

    /**
     * Redirige vers la page de connexion en mémorisant l'URL demandée dans la session
     */
    private static function seConnecter(): void
    {
        // Mémorisation de la page demandée
        $_SESSION['url'] = $_SERVER['REQUEST_URI'];
        // Redirection vers la page de connexion
        header("Location: /connexion");
        exit;
    }
}
