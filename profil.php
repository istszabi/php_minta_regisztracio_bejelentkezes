<?php
// profil.php
require __DIR__ . '/auth.php';

$en = bejelentkezve_kell();
?>
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <title>Profil</title>
</head>
<body>
<h1>Szia, <?= e($en['nev']) ?>!</h1>
<p>E-mail: <?= e($en['email']) ?> · Regisztrált: <?= e((string)$en['letrehozva']) ?></p>

<form method="post" action="/kilepes.php">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <button type="submit">Kijelentkezés</button>
</form>
</body>
</html>