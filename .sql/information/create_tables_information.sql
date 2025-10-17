-- Script de création des tables pour le module "information"
CREATE TABLE IF NOT EXISTS `information` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `titre` VARCHAR(255) NOT NULL,
  `chapeau` TEXT DEFAULT NULL,
  `contenu` LONGTEXT NOT NULL,
  `type` ENUM('Publique','Privée') NOT NULL DEFAULT 'Publique',
  `auteur` VARCHAR(255) DEFAULT NULL,
  `date_creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modif` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `documentinformation` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fichier` VARCHAR(512) NOT NULL,
  `idInformation` INT NOT NULL,
  `nom_original` VARCHAR(255) DEFAULT NULL,
  `date_upload` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`idInformation`) REFERENCES `information`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
