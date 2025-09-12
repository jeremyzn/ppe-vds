use ppe;

drop trigger if exists avantAjoutClassement;
drop trigger if exists avantModificationClassement;

delimiter $$

create trigger avantAjoutClassement
    before insert
    on classement
    for each row
begin
    declare msg varchar(255);
    # contrôle de la colonne titre
    set new.titre = trim(new.titre);
    # le titre doit comporter entre 3  et 100 caractères
    if char_length(new.titre) not between 3 and 100 then
        signal sqlstate '45000' set message_text = '~Le titre doit comporter entre 3 et 100 caractères';
    end if;
    # le titre doit respecter l'expression régulière suivante
    if new.titre not regexp
       '^[0-9A-Za-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ/,\' ()-]+$' then
        set msg = concat('~Le titre comporte des caractères non autorisés : ', new.titre);
        signal sqlstate '45000' set message_text = msg;
    end if;

    # contrôle de la colonne date
    # la date de l'événement doit être inférieure ou égale à la date du jour
    if new.date > curdate() then
        signal sqlstate '45000' set message_text = '~La date doit être inférieure ou égale à la date du jour';
    end if;

    # contrôle de la colonne fichier
    # contrôle du format du fichier pdf : des lettres, des chiffres, des espaces des tirets et l'apostrophe et se terminer par .pdf'
    if new.fichier not regexp '^[a-zA-Z0-9() \'-]+\.pdf$' then
        set msg = concat('~L\'URI du fichier PDF n\'est pas valide :', new.fichier);
        signal sqlstate '45000' set message_text = msg;
    end if;
    if exists(select 1 from classement where fichier = new.fichier) then
        signal sqlstate '45000' set message_text = '~Ce fichier PDF est déjà référencé';
    end if;
end;

$$

create trigger avantModificationClassement
    before update
    on classement
    for each row

begin
    declare msg varchar(255);
    # L'identifiant n'est pas modifiable
    if new.id is null or new.id != old.id then
        signal sqlstate '45000' set message_text = '~L\'identifiant ne peut être modifié';
    end if;

    # contrôle de la colonne titre
    if new.titre is null then
        signal sqlstate '45000' set message_text = '~Le titre doit être renseigné';
    end if;

    if new.titre != old.titre then
        # le titre doit comporter entre 3  et 100 caractères
        if char_length(new.titre) not between 3 and 100 then
            signal sqlstate '45000' set message_text = '~Le titre doit comporter entre 3 et 150 caractères';
        end if;
        # le titre doit respecter l'expression régulière suivante
        if new.titre not regexp
           '^[0-9A-Za-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ/,\' ()-]*$' then
            set msg = concat('~Le titre comporte des caractères non autoriséss : ', new.titre);
            signal sqlstate '45000' set message_text = msg;
        end if;
    end if;

    # contrôle de la date
    if new.date is null then
        signal sqlstate '45000' set message_text = '~La date doit être renseignée';
    end if;
    -- Vérifier si la date est valide : en mode ansi toute valeur erronée est remplacée par la chaine '0000-00-00'
    if new.date regexp '^0000-00-00$' then
        signal sqlstate '45000' set message_text = '~la date n\'est pas valide';
    end if;
    # si la date de l'événement est modifiée, elle doit être supérieure à la date du jour
    if new.date < old.date then
        if new.date > curdate() then
            signal sqlstate '45000' set message_text = '~La date doit être inférieure ou égale à la date du jour';
        end if;
    end if;
    # le nom du fichier pdf associé n'est pas modifiable
    if new.fichier is null or new.fichier != old.fichier then
        signal sqlstate '45000' set message_text = '~L\'URI du fichier PDF n\'est pas modifiable';
    end if;
end