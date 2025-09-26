<?php

/**
 * Classe gérant les informations publiées sur le site
 * Gestion de la table 'information' avec les colonnes :
 *  - id : identifiant unique
 *  - titre : titre de l'information
 *  - contenu : contenu de l'information
 *  - type : type d'information (Publique, Privée)
 *  - auteur : nom et prénom de l'auteur
 */
class Information extends Table
{
    public function __construct()
    {
        // appel du constructeur de la classe parent
        parent::__construct('information');

        // le titre
        $input = new InputText();
        $input->Require = true;
        $this->columns['titre'] = $input;

        // le contenu
        $input = new InputTextarea();
        $input->Require = true;
        $this->columns['contenu'] = $input;

        // le type
        $input = new InputList();
        $input->Require = true;
        // InputList expose la propriété Values
        $input->Values = ['Publique', 'Privée'];
        $this->columns['type'] = $input;

        // l'auteur
        $input = new InputText();
        $input->Require = true;
        // si un membre est connecté, renseigner automatiquement l'auteur depuis la session
        if (isset($_SESSION['membre'])) {
            $prenom = $_SESSION['membre']['prenom'] ?? '';
            $nom = $_SESSION['membre']['nom'] ?? '';
            $input->Value = trim($prenom . ' ' . $nom);
        }
        $this->columns['auteur'] = $input;
    }

    /**
     * Retourne toutes les informations selon les types demandés
     * @param array $types Types d'informations à récupérer
     * @return array Tableau des informations avec leurs documents associés
     */
    public static function getAll($types = ["Publique"]) {
        if (empty($types)) {
            $types = ["Publique"];
        }
        $pdo = Database::getInstance();
        $infos = [];
        $sql = "SELECT i.*, GROUP_CONCAT(d.id) AS documents
                FROM information i
                LEFT JOIN documentinformation d ON d.idInformation = i.id
                WHERE i.type IN ('" . implode("','", $types) . "')
                GROUP BY i.id
                ORDER BY i.id DESC";
        foreach ($pdo->query($sql) as $row) {
            $row['documents'] = $row['documents'] ? explode(',', $row['documents']) : [];
            $infos[] = $row;
        }
        return $infos;
    }
}
