<?php
if (!isset($titre)) {
    $titre = ucwords(trim(dirname($_SERVER['PHP_SELF']), '/'));
}
?>
<div class="d-flex justify-content-between align-items-center w-100">
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
            // Menu déroulant "Mon espace"
            echo '
            <div class="dropdown d-inline me-2">
                <a class="dropdown-toggle text-decoration-none" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Mon espace
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item  text-primary" href="/membre/profil/">👤 Profil</a></li>
                    <li><a class="dropdown-item text-primary " href="/membre/photo/">📷 Photo</a></li>
                    <li><a class="dropdown-item text-primary" href="/membre/annuaire/">👥 Annuaire</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/membre/deconnexion/">🚪 Déconnexion</a></li>
                </ul>
            </div>';
            if (Administrateur::estUnAdministrateur($id)) {
                $mesFonctions = Administrateur::getLesFonctionsAutorisees($_SESSION['membre']['id']);
                // Menu déroulant "Mon espace"
                echo '
            <div class="dropdown d-inline me-2">
                <a class="dropdown-toggle text-decoration-none" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Mes fonctions
                </a>
                <ul class="dropdown-menu">';
                foreach ($mesFonctions as $fonction) {
                    echo '<li><a class="dropdown-item text-primary"  href="/administration/' 
                            . htmlspecialchars($fonction['repertoire']) . '">' . htmlspecialchars($fonction['nom']) . '</a></li>';
                }
                echo '
                    </ul>
                </div>'; 
            }
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
