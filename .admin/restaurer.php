<?php
// restauration sécurisée du dépôt local depuis GitHub

date_default_timezone_set('Europe/Paris');

// Se placer à la racine du projet
chdir(__DIR__ . '/../');

// --- Récupération de l’état distant ---
echo "Récupération de l’état distant (git fetch)\n";
exec('git fetch');

// --- Analyse de l’état local/distant ---
echo "Analyse de l’état local vs distant : git status -sb\n";
exec('git status -sb', $statusOutput);
$etat = $statusOutput[0] ?? '';
echo "Statut actuel : $etat\n";

// --- Cas bloquant : commits locaux non poussés ---
if (str_contains($etat, '[ahead')) {
    echo "Le dépôt local contient des commits non présents sur GitHub.\n";
    echo "Veuillez d'abord exécuter : git push\n";
    exit(1);
}

// --- Vérification des modifications locales non commités ---
echo "Vérification des modifications locales : git status --porcelain\n";
exec('git status --porcelain', $modifsLocales);
if (!empty($modifsLocales)) {
    echo "Des fichiers ont été modifiés localement mais pas commités.\n";
    echo "Ces changements seront perdus si un conflit survient pendant le pull.\n";
    echo "Souhaitez-vous poursuivre malgré tout ? (o/n) : ";
    $confirmation = strtolower(trim(fgets(STDIN)));
    if ($confirmation !== 'o') {
        echo "Restauration annulée. Vous pouvez faire un commit ou push manuel si nécessaire.\n";
        exit(1);
    }
} else {
    echo "Aucun changement local à sauvegarder.\n";
}

// --- Vérification du suivi distant (tracking) ---
$currentBranch = trim(shell_exec('git symbolic-ref --short HEAD'));
exec("git rev-parse --abbrev-ref --symbolic-full-name @{u} 2>NUL", $output, $trackingStatus);

if ($trackingStatus !== 0) {
    echo "La branche '$currentBranch' n’est pas liée à une branche distante.\n";
    echo "Définition du suivi vers origin/$currentBranch...\n";
    exec("git branch --set-upstream-to=origin/$currentBranch $currentBranch", $setTrackOutput, $setTrackCode);
    if ($setTrackCode !== 0) {
        echo "Echec de la définition du suivi distant :\n";
        echo implode("\n", $setTrackOutput) . "\n";
        exit(1);
    }
}

// --- Pull avec rebase ---
echo "Synchronisation avec le dépôt GitHub : git pull --rebase\n";
exec('git pull --rebase', $pullResult, $pullCode);

$date = date('d/m/Y à H:i');
if ($pullCode === 0) {
    echo "Mise à jour réussie le $date\n";
} else {
    echo "Echec du pull (rebase). Conflits ou erreur réseau ?\n";
    echo implode("\n", $pullResult) . "\n";
    exit(1);
}
