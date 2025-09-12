<?php
declare(strict_types=1);

/**
 * Classe InputUrl : contrôle une URL
 * @Author : Guy Verghote
 * @Version : 2025.2
 * @Date : 14/06/2025
 */
 
class InputUrl extends Input
{
    // attribut permettant de préciser si l'on souhaite vérifier l'existence de l'url
    public bool $VerifierExistence = false;

    /**
     * Redéfinition de la méthode checkValidity
     * @return bool
     */
    public function checkValidity(): bool
    {
        $valide = true;

        if (!parent::checkValidity()) {
            $valide = false;
        } elseif ($this->Value !== null && $this->Value !== "") {
            $valeur = (string)$this->Value;
            if (!filter_var($valeur, FILTER_VALIDATE_URL)) {
                $this->validationMessage = "URL non valide";
                $valide = false;
            } elseif ($this->VerifierExistence) {
                $correct = $this->verifyUrlExistence($valeur);
                if (!$correct) {
                    $this->validationMessage = "ne correspond pas à une url existante";
                    $valide = false;
                }
            }
        }
        return $valide;
    }

    /**
     * Vérifie si l'url existe
     * @param string $url
     * @return bool
     */
    private function verifyUrlExistence(string $url): bool
    {
        $headers = @get_headers($url);
        return $headers && strpos($headers[0], '200') !== false;
    }

}
