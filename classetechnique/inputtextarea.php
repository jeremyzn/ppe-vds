<?php
declare(strict_types=1);

/**
 * Classe InputTextarea : contrôle une chaine de caractères
 * @Author : Guy Verghote
 * @Version : 2025.1
 * @Date : 03/05/2025
 */
class InputTextarea extends Input
{
// encoder les balises
    public $EncoderHtml = false;
    public $AcceptHtml = true;
    public $lesBalisesAutorisees = ['<br>', '<span>', '<b>', '<i>', '<strong>', '<ul>', '<li>', '<img>', '<a>', '<div>'];

    public function checkValidity(): bool
    {
        if (!parent::checkValidity()) return false;

        if ($this->Value !== null && $this->Value !== "") {
            $valeur = (string)$this->Value;
            // Si la saisie utilise un Rich text Editor, il n'y a pas de danger : les caractères sont remplacées par leur code HTML (htmlSpecialchar)
            // le test suivant ne sera alors jamais vrai
            // la valeur ne doit pas contenir de balise script : la balise script ainsi que son contenu doivent être éliminés
            if (preg_match('/<script|drop|select|insert|delete|update|--|\/\*|\*\//i', $this->Value)) {
                $this->validationMessage = "La valeur contient des caractères ou des mots interdits.";
                return false;
            }
            if ($this->EncoderHtml)
                $this->Value = htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8');
            else {
                if (!$this->AcceptHtml) {
                    $this->Value = strip_tags($valeur, $this->lesBalisesAutorisees);
                }
            }
        }
        return true;
    }
}
