<?php
if (!isset($titre)) {
    $titre = ucwords(trim(dirname($_SERVER['PHP_SELF']), '/'));
}
?>
<div class="d-flex justify-content-between align-items-center">
    <!-- Bloc de gauche : liens de navigation -->
    <div>
        <?php if ($titre !== "Site Du VDS") : ?>
            <a href="/" class="me-2 text-decoration-none" title="Page d'accueil">🏠</a>
        <?php endif; ?>
        <?php
        // Construction des liens de session
        if (isset($_SESSION['membre'])) {
            $id = $_SESSION['membre']['id'];
            $prenom = ucwords(strtolower($_SESSION['membre']['prenom']));
            echo '<span class="me-2 masquer"> Bienvenue, ' . $prenom . '</span>';
            echo '<a href="/membre/profil" class="me-2 masquer" >Mon espace</a>';
            echo '<a href="/membre/annuaire" class="me-2">Annuaire membre</a>';
            if (Administrateur::estUnAdministrateur($id)) {
                echo '<a href="/administration" class="me-2">Mes fonctions</a>';
            }
            echo ' <a href="/membre/deconnexion" class="me-2">Se déconnecter</a>';
        } else {
            echo ' <a href="/connexion" class="me-2">Se connecter</a>';
        }
        ?>
    </div>
    <!-- Bloc de droite : le titre -->
    <div class="text-end fw-bold masquer">
        <?= htmlspecialchars($titre) ?>
    </div>
</div>
