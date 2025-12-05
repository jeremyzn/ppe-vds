<?php
class Inscription extends Table
{
    public function traiterAjout($post)
    {
      
        if ($post['date_ouverture'] >= $post['date_cloture']) {
            return "Erreur : La date d'ouverture doit être avant la date de clôture.";
        }

        if ($post['date_ouverture'] < date('Y-m-d')) {
            return "Erreur : La date d'ouverture ne peut pas être passée.";
        }

        $donneesPourBDD = [
            "nom"             => $post['nom'],
            "dateEpreuve"     => $post['date_epreuve'],    
            "dateOuverture"   => $post['date_ouverture'],
            "dateCloture"     => $post['date_cloture'],
            "lienInscription" => $post['lien_inscription'],
            "lienInscrit"     => $post['lien_inscrits']
        ];

        $this->add($donneesPourBDD);
        
        return "OK";
    }
}
// Message vide au début
$message = ""; 

// Si le formulaire est envoyé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $inscription = new Inscription();
    
    // On lance le traitement et on récupère le résultat
    $resultat = $inscription->traiterAjout($_POST);

    if ($resultat === "OK") {
        $message = "<p style='color:green'>Course ajoutée avec succès !</p>";
    } else {
        // Sinon, c'est une erreur, on l'affiche
        $message = "<p style='color:red'>erreur : $resultat</p>";
    }
}
?>

<?= $message ?>

<form method="POST">
    Nom : <br>
    <input type="text" name="nom" required value="<?= $_POST['nom'] ?? '' ?>"><br><br>

    Date épreuve : <br>
    <input type="date" name="date_epreuve" required value="<?= $_POST['date_epreuve'] ?? '' ?>"><br><br>

    Date ouverture : <br>
    <input type="date" name="date_ouverture" required value="<?= $_POST['date_ouverture'] ?? '' ?>"><br><br>

    Date clôture : <br>
    <input type="date" name="date_cloture" required value="<?= $_POST['date_cloture'] ?? '' ?>"><br><br>

    Lien inscription : <br>
    <input type="text" name="lien_inscription" required value="<?= $_POST['lien_inscription'] ?? '' ?>"><br><br>

    Lien inscrits : <br>
    <input type="text" name="lien_inscrits" required value="<?= $_POST['lien_inscrits'] ?? '' ?>"><br><br>

    <button type="submit">Ajouter</button>
</form>
?>