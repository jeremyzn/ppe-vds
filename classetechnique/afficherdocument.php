<?php
// Connexion à la base
require_once 'config.php'; // contient la connexion PDO $pdo

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit('ID manquant');
}

$id = (int)$_GET['id'];

// Récupérer le document
$stmt = $pdo->prepare('SELECT fichier, type FROM document WHERE id = ?');
$stmt->execute([$id]);
$doc = $stmt->fetch();

if (!$doc) {
    http_response_code(404);
    exit('Document non trouvé');
}

$filepath = __DIR__ . '/data/document/' . $doc['fichier'];

if (!file_exists($filepath)) {
    http_response_code(404);
    exit('Fichier non trouvé');
}

// Définir le type MIME
$mime = mime_content_type($filepath);
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . basename($filepath) . '"');
readfile($filepath);
exit;
?><?php
