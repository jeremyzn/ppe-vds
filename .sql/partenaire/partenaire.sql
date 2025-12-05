-- Migration : création de la table `partenaire`
-- Chemin : data/migrations/partenaire.sql
-- But : stocker les partenaires et le nom du fichier logo
use ppe;
drop table if exists partenaire;
CREATE TABLE IF NOT EXISTS partenaire (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(255) NOT NULL,
  url VARCHAR(255) DEFAULT NULL,
  fichier VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Remarques :
-- - Le champ `fichier` contient le nom du fichier (ex: "logo_exemple.png").
-- - Les fichiers devront être stockés dans le répertoire data/partenaire/.
-- - Contraintes applicatives attendues : extension jpg/png, hauteur max 100px, taille max 30Ko.
