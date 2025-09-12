<?php
// Récupération des options du menu vertical
$lesOptions = file_get_contents(RACINE . '/backoffice/.config/menuvertical.json');

?>
<!DOCTYPE HTML>
<html lang="fr">
<head>
    <title>Amicale du Val de Somme</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="data:;base64,iVBORw0KGgo=">
    <link rel="stylesheet" href="/composant/bootstrap/bootstrap.min.css">
    <script src="/composant/bootstrap/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/css/style.css">
    <script type='module'>
        // Initialisation du menu vertical
        import {initialiserMenuVertical} from "/composant/menuvertical/menu.js";
        initialiserMenuVertical(<?=$lesOptions;?>, 230);
    </script>
    <?php
    // chargement du fichier js de la page
    // récupération du nom du fichier php appelant cela va permettre de charger l'interface correspondante
    // fichier html portant le même nom ou le fichier php de même nom dans le dossier interface
    if (!isset($file)) {
        $file = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
    }
    if (is_file("$file.js")) {
        $v = filemtime("$file.js");
        echo "<script type='module' src='$file.js?t=$v'></script>";
    }
    if (isset($head)) {
        echo $head;
    }
    ?>
</head>
<body>
<main>
    <div style="margin-left: 45px; margin-right: 5px; margin-top: 5px;">
        <?php
        if (is_file("$file.html")) {
            require "$file.html";
        }
        ?>
    </div>
</main>
</body>
</html>
