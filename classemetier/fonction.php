<?php
declare(strict_types=1);
// définition de la table fonction : repertoire nom
// clé primaire : repertoire

class Fonction extends Table
{
    public function __construct()
    {
        // appel du contructeur de la classe parent
        parent::__construct('fonction');

        // définition de la clé primaire si différente de id
        $this->idName = 'repertoire';

        // création des colonnes de la table
        // colonne repertoire
        $input = new InputText();
        $input->Require = true;
        $input->MaxLength = 50;
        $input->MinLength = 4;
        $input->Pattern = '^[a-zA-Z0-9]{4,50}$';
        $this->columns['repertoire'] = $input;

        // colonne nom
        $input = new InputText();
        $input->Require = true;
        $input->MaxLength = 150;
        $input->MinLength = 10;
        $input->Pattern = "^[A-Za-zÀ-ÖØ-öø-ÿ0-9](?:[A-Za-zÀ-ÖØ-öø-ÿ0-9' ]*[A-Za-zÀ-ÖØ-öø-ÿ0-9])?$";
        $this->columns['nom'] = $input;
    }


    /**
     * Retourne l'ensemble de pages
     * @return array
     */
    public static function getAll() : array
    {
        $sql = <<<SQL
          Select repertoire, nom 
          from fonction
          order by repertoire;
SQL;
        $select = new Select();
        return $select->getRows($sql);
    }
}
