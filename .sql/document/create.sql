set default_storage_engine = InnoDb;
set foreign_key_checks = 1;
use ppe;

drop table if exists Document;


CREATE TABLE Document
(
    id      INT PRIMARY KEY auto_increment                NOT NULL  ,
    titre   VARCHAR(255)                                   NOT NULL,
    type    ENUM ('Club', '4 saisons', 'Membre', 'Public') NOT NULL,
    fichier VARCHAR(255)                                   NOT NULL
#     CONSTRAINT chk_titre CHECK (
#         titre REGEXP '^[A-Za-z0-9 ,\'-]+[!?]?$'
#         ),
#     CONSTRAINT chk_fichier CHECK (
#         fichier REGEXP '^[a-z0-9 ]+\.pdf$'
#         )
);


insert into document (titre, type, fichier)
values ('Autorisation parentale 4 saisons', '4 saisons', 'Autorisation parentale 4 saisons.pdf'),
       ('Autorisation parentale pour l''adhésion', 'Club', 'Autorisation parentale pour adhesion.pdf'),
       ('Les minimas pour les championnats de France', 'Public', 'Les minimas pour les championnats de France.pdf'),
       ('Parcours du 10 Km', '4 saisons', 'Parcours du 10 Km.pdf'),
       ('Parcours du 5 Km', '4 saisons', 'Parcours du 5 Km.pdf'),
       ('Règlement des 4 saisons', '4 saisons', 'Reglement des 4 saisons.pdf'),
       ('Règlement intérieur', 'Club', 'Reglement interieur.pdf'),
       ('Statuts VDS adoptés en AG du 19/11/2021', 'Club', 'STATUTS VDS.pdf'),
       ('Tableau des allures pour les séances de VS', 'Membre', 'Tableau des allures pour séances de VS.pdf'),
       ('Tableau des allures pour les sorties longues', 'Membre', 'Tableau des allures pour les sorties longues.pdf'),
       ('Tableau pour séance VMA', 'Membre', 'Tableau pour seance VMA.pdf');