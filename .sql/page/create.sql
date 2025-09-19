use ppe;

set default_storage_engine = innodb;

drop table if exists page;

create table page
(
    id      int          auto_increment primary key,
    nom     varchar(50)  not null unique,
    contenu text  not null
);

