<?php

class Structure {

    public static function verifier(): void {

        // vérifier la présence d'un fichier structure.json
        if (!is_file($_SERVER['DOCUMENT_ROOT'] . '/.config/structure.json')) {
            Erreur::afficherReponse("Le fichier structure.json est manquant dans le répertoire /.config.", 'system');
        }

        // vérifier la possibilité de créer une variable de session
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Ne vérifie qu'une fois par session
        if (!empty($_SESSION['structure'])) {
            return;
        }

        try {
            $pdo = Database::getInstance();

            $json = file_get_contents($_SERVER['DOCUMENT_ROOT']. '/.config/structure.json');
            $structure = json_decode($json, true);

            if (!is_array($structure)) {
                Erreur::afficherReponse("Le fichier structure.json est invalide.", 'system');
            }

            $dbname = $pdo->query("SELECT DATABASE()")->fetchColumn();

            foreach ($structure as $table => $lesColonnesAttendues) {
                // Vérifier la table
                $sql = <<<SQL
                    SELECT COUNT(*) FROM information_schema.tables 
                    WHERE table_schema = :db AND table_name = :table
SQL;
                $select = new Select();
                $nb = $select->getValue($sql, ['db' => $dbname, 'table' => $table]);


                if ($nb == 0) {
                    Erreur::afficherReponse("La table `$table` est manquante dans la base `$dbname`.", 'system');
                }

                $sql = <<<SQL
                    SELECT column_name FROM information_schema.columns 
                    WHERE table_schema = :db AND table_name = :table
SQL;

                $lesColonnes = array_column($select->getRows($sql, ['db' => $dbname, 'table' => $table]),'column_name');

                foreach ($lesColonnes as $colonne) {
                    if (!in_array($colonne, $lesColonnesAttendues)) {
                        Erreur::afficherReponse("Colonne manquante dans `$table` : `$colonne`", 'system');
                    }
                }


            }
            // marquér la vérification
            $_SESSION['structure'] = true;

        } catch (Exception $e) {
            Erreur::afficherReponse("Erreur lors de la vérification de la base : " . $e->getMessage(), 'system');
        }
    }
}
