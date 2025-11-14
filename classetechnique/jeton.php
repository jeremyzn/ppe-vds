<?php
declare(strict_types=1);

/**
 * Classe Jeton : gère la création et la vérification de jetons CSRF
 * Permet de fournir une preuve que la requête a bien été initiée par l’utilisateur légitime (et non par un site externe).
 * Remarque : Si toutes les entrées utilisateurs sont bien filtrée il n'a pas de risque d'injection XSS qui pourrait contourner cette protection
 * @Author : Guy Verghote
 * @Version 2025.4
 * @Date : 12/11/2025
 */
class Jeton
{
    /**
     * Création d'un jeton de vérification sécurisé CSRF ("Cross-Site Request Forgery")
     * @param int $dureeVie Durée de vie du jeton en secondes
     * Si $dureeVie vaut 0, le jeton expire avec la session.
     * @return void
     */
    public static function creer(int $dureeVie = 0): void
    {
        $token = bin2hex(random_bytes(32));
        $expires = $dureeVie > 0 ? time() + $dureeVie : 0;
        $_SESSION['csrf_token'] = [
            'value' => $token,
            'expires' => $expires,
        ];

        setcookie('csrf_token', $token, [
            'expires' => $expires,
            'path' => '/',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }

     /**
     * Vérifie si le jeton reçu via le cookie est valide
     * @return void
     */
    public static function verifier(): void
    {
        
        // Vérifier que le jeton existe côté serveur 
           if (!isset($_SESSION['csrf_token']['value'], $_SESSION['csrf_token']['expires'])) {
           // accès direct ou non-AJAX → redirection vers la page d'erreur
           header('Location: /erreur/403.php');
           exit;
        }

        // Vérifier que  le cookie de meme nom existe aussi
        if (!isset($_COOKIE['csrf_token'])) {
            // accès direct ou non-AJAX → redirection vers la page d'erreur
            header('Location: /erreur/403.php');
            exit;
        }

        //  Vérifier que le jeton côté serveur n'a pas expiré
        $expires = (int)$_SESSION['csrf_token']['expires'];
        if ($expires > 0 && $expires < time()) {
            Erreur::envoyerReponse("Votre session a expiré, veuillez recharger la page et réessayer.", 'global');
        }

        // Vérifier que les deux valeurs correspondent
		// Utiliser hash_equals() est essentiel pour éviter les attaques par timing.
        if (!hash_equals($_SESSION['csrf_token']['value'], $_COOKIE['csrf_token'])) {
             header('Location: /erreur/403.php');
             exit;
        }
    }
}
