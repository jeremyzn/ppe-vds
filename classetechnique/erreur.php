<?php
declare(strict_types=1);

/**
 * Classe Erreur : Classe permettant de générer la réponse du serveur en cas d'erreur détectée
 * Utilise la classe technique Journal pour journaliser les erreurs
 * @Author : Guy Verghote
 * @Version : 2025.4
 * @Date : 09/09/2025
 * Renommage de la méthode traiterErreur en traiterReponse
 * réécriture methode getErreurHttp
 * remplacement du caractère # par ~ pour la détection des erreurs déclenchées par un trigger
 */
class Erreur
{
    /**
     * Réponse du serveur dans le format json suite à la détection d'une erreur
     * Méthode à utiliser lors d'un appel ajax de type Fecth
     *
     *    Si le type n'est pas précisé, il est déduit à partir du contenu du message
     *    C'est le cas lors d'une erreur déclenché par un trigger
     *    Si le type est 'system', le message est enregistré dans le journal des erreurs
     *
     * @param string $message message associé au type de l'erreur
     * @param string|null $type [facultatif] type de l'erreur : 'global' ou 'system'
     * @return void
     */
    public static function envoyerReponse(string $message, ?string $type = null): void
    {
        if ($type === null) {
            $messageDeclencheur = strstr($message, '~');
            if ($messageDeclencheur) {
                $type = 'global';
                $message = substr($messageDeclencheur, 1);
            } else {
                Journal::enregistrer($message, 'erreur');
                $type = 'system';
                $message = "Une erreur est survenue, veuillez consulter le journal des erreurs pour en savoir plus";
            }
        } elseif ($type === 'system') {
            Journal::enregistrer($message, 'erreur');
            $message = "Une erreur est survenue, veuillez consulter le journal des erreurs pour en savoir plus";
        }

        $lesErreurs[$type] = $message;
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => $lesErreurs], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Rédirection vers la page /erreur afin d'afficher le message d'erreur dans une page
     *
     * Utiliser au niveau des scripts appelés directement depuis l'url
     *    La méthode redirige l'utilisateur vers la page erreur/index.php
     *    Le message d'erreur et le script (page) à l'origine de l'erreur sont conservés dans une variable de session
     * @param string $message message associé au type de l'erreur
     * @param string|null $type [facultatif] type de l'erreur :  'global' ou 'system'
     * @return void
     */
    public static function afficherReponse(string $message, ?string $type = null): void
    {
        // Si le type n'est pas précisé, on tente de le déduire du message
        if ($type === null) {
            // si le message provient d'un déclencheur il doit contenir un ~ et dans ce cas, il peut être affiché
            $messageDeclencheur = strstr($message, '');
            if ($messageDeclencheur) {
                $type = 'global';
                $message = substr($messageDeclencheur, 1);
            } else {
                // Si le message ne provient pas d'un déclencheur, on enregistre l'erreur dans le journal
                Journal::enregistrer($message, 'erreur');
                $type = 'system';
                $message = "Une erreur est survenue, veuillez consulter le journal des erreurs pour en savoir plus";
            }
        } elseif ($type === 'system') {
            // Si le type est 'system', on enregistre l'erreur dans le journal
            Journal::enregistrer($message, 'erreur');
            $message = "Une erreur est survenue, veuillez consulter le journal des erreurs pour en savoir plus";
        }

        // Démarrage de la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Désactivation de la session et enregistrement du message d'erreur
        $_SESSION['erreur'] = [];
        $_SESSION['erreur']['page'] = $_SERVER['PHP_SELF'];
        $_SESSION['erreur']['message'] = $message;

        // Redirection vers la page d'erreur
        header('Location:/erreur');
        exit;
    }

    /**
     * Appelé dans les scripts pouvant être invoqués en Ajax ou en direct
     */
    public static function traiterReponse(string $message, ?string $type = null): void
    {
        if (self::estAppelAjax()) {
            self::envoyerReponse($message, $type);
        } else {
            self::afficherReponse($message, $type);
        }
    }

    /**
     * Détection d'un appel Ajax (fetch ou jQuery)
     * Attention l'appel fecth doit contenir l'en-tête 'X-Requested-With' pour être reconnu
     *            headers: {'X-Requested-With': 'XMLHttpRequest'},
     */
    private static function estAppelAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Blocage IP et session, redirection vers la page d'erreur
     */
    public static function bloquerVisiteur(): void
    {
        // Enregistrement de l'URL dans le journal des menaces
        $url = $_SERVER['REQUEST_URI'];
        Journal::enregistrer($url, 'menace');

        // Démarrage de la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Désactivation de la session et enregistrement du message d'erreur
        $_SESSION['disable'] = true;
        $_SESSION['erreur'] = [];
        $_SESSION['erreur']['message'] = "Votre requête a été jugée malveillante, Votre session a été désactivée et votre adresse IP a été enregistrée";
        header('Location:/erreur');
        exit;
    }

    /**
     * Message associé à un code HTTP
     */
    public static function getErreurHttp(int|string $codeHttp): string
    {
        $libelles = [
            400 => "Requête incorrecte",
            401 => "Erreur d'authentification",
            403 => "Demande interdite par les règles administratives. Veuillez vous assurer que votre demande comporte un en-tête User-Agent.",
            404 => "Page non trouvée",
            405 => "Méthode non autorisée",
            408 => "Temps d'attente d'une requête dépassé",
            500 => "Erreur interne du serveur",
            502 => "Mauvaise passerelle",
            503 => "Service indisponible",
            504 => "Temps d'attente de la passerelle dépassé"
        ];

        return $libelles[$codeHttp] ?? "Erreur HTTP : " . $codeHttp;
    }

}
