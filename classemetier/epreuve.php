<?php

// table epreuve (id, saison, description, date)


class Epreuve extends table
{
    public function __construct()
    {
        // appel du contructeur de la classe parent
        parent::__construct('epreuve');

        // définition de la clé primaire si différente de id
        $this->idName = 'saison';

        // la saison
        $input = new InputText();
        $input->Require = true;

        // la date de l'épreuve doit être renseignée et se situer dans l'année à venir
        $input = new InputDate();
        $input->Min = date("Y-m-d");
        $input->Max = date('Y-m-d', strtotime('+ 1 year + 3 month'));
        $this->columns['date'] = $input;

        // la description
        $input = new InputTextarea();
        $input->Require = false;
        $this->columns['description'] = $input;

        $input = new InputDate();
        $input->Require = true;
        $this->columns['dateOuverture'] = $input;

    }

    public static function getAll()
    {
        $sql = "Select saison, date, description From epreuve;";
        $select = new Select();
        return $select->getRows($sql);
    }

    /**
     * Retourne l'épreuve à venir
     * @return array|false
     */
    public static function getProchaineEpreuve()
    {
        $sql = <<<SQL
            Select date, description, saison
            From epreuve
            where date >= curdate() 
            order by date 
            limit 1;
SQL;
        $select = new Select();
        return $select->getRow($sql);
    }
}

