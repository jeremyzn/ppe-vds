# use ppe;
#
# drop trigger if exists avant_insert_document;
# drop trigger if exists avant_update_document;
#
#
#
#
# DELIMITER //
# CREATE TRIGGER avant_insert_document
#     BEFORE INSERT
#     ON document
#     FOR EACH ROW
# BEGIN
#     DECLARE v_max_id BIGINT;
#
#     -- Vérifie l'ID : si NULL ou existant, on prend max(id)+1
#     IF NEW.id IS NULL OR EXISTS (SELECT 1 FROM document WHERE id = NEW.id) THEN
#         SELECT IFNULL(MAX(id), 0) INTO v_max_id FROM document;
#         SET NEW.id = v_max_id + 1;
#     END IF;
# END//
# DELIMITER ;
#
# DELIMITER //
#
#
#
# CREATE TRIGGER avant_update_document
#     BEFORE UPDATE
#     ON document
#     FOR EACH ROW
# BEGIN
#     -- Empêche la modification du fichier lors de la mise à jour
#     IF (NEW.fichier <> OLD.fichier) OR ((NEW.fichier IS NULL) <> (OLD.fichier IS NULL)) THEN
#         SET NEW.fichier = OLD.fichier;
#     END IF;
# END//
# DELIMITER ;
# //
#
# DELIMITER ;
