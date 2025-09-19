-- réinitialisation des tables
-- suppression des données dans l'ordre des dépendances (contrainte de clé étrangère) évite le recours à set foreign_key_checks = 0;
delete from droit;
delete from fonction;
delete from administrateur;
delete from membre;


-- insertion des membres
-- réinitialisation du compteur
alter table membre auto_increment = 1;

insert into membre (nom, prenom, email, photo)
values ('VERGHOTE', 'GUY', 'guy.verghote@saint-remi.net', '0.png'),
       ('BAKASSI', 'SOULAIMANE',  'soulaimane.bakassi@saint-remi.net', 'bakassi soulaimane.jpg'),
       ('BERNARD', 'JULIEN',  'julien.bernard@saint-remi.net', 'bernard julien.jpg'),
       ('BOULARBI', 'MEDDHY',  'meddhy.boularbi@saint-remi.net', 'boularbi meddhy.jpg'),
       ('CARON', 'ADAM', 'adam.caron@saint-remi.net', 'caron adam.jpg'),
       ('CHARKAOUI', 'RAYANE',  'rayane.charkaoui@saint-remi.net', 'charkaoui rayane.jpg'),
       ('CHASTAGNER', 'ARTHUR',  'arthur.chastagner@saint-remi.net','chastagner arthur.jpg'),
       ('COULON', 'ALEXANDRE',  'alexandre.coulon@saint-remi.net', 'coulon alexandre.jpg'),
       ('DUBOIS', 'ALEXANDRE',  'alexandre.dubois@saint-remi.net', 'dubois alexandre.jpg'),
       ('JOSSE', 'THOMAS',  'thomas.josse@saint-remi.net', 'josse thomas.jpg'),
       ('LE CANU', 'MATHIS',  'mathis.le-canu@saint-remi.net', 'le canu mathis.jpg'),
       ('LION', 'ZIGGY',  'ziggy.lion@saint-remi.net', 'lion ziggy.jpg'),
       ('LONGBY', 'RENEDI',  'renedi.longby@saint-remi.net', 'longby renedi.jpg'),
       ('LOURDEL', 'MATHIS',  'mathis.lourdel@saint-remi.net', 'lourdel mathis.jpg'),
       ('MORALES', 'SIMON',  'simon.morales@saint-remi.net' , 'morales simon.jpg'),
       ('NEDELEC', 'FLORE', 'flore.nedelec@saint-remi.net', 'nedelec flore.jpg'),
       ('PARIS', 'THOMAS',  'thomas.paris@saint-remi.net', 'paris thomas.jpg'),
       ('RICHARD', 'TONNY',  'tonny.richard@saint-remi.net', null),
       ('RICHARD', 'TOM',  'tom.richard@saint-remi.net', 'richard tom.jpg'),
       ('ROELENS', 'GABRIEL',  'gabriel.roelens@saint-remi.net', 'roelens gabriel.jpg'),
       ('SOUKTANI', 'LEO',  'leo.souktani@saint-remi.net', 'souktani leo.jpg'),
       ('SUBERU', 'MOUBARAK',  'moubarak.suberu@saint-remi.net','suberu moubarak.jpg'),
       ('TISON', 'CLAIRE',  'claire.tison@saint-remi.net', 'tison claire.jpg');

INSERT INTO membre (nom, prenom, email, photo)
VALUES
    ('BALDE' ,'AISSATOU', 'aissatou.balde@saint-remi.net', 'balde aissatou.jpg'),
    ('BOILET', 'KAMERON', 'kameron.boilet@saint-remi.net', 'boilet kameron.jpg'),
    ('BOULLY', 'ALEXANDRE', 'alexandre.boully@saint-remi.net', 'boully alexandre.jpg'),
    ('CAZIN', 'TOM', 'tom.cazin@saint-remi.net', 'cazin tom.jpg'),
    ('DIANI', 'ISMAEL', 'ismael.diani@saint-remi.net', 'diani ismael.jpg'),
    ('DUMONT', 'HUGO', 'hugo.dumont@saint-remi.net', 'dumont hugo.jpg'),
    ('DUPRESSOIR', 'MATHIEU', 'mathieu.dupressoir@saint-remi.net', 'dupressoir mathieu.jpg'),
    ('FOULON', 'MATHIS', 'mathys.foulon@saint-remi.net', 'foulon mathis.jpg'),
    ('GARNIER', 'KYLLIAN', 'kyllian.garnier@saint-remi.net', 'garnier kyllian.jpg'),
    ('KARACA' , 'ATTILA', 'attila.karaca@saint-remi.net', 'karaca attila.jpg'),
    ('MARGOTIN', 'PAUL', 'paul.margotin@saint-remi.net', 'margotin paul.jpg'),
    ('MERCIER', 'ALEXI', 'alexi.mercier@saint-remi.net', 'mercier alexi.jpg'),
    ('MERVILLE', 'LUCAS', 'lucas.merville@saint-remi.net', 'merville lucas.jpg'),
    ('MORTELETTE', 'CLEMENT', 'clement.mortelette@saint-remi.net', 'mortelette clement.jpg'),
    ('NOUHI', 'MARWAN', 'marwan.nouhi@saint-remi.net', 'nouhi marwan.jpg'),
    ('ROUSELLE', 'ETIENNE', 'etienne.rouselle@saint-remi.net', 'rouselle etienne.jpg'),
    ('VASSEUR', 'LORENZO', 'lorenzo.vasseur@saint-remi.net', 'vasseur lorenzo.jpg'),
    ('YILDIZ', 'MUHAMMEDALI', 'muhammedali.yildiz@saint-remi.net', 'yildiz muhammedali.jpg'),
    ('ZON', 'JEREMY', 'jeremy.zon@saint-remi.net', 'zon jeremy.jpg');

-- modification du login et du mot de passe du membre 1 (modification du login interdite par le trigger sauf avec le jeton)
set @trigger = 1;
update membre set login = 'admin' where id = 1;
set @trigger = null;

-- insertion de l'administrateur
insert into administrateur values (1);

-- insertion des fonctions
insert into fonction (repertoire, nom)
values
    ('classement', 'Gérer les classements'),
    ('epreuve', 'Planifier les 4 saisons'),
    ('membre', 'Gérer les membres'),
    ('mention', 'Modifier les mentions légales'),
    ('photoinformation', 'Gérer les photos associées aux informations');
-- insertion des droits

-- attribution de tous les droits à l'administrateur 1
insert into droit
    select 1, repertoire
    from fonction;

select * from droit;

