set default_storage_engine = InnoDb;
set foreign_key_checks = 1;

drop table if exists droit;
drop table if exists administrateur;
drop table if exists membre;
drop table if exists fonction;


create table membre
(
    id              int auto_increment primary key,
    login           varchar(50)  null unique,
    password        varchar(64)  not null,
    nom             varchar(30)  not null,
    prenom          varchar(50)  not null,
    email           varchar(100) not null,
    telephone       varchar(10)  null,
    photo           varchar(100) null,
    autMail         tinyint(1)   not null default '0',
    unique (nom, prenom, email)
);


-- Utilisation de la définition inline des contraintes pour la table administrateur
-- Inconvénient : on ne peut pas nommer la contrainte
-- Avantage : écriture plus condensée
create table administrateur
(
    id int primary key references membre (id) on delete cascade on update cascade
);


create table fonction
(
    repertoire varchar(50)  not null,
    nom        varchar(150) not null,
    constraint pk_fonction primary key (repertoire)
);

create table droit
(
    idAdministrateur int not null,
    repertoire       varchar(50) not null,
    constraint pk_droit primary key (idAdministrateur, repertoire),
    constraint fk_droit_administrateur foreign key (idAdministrateur) references administrateur (id) on delete cascade,
    constraint fk_droit_fonction foreign key (repertoire) references fonction (repertoire) on delete cascade on update cascade
);

