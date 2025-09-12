<?php
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';
Page::modifierMentions($_POST['contenu']);
echo json_encode(['success' => 'Modification prise en compte']);
