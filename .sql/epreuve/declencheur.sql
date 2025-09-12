use ppe;

-- IMPORTANT : A lancer après le script insert

# La table contient 4 enregistrements
# seul la modification est autorisée

drop trigger if exists avantAjoutEpreuve;
drop trigger if exists avantModificationEpreuve;
drop trigger if exists avantSuppressionEpreuve;

create trigger avantAjoutEpreuve before insert on epreuve
for each row
   signal sqlstate '45000' set message_text = '~L''ajout n''est pas autorisé';

create trigger avantSuppressionEpreuve before delete on epreuve
    for each row
    signal sqlstate '45000' set message_text = '~La suppression n''est pas autorisée';

create trigger avantModificationEpreuve
    before update
    on epreuve
    for each row
begin
    # colonne saison non modifiable
    if new.saison != old.saison then
        signal sqlstate '45000' set message_text = '~La saison ne peut pas être modifiée';
    end if;

    # contrôle de la description

    set new.description = trim(new.description);

    if new.description != old.description then
        if char_length(new.description) < 10 then
            SIGNAL sqlstate '45000' set message_text = '~La description doit être plus détaillée';
        end if;
        if new.description regexp
           '<script|drop|select|insert|delete|update|--|\\/\\*|\\*\\/' then
            signal sqlstate '45000' set message_text =
                    '~La description contient des caractères ou des mots interdits. interdits';
        end if;
    end if;

    # contrôle de la date
    # si la date de l'épreuve est modifiée,
    if new.date != old.date and new.date < curdate() then
            SIGNAL sqlstate '45000' set message_text =
                    '~La date de l''épreuve doit être supérieure à la date du jour';
    end if;

end
