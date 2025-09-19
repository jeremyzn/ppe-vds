<?php
// La page erreur/index.php est appelée en cas de détection d'une erreur,
// elle doit charger ses propres ressources.
// Démarrage de la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// récupération des informations sur l'erreur
$message = isset($_SESSION['erreur']['message']) ? $_SESSION['erreur']['message'] : 'Erreur inconnue';
// destruction de la variable de session
unset($_SESSION['erreur']);
?>
<!DOCTYPE HTML>
<html lang="fr">
<head>
    <title>Erreur</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="data:,">
    <link href="style.css" rel="stylesheet">
</head>
<body>
<div class="card">
    <div class="card-header">Avertissement</div>
    <div class="card-body">
        <?= htmlspecialchars($message) ?> <!-- Affichage sécurisé du message d'erreur -->
    </div>
    <div class="card-footer">
        <a href="/">Revenir à la page d'accueil</a>
    </div>
</div>
</body>
</html>
