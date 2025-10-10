<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// Contrôle sur le fichier téléversé
if (!isset($_FILES['fichier'])) {
    Erreur::envoyerReponse("Le fichier n'a pas été transmis", 'global');
}

// instanciation et paramétrage d'un objet InputFile
$file = new InputFile($_FILES['fichier'],Classement::getConfig());
// vérifie la validité du fichier
if (!$file->checkValidity()) {
    Erreur::envoyerReponse($file->getValidationMessage(), 'global');
}

// création d'un objetClassement pour réaliser les contrôles sur les données
$classement = new Classement();

// Les données ont-elles été transmises ?
if (!$classement->donneesTransmises()) {
    Erreur::envoyerReponse("Toutes les données attendues ne sont pas transmises", 'global');
}

// Toutes les données sont-elles valides ?
if (!$classement->checkAll()) {
    Erreur::envoyerReponse("Certaines données transmises ne sont pas valides", 'global');
}

// Alimentation de la colonne 'fichier' : sa valeur  est stockée dans la propriété  Value de  l'objet $file
$classement->setValue('fichier', $file->Value);

// Ajout dans la table document
$classement->insert();

// Récupération de l'identifiant du document ajouté
$id =  $classement->getLastInsertId();

// copie du fichier dans le répertoire de stockage
$ok = $file->copy();

// en cas d'échec (peu probable) il faut supprimer l'enregistrement créé afin de conserver une cohérence
if (!$ok) {
    $classement->delete($id);
    Erreur::envoyerReponse("L'ajout a échoué car le fichier PDF n'a pas pu être téléversé", 'global');
}

$reponse = ['success' => $id];
echo json_encode($reponse, JSON_UNESCAPED_UNICODE);

