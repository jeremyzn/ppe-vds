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
        $input->Values = ['Publique', 'Privée'];
        $this->columns['type'] = $input;

        // l'auteur
        $input = new InputText();
        $input->Require = true;
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
        $sql = "SELECT i.*, GROUP_CONCAT(CONCAT(d.id, ':', d.fichier) SEPARATOR '|') AS documents
                FROM information i
                LEFT JOIN documentinformation d ON d.idInformation = i.id
                WHERE i.type IN ('" . implode("','", $types) . "')
                GROUP BY i.id
                ORDER BY i.id DESC";
        foreach ($pdo->query($sql) as $row) {
            // Parser les documents pour créer un tableau d'objets avec id et fichier
            $docs = [];
            if ($row['documents']) {
                foreach (explode('|', $row['documents']) as $doc) {
                    if (strpos($doc, ':') !== false) {
                        list($id, $fichier) = explode(':', $doc, 2);
                        $docs[] = ['id' => $id, 'fichier' => $fichier];
                    }
                }
            }
            $row['documents'] = $docs;
            $infos[] = $row;
        }
        return $infos;
    }
}
