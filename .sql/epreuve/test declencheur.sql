use ppe;

-- contrôle sur l'ajout
insert into epreuve (saison, description, date)
values ('hiver', 'description', curdate() + interval 2 month);

# [45000][1644] #L'ajout n'est pas autorisé

-- controle sur la suppression

delete from epreuve;
#[45000][1644] #La suppression n'est pas autorisée

-- contrôle de la modification
update epreuve set saison = 'printemps' where saison = 'hiver';
# [45000][1644] #La saison ne peut pas être modifiée


update epreuve set date = null;
# [45000][1644] #La date de l'épreuve doit être renseignée


-- valeur erronée

update epreuve set date = '2025-02-29';
# [22001][1292] Data truncation: Incorrect date value: '2025-02-29' for column 'date' at row 1


select * from epreuve;
