use ppe;

CREATE TABLE information (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    type ENUM('Publique', 'Privée') NOT NULL,
    auteur VARCHAR(255) NOT NULL
);
