<?php
date_default_timezone_set('Europe/Paris');

// Aller à la racine du projet
chdir(__DIR__ . '/..');

echo "Sauvegarde du dépôt local vers GitHub en cours...\n";

// Désactiver les messages d'avertissement sur les fichiers ignorés
exec('git config advice.addIgnoredFile false');

// Configurer Git pour créer automatiquement le lien de suivi distant lors du premier push
exec('git config --global push.autoSetupRemote true');

// Étape 1 : Récupération de l’état distant
echo "Vérification de la synchronisation avec la branche distante : git fetch\n";
exec('git fetch', $outputFetch, $codeFetch);

if ($codeFetch !== 0) {
    echo "Erreur lors de la récupération de l'état distant (git fetch).\n";
    echo "Détails :\n" . implode("\n", $outputFetch) . "\n";
    exit(1);
}

// Étape 2 : Analyse du statut local vs distant
echo "Analyse du statut local vs distant : git status -sb\n";
exec('git status -sb', $statusOutput, $statusCode);

if ($statusCode !== 0 || empty($statusOutput)) {
    echo "Erreur lors de l’analyse du statut du dépôt.\n";
    echo "Détails :\n" . implode("\n", $statusOutput) . "\n";
    exit(1);
}

$etat = $statusOutput[0];

if (str_contains($etat, '[behind')) {
    echo "Le dépôt local est en retard sur le dépôt distant.\n";
    echo "Veuillez d’abord exécuter : git pull\n";
    exit;
} elseif (str_contains($etat, '[ahead')) {
    echo "Le dépôt local est en avance. Les changements peuvent être poussés.\n";
} else {
    echo "Le dépôt est actuellement synchronisé avec le dépôt distant.\n";
}

// Étape 3 : Construction du message de commit
$date = date('d/m/Y à H:i');
$commitMessage = "Dernière sauvegarde le $date";

// Étape 4 : Ajout des fichiers
echo "Ajout des fichiers modifiés : git add .\n";
exec('git add .');

// Étape 5 : Commit horodaté
echo "Commit en cours : git commit -m \"$commitMessage\"\n";
exec("git commit -m \"$commitMessage\"", $commitResult, $commitCode);
if ($commitCode !== 0) {
    echo "Aucun commit créé car aucune modification n’est détectée.\n";
    echo "Aucun changement à committer, la sauvegarde est donc inutile.\n";
    exit(0);
}

// Étape 6 : Push vers GitHub
echo "Envoi vers le dépôt distant : git push\n";
exec('git push', $pushOutput, $pushCode);

// Si push échoue à cause de l’absence d’upstream, on l’ajoute automatiquement
if ($pushCode !== 0 && str_contains(implode("\n", $pushOutput), 'has no upstream branch')) {
    echo "Aucun lien de suivi distant. Ajout automatique avec : git push --set-upstream origin main\n";
    exec('git push --set-upstream origin main', $pushOutput, $pushCode);
}

if ($pushCode === 0) {
    echo "Sauvegarde réussie le $date\n";
} else {
    echo "Erreur lors du push. Vérifiez les conflits ou votre connexion.\n";
    echo "Détails :\n" . implode("\n", $pushOutput) . "\n";
}
