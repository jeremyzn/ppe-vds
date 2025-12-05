-- Migration : ajout de la fonction 'partenaire' dans la table fonction
-- Chemin : data/migrations/ajout_fonction_partenaire.sql
-- Exécutez ce script dans votre base de données pour que la page d'administration des partenaires apparaisse
use ppe ;
INSERT INTO fonction (repertoire, nom)
VALUES ('partenaire', 'Partenaires');

-- Exemple : donner le droit à un administrateur (remplacez 1 par l'id du membre administrateur)
-- INSERT INTO droit (idAdministrateur, repertoire) VALUES (1, 'partenaire');

-- Vérification :
-- SELECT * FROM fonction WHERE repertoire = 'partenaire';
-- SELECT * FROM droit WHERE repertoire = 'partenaire';

-- Note : après exécution, rechargez la page /administration pour voir la tuile "Partenaires" dans "Mes fonctions".
