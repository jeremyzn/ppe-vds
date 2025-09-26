<?php
// Page légère pour TinyMCE image picker
require $_SERVER['DOCUMENT_ROOT'] . '/include/autoload.php';

$lesFichiers = json_encode(FichierImage::getAll(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$lesParametres = json_encode(FichierImage::getConfig(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$head = <<<EOD
<style>
  body { font-family: Arial, sans-serif; padding: 1rem; }
  .grid { display:flex; flex-wrap:wrap; gap:0.75rem; }
  .card { width:150px; border:1px solid #ddd; padding:0.5rem; text-align:center; }
  .card img { max-width:100%; height:100px; object-fit:cover; cursor:pointer; }
  .card button { margin-top:0.5rem; }
</style>
<script>
  const lesFichiers = $lesFichiers;
  const lesParametres = $lesParametres;
</script>
EOD;

// affichage minimal
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Photothèque - Choisir une image</title>
  <?php echo $head; ?>
</head>
<body>
  <h4>Choisir une image</h4>
  <div class="grid" id="grid"></div>

  <script>
    const grid = document.getElementById('grid');
    for (const f of lesFichiers) {
      const card = document.createElement('div');
      card.className = 'card';
      const img = document.createElement('img');
      img.src = lesParametres.repertoire + '/' + f;
      img.title = f;
      img.onclick = () => {
        if (window.opener && !window.opener.closed) {
          window.opener.postMessage({mceAction: 'insertImage', url: img.src}, '*');
          window.close();
        }
      };
      card.appendChild(img);
      const name = document.createElement('div');
      name.style.fontSize = '0.8rem';
      name.style.marginTop = '0.35rem';
      name.innerText = f;
      card.appendChild(name);
      grid.appendChild(card);
    }
  </script>
</body>
</html>
