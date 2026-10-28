<?php
// regisztracio.php
require __DIR__ . '/auth.php';

$adat = ['nev' => '', 'email' => ''];
$hibak = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $adat['nev'] = trim((string)($_POST['nev'] ?? ''));
    $adat['email'] = mb_strtolower(trim((string)($_POST['email'] ?? '')));
    $jelszo = (string)($_POST['jelszo'] ?? '');
    $jelszo2 = (string)($_POST['jelszo2'] ?? '');

    if (mb_strlen($adat['nev']) < 2 || mb_strlen($adat['nev']) > 80) { 
        $hibak['nev'] = 'A név 2–80 karakter.'; 
    }
    if (!filter_var($adat['email'], FILTER_VALIDATE_EMAIL)) { 
        $hibak['email'] = 'Érvénytelen e-mail cím.'; 
    }
    if (mb_strlen($jelszo) < 10) { 
        $hibak['jelszo'] = 'A jelszó legalább 10 karakter.'; 
    }
    if (strlen($jelszo) > 72) { 
        $hibak['jelszo'] = 'A jelszó legfeljebb 72 bájt (bcrypt-korlát).'; 
    }
    if ($jelszo !== $jelszo2) { 
        $hibak['jelszo2'] = 'A két jelszó nem egyezik.'; 
    }

    if (!$hibak) {
        try {
            db()->prepare('INSERT INTO felhasznalo (nev, email, jelszo_hash) VALUES (?, ?, ?)')
                ->execute([$adat['nev'], $adat['email'], password_hash($jelszo, PASSWORD_DEFAULT)]);
            $_SESSION['flash'] = 'Sikeres regisztráció, most jelentkezz be.';
            header('Location: /belepes.php', true, 303);
            exit;
        } catch (PDOException $ex) {
            if ($ex->getCode() !== '23000') { throw $ex; }
            $hibak['email'] = 'Ezzel az e-maillel már regisztráltak.';
        }
    }
    http_response_code(422);
}
?>
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <title>Regisztráció</title>
</head>
<body>
<h1>Regisztráció</h1>

<?php if (!empty($hibak)): ?>
  <ul style="color: red;">
    <?php foreach ($hibak as $hibainfo): ?>
      <li><?= e($hibainfo) ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<form method="post" action="/regisztracio.php">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  
  <div>
    <label for="nev">Név</label><br>
    <input id="nev" name="nev" type="text" value="<?= e($adat['nev']) ?>" required>
  </div>
  
  <div>
    <label for="email">E-mail</label><br>
    <input id="email" name="email" type="email" value="<?= e($adat['email']) ?>" required>
  </div>
  
  <div>
    <label for="jelszo">Jelszó</label><br>
    <input id="jelszo" name="jelszo" type="password" required>
  </div>
  
  <div>
    <label for="jelszo2">Jelszó ismét</label><br>
    <input id="jelszo2" name="jelszo2" type="password" required>
  </div>
  
  <br>
  <button type="submit">Regisztráció</button>
</form>

<p><a href="/belepes.php">Már van fiókod? Belépés</a></p>
</body>
</html>