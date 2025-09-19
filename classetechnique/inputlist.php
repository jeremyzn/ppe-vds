<?php
declare(strict_types=1);

/**
 * Classe InputList : contrôle une valeur qui doit se trouver dans un ensemble de valeur
 * @Author : Guy Verghote
 * @Version : 2025.1
 * @Date : 03/05/2025
 */
class InputList extends Input
{
    // Tableau contenant les valeurs autorisées
    public array $Values = [];

    // Indique s'il faut mettre la valeur en majuscule 'U', en minuscule 'L' ou la laisser telle quel
    public string $Casse = '';

    public function checkValidity(): bool
    {
        if (!parent::checkValidity()) return false;

        if ($this->Value != null) {
            // mise en forme demandée
            if ($this->Casse === 'U') {
                $this->Value = strtoupper($this->Value);
            } elseif ($this->Casse === 'L') {
                $this->Value = strtolower($this->Value);
            }
            // La valeur fait-elle partie des valeurs de la liste
            if (!in_array($this->Value, $this->Values)) {
                $this->validationMessage = "Veuillez entrer une des valeurs acceptées";
                return false;
            }
        }
        return true;
    }
}