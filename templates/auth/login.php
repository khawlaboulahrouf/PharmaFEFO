<?php
// templates/auth/login.php
// Vue HTML isolée - reçoit $error du AuthController
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaFEFO - Connexion</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-wrapper">
        <form class="login-card" method="POST" action="index.php?route=login/submit">
            <h1>💊 PharmaFEFO</h1>
            <p class="subtitle">Connectez-vous selon votre rôle</p>

            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required autofocus>

            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Se connecter</button>

            <div class="demo-accounts">
                <p>Comptes de démo :</p>
                <ul>
                    <li>Préparateur : preparateur@pharmafefo.local / password</li>
                    <li>Pharmacien : pharmacien@pharmafefo.local / password</li>
                    <li>Administrateur : admin@pharmafefo.local / password</li>
                </ul>
            </div>
        </form>
    </div>
</body>
</html>
