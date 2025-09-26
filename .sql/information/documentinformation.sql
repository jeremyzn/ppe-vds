use ppe;

CREATE TABLE documentinformation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fichier VARCHAR(255) NOT NULL,
    idInformation INT NOT NULL,
    FOREIGN KEY (idInformation) REFERENCES information(id) ON DELETE CASCADE
);
