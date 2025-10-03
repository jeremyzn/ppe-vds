<?php
declare(strict_types=1);

/**
 * Classe Document
 * Cette classe permet de gérer les documents de l'application (id, titre, type, fichier).
 * Elle hérite de la classe Table pour les opérations de base de données.
 */


class Document extends table
{
    public function __construct()
    {
        parent::__construct('document');
    }

    public function getAll(): array
    {
        $sql = <<<SQL
    select id, titre, type , fichier from Document;
SQL;
        $select = new Select();
        return $select->getRows($sql);
    }
}

