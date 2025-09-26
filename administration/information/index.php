<?php
// activation du chargement dynamique des ressources
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

// récupération des informations existantes

$lesInfos = json_encode(Information::getAll(['Publique','Privée']), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$head = <<<EOD
<script>
    const lesInfos = $lesInfos;
</script>
<!-- TinyMCE -->
<script src="/composant/tinymce/tinymce.min.js"></script>
<script>
    // initialisation basique et légère, le JavaScript principal pourra reconfigurer si besoin
    document.addEventListener('DOMContentLoaded', function() {
            if (typeof tinymce !== 'undefined') {
            tinymce.init({
                license_key: 'gpl',
                selector: '#contenu',
                menubar: false,
                // retirer les plugins obsolètes (textcolor, colorpicker, paste) et garder les autres disponibles
                plugins: 'link image lists table media code',
                // toolbar simplifié sans boutons de couleur obsolètes
                toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image media | code',
                height: 400,
                branding: false,
                relative_urls: false,
                remove_script_host: false,
                automatic_uploads: false,
                file_picker_types: 'image',
                file_picker_callback: function(callback, value, meta) {
                    // Ouvre la photothèque en mode picker
                    var picker = window.open('/administration/photoinformation/picker.php', 'photobank', 'width=900,height=600');
                    function receive(e) {
                        if (!e.data) return;
                        if (e.data.mceAction === 'insertImage' && e.data.url) {
                            callback(e.data.url);
                            window.removeEventListener('message', receive);
                            try { picker.close(); } catch (err) {}
                        }
                    }
                    window.addEventListener('message', receive, false);
                }
            });
        }
    });
</script>
EOD;

// chargement de l'interface
require RACINE . "/include/interface.php";
