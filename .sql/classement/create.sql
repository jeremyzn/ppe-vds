use ppe;

set default_storage_engine = InnoDb;
set foreign_key_checks = 1;

drop table if exists classement;


create table classement
(
    id        int auto_increment primary key,
    date      date         not null,
    titre     varchar(100) not null,
    fichier   varchar(100) not null,
    nbDemande int          not null default '0'
)