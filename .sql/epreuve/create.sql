use ppe;

set default_storage_engine = InnoDb;
set foreign_key_checks = 1;

drop table if exists epreuve;

create table epreuve (
  saison enum('Hiver','Printemps','Été','Automne') primary key,
  description text not null,
  date date not null
);

