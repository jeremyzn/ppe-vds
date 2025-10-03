<?php
Structure::verifier();

// récupération du contenu de la page mentions légales et de la politique de confidentialité
// pour l'affichage dans le pied de page
$mention = Page::getMentions();
$politique = Page::getPolitique();

?>

<!DOCTYPE HTML>
<html lang="fr">
<head>
    <title>PPE VDS</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="data:,">
    <!-- Script Bootstrap  -->
    <link rel="stylesheet"  href="/composant/bootstrap/bootstrap.min.css">
    <script src="/composant/bootstrap/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/css/style.css">
    <?php
    // récupération du nom du script php appelé afin de charger le fichier js de même nom
    $file = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
    if (is_file("$file.js")) {
        $v = filemtime("$file.js");
        echo "<script type='module' src='$file.js?t=$v'></script>";
    }
    // chargement des données et composants spécifiques de la page si nécessaire
    if (isset($head)) {
        echo $head;
    }
    ?>
</head>
<body>
    <header>
        <?php require __DIR__ . '/header.php' ?>
    </header>
    <main>
        <?php
        // chargement de l'interface de la page
        if (is_file("$file.html")) {
            require "$file.html";
        }
        ?>
    </main>
    <footer id="pied">
        <?php require __DIR__ . '/footer.php' ?>
    </footer>
</body>
</html>
