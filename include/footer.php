<?php
global $mention, $politique;
?>

<a class="masquer" id="lienMentions" href="#" data-bs-toggle="modal" data-bs-target="#modalMentions">
    Mentions légales
</a>&nbsp;
<a class="masquer" id="lienPolitique" href="#" data-bs-toggle="modal" data-bs-target="#modalPolitique">
    Politique de confidentialité
</a>&nbsp;


<!-- Modal pour les mentions légales-->
<div class="modal fade" id="modalMentions" tabindex="-1" aria-labelledby="modalMentionsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <!-- Titre aligné à gauche -->
                <h5 class="modal-title mb-0" id="modalMentionsLabel">Mentions légales</h5>

                <!-- Lien vers PDF au centre -->
                <a href="/mentionslegales/pdf.php" class="text-decoration-none text-dark fw-bold mx-3"
                   target="_blank">
                    📥 Version PDF
                </a>

                <!-- Bouton de fermeture aligné à droite -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>

            <div class="modal-body">
                <?= $mention ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour la politique de confidentialité -->
<div class="modal fade" id="modalPolitique" tabindex="-1" aria-labelledby="modalPolitiqueLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <!-- Titre aligné à gauche -->
                <h5 class="modal-title mb-0" id="modalMentionsLabel">Politique de confidentialité</h5>

                <!-- Lien vers PDF au centre -->
                <a href="/politique/pdf.php" class="text-decoration-none text-dark fw-bold mx-3"
                   target="_blank">
                    📥 Version PDF
                </a>

                <!-- Bouton de fermeture aligné à droite -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <?= $politique ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>