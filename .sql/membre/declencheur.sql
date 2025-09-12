use ppe;

drop trigger if exists avantAjoutMembre;
drop trigger if exists avantModificationMembre;

create trigger avantAjoutMembre before insert on membre
for each row
begin
    # déclaration des variables
    declare nb INT;
    declare nouveauLogin varchar(50);
    declare baseLogin varchar(50);
    declare msg varchar(255);

    set new.nom = ucase(new.nom);

    if new.nom not regexp '^[A-Z]( ?[A-Z])*$' then
        set msg = concat('~Le nom ', new.nom, ' ne respecte pas le format attendu');
        signal sqlstate '45000' set message_text = msg;
    end if;

    -- Contrôle sur la colonne prenom
    set new.prenom = ucase(new.prenom);

    if new.prenom not regexp '^[A-Z]( ?[A-Z])*$' then
        set msg = concat('~Le prénom ', new.prenom, ' ne respecte pas le format attendu');
        signal sqlstate '45000' set message_text = msg;
    end if;

    -- Contrôle sur la colonne email
    if new.email not regexp '^[0-9a-zA-Z]([-_\.]?[0-9a-zA-Z])*@[0-9a-zA-Z]([-_\.]?[0-9a-zA-Z])*[\.][a-zA-Z]{2,4}$' then
        set msg = concat('~L''email ne respecte pas le format attendu : ', new.email);
        signal sqlstate '45000' set message_text = msg;
    end if;

    -- Contrôle sur la colonne telephone si le champ est renseigné
    if new.telephone is not null then
        if new.telephone not regexp '^0[1-79][0-9]{8}$' then
            set msg = concat('~Le numéro de téléphone ne respecte pas le format attendu : ', new.telephone);
            signal sqlstate '45000' set message_text = msg;
        end if;
    end if;

    -- Contrôle de l'unicité du triplet nom, prénom, email
    if exists (select 1 from membre where nom = new.nom and prenom = new.prenom and email = new.email) then
        signal sqlstate '45000' set message_text = '~Ce membre est déjà inscrit';
    end if;

    # création du login : première lettre du prénom suivie du nom en minuscules avec un suffixe en cas de doublon
    SET baseLogin = CONCAT(LOWER(LEFT(NEW.prenom, 1)), LOWER(replace(NEW.nom, ' ', '')));
    SET nouveauLogin = baseLogin;
    Set nb = 1;
    WHILE EXISTS (SELECT 1 FROM membre WHERE login = nouveauLogin) DO
        SET nouveauLogin = CONCAT(baseLogin, nb);
        SET nb = nb + 1;
    END WHILE;
    SET NEW.login = nouveauLogin;
    # initialisation du mot de passe par défaut : 0000
    SET NEW.password = SHA2('0000', 256);
end;


create trigger avantModificationMembre before update on membre
for each row
begin
    declare msg varchar(255);
    # L'identifiant n'est pas modifiable
    if new.id != old.id then
        signal sqlstate '45000' set message_text = '~L''identifiant ne peut être modifié';
    end if;

    -- Le nom et le prénom ne sont pas modifiables sauf avec le jeton trigger
    if (new.nom != old.nom) and (@trigger is null or @trigger != 1) then
        signal sqlstate '45000' set message_text = '~Le nom ne peut être modifié';
    end if;
    if (new.prenom != old.prenom) and (@trigger is null or @trigger != 1) then
        signal sqlstate '45000' set message_text = '~Le prénom ne peut être modifié';
    end if;

    -- Contrôle sur la colonne email
    if new.email not regexp '^[0-9a-zA-Z]([-_\.]?[0-9a-zA-Z])*@[0-9a-zA-Z]([-_\.]?[0-9a-zA-Z])*[\.][a-zA-Z]{2,4}$' then
        set msg = concat('~L''email ne respecte pas le format attendu : ', new.email);
        signal sqlstate '45000' set message_text = msg;
    end if;

    -- Contrôle sur la colonne telephone si le champ est renseigné
    if new.telephone is not null then
        if new.telephone not regexp '^0[1-79][0-9]{8}$' then
            set msg = concat('~Le numéro de téléphone portable ne respecte pas le format attendu : ', new.telephone);
            signal sqlstate '45000' set message_text = msg;
        end if;
    end if;

    -- Contrôle de l'unicité du triplet nom, prénom, email
    if exists (select 1 from membre where nom = new.nom and prenom = new.prenom and email = new.email and id != new.id) then
        signal sqlstate '45000' set message_text = '~Ce membre est déjà inscrit';
    end if;

    -- le login n'est pas modifiable sauf avec le jeton trigger
    if (new.login is null or new.login != old.login) and (@trigger is null or @trigger != 1) then
        signal sqlstate '45000' set message_text = '~Le login ne peut être modifié';
    end if;
end;
