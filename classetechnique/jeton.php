<?php
declare(strict_types=1);

/**
 * Classe Jeton : gère la création et la vérification de jetons CSRF
 * Permet de fournir une preuve que la requête a bien été initiée par l’utilisateur légitime (et non par un site externe).
 *
 * @Author : Guy Verghote
 * @Version 2025.2
 * @Date : 11/07/2025
 */
class Jeton
{

    /**
     * Création d'un jeton de vérification sécurisé CSRF ("Cross-Site Request Forgery")
     * @param int $dureeVie Durée de vie du jeton en secondes
     * Si $dureeVie vaut 0, le jeton n’expire pas (ou expire avec la session).
     * @return void
     */
    public static function creer(int $dureeVie = 0)
    {
        if (!isset($_SESSION['token'])) {

            $token = bin2hex(random_bytes(32));
            $expires = $dureeVie > 0 ? time() + $dureeVie : time() + (10 * 365 * 24 * 3600); // 10 ans

            $_SESSION['token'] = [
                'value' => $token,
                'expires' => $expires,
            ];

            setcookie('token', $token, [
                'expires' => $expires, // Le navigateur supprimera le cookie après cette date
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // HTTPS obligatoire
                'httponly' => true,    // Le cookie n'est pas accessible en JavaScript
                'samesite' => 'Strict' // Protection CSRF
            ]);
        }
    }


    /**
     * Vérifie si le jeton reçu via le cookie est valide
     * @return bool
     */
    public static function verifier()
    {

        // 1. Vérifier que le jeton existe côté serveur
        if (!isset($_SESSION['token'])) {
            Erreur::traiterReponse("Jeton manquant côté serveur.", 'global');
        }

        // 2. Récupérer le jeton envoyé par le navigateur (via cookie)

        $token = $_COOKIE['token'] ?? null;


        if ($token === null) {
            Erreur::traiterReponse("Jeton manquant.", 'global');
        }

        // 3. Comparer la valeur du cookie avec celle en session
        if ($_SESSION['token']['value'] !== $token) {
            Erreur::traiterReponse("Jeton invalide.", 'global');
        }

        // 4. Vérifier l'expiration du jeton
        if ($_SESSION['token']['expires'] < time()) {
            Erreur::traiterReponse("Jeton expiré.", 'global');
        }

    }
}


