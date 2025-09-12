<?php

// gestion de la table page(id, nom, contenu)
/*
1 Présentation du club
3 Présentation des 4 saisons
5 Mentions légales
6 Politique de confidentialité
*/

// Seule la modification du contenu est possible

class Page
{
    /**
     * Retourne le contenu de la page présentation du club
     * @return string
     */
    public static function getClub(): string
    {
        $select = new select();
        return $select->getValue("select contenu from page where id = 1;");
    }

    /**
     * Retourne le contenu de la page présentation des 4 saisons
     * @return string
     */
    public static function get4Saisons(): string
    {
        $select = new select();
        return $select->getValue("select contenu from page where id = 3;");
    }

    /**
     * Retourne le contenu de la page mentions légales
     * @return string
     */
    public static function getMentions(): string
    {
        $select = new select();
        return $select->getValue("select contenu from page where id = 5;");
    }

    /**
     * Retourne le contenu de la page politique de confidentialité
     * @return string
     */
    public static function getPolitique(): string
    {
        $select = new select();
        return $select->getValue("select contenu from page where id = 6;");
    }

    /*
  * Modifie le contenu de la page Mentions légales
  * @param string $contenu
  */
    public static function modifierMentions($contenu)
    {
        $db = Database::getInstance();
        $cmd = $db->prepare("update page set contenu = :contenu where id = 5");
        $cmd->execute(["contenu" => $contenu]);
    }

}
