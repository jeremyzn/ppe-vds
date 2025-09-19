<?php
declare(strict_types=1);

/**
 * Classe InputEmail : contrôle une adresse email
 * @Author : Guy Verghote
 * @Version : 2025.1
 * @Date : 03/05/2025
 */

class InputEmail extends Input
{
    public int $MaxLength;

    public function checkValidity(): bool
    {
        if (!parent::checkValidity()) {
            return false;
        }
        if ($this->Value !== null && $this->Value !== "") {
            $valeur = (string)$this->Value;
            // ancienne solution
            // $correct = preg_match("/^[0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-_.]?[0-9a-z])*\.[a-z]{2,4}$/i", $valeur);
            // nouvelle solution à l'aide de la fonction filter_var
            $correct = filter_var($valeur, FILTER_VALIDATE_EMAIL);
            if (!$correct) {
                $this->validationMessage = "non respect du format attendu ";
                return false;
            }
            // vérification de l'existence du domaine
            $domaine = substr(strrchr($valeur, "@"), 1);
            if (!checkdnsrr($domaine, "MX")) {
                $this->validationMessage = "Ce domaine n'existe pas";
                return false; // Le domaine ou les enregistrements MX n'existent pas
            }
            // respect de la longueur maximale
            if (isset($this->MaxLength)) {
                if (strlen($valeur) > $this->MaxLength) {
                    $this->validationMessage = "L'adresse mail ne doit pas depasser " . $this->MaxLength . " caractères";
                    return false;
                }
            }
            $this->Value = $valeur;
        }
        return true;
    }
}
