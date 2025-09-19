<?php

require $_SERVER['DOCUMENT_ROOT'] . '/backoffice/include/autoload.php';

// suppression du fichier
Journal::supprimer('erreur');

echo json_encode(['success' => "Le fichier a été supprimé avec succès"], JSON_UNESCAPED_UNICODE);

