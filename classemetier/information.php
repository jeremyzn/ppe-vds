<?php
declare(strict_types=1);
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
    public static function getAll(array $types = ["Publique"]): array
    {
        if (empty($types)) {
            $types = ["Publique"];
        }

        $db = Database::getInstance();

        $placeholders = [];
        $params = [];
        foreach ($types as $i => $t) {
            $key = 't' . $i;
            $placeholders[] = ':' . $key;
            $params[$key] = $t;
        }
        $in = implode(',', $placeholders);

        $sql = "SELECT i.*, GROUP_CONCAT(CONCAT(d.id, ':', d.fichier) SEPARATOR '|') AS documents
                FROM information i
                LEFT JOIN documentinformation d ON d.idInformation = i.id
                WHERE i.type IN (" . $in . ")
                GROUP BY i.id
                ORDER BY i.id DESC";

        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();

        $infos = [];
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $docs = [];
            if (!empty($row['documents'])) {
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
