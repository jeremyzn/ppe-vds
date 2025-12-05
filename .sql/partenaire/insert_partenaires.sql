-- Insert exemples pour la table partenaire
-- Cheminer les fichiers dans data/partenaire/ puis exécuter ce script
use ppe;

delete from partenaire;
INSERT INTO partenaire (nom, url, fichier) VALUES
('Ville d\'Amiens', 'https://www.amiens.fr', 'amiens.png'),
('Amiens se prend au jeu 2024', NULL, 'amiensseprendaujeu2024.png'),
('CD80', 'http://cda80.athle.com/', 'cd80.png'),
('Courses80', 'https://courses80.fr/', 'courses80.png'),
('FFA', 'https://www.athle.fr', 'ffa.png'),
('Haut de France', 'https://www.hautsdefrance.fr', 'hautdefrance.png'),
('Klik & Go', 'https://www.klikego.com', 'klikego.jpg'),
('Running', NULL, 'running.jpg'),
('Somme', 'https://www.somme.fr', 'somme.png');
