set default_storage_engine = InnoDb;
set foreign_key_checks = 1;
use ppe;

drop table if exists Document;


CREATE TABLE Document (
                          id INT PRIMARY KEY,
                          titre VARCHAR(255) NOT NULL,
                          type ENUM('Club', '4 saisons', 'Membre', 'Public') NOT NULL,
                          fichier VARCHAR(255) NOT NULL,
                          CONSTRAINT chk_titre CHECK (
                              titre REGEXP '^[A-Za-z0-9 ,\'-]+[!?]?$'
                              ),
                          CONSTRAINT chk_fichier CHECK (
                              fichier REGEXP '^[a-z0-9 ]+\.pdf$'
                              )
);
