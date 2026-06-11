<?php
// templates/layout/base.php
// Header / footer commun - Pas de logique métier ici, uniquement présentation

use PharmaFEFO\Controller\AuthController;

$currentUser = AuthController::currentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaFEFO - Tableau de bord</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="topbar">
        <h1>💊 PharmaFEFO</h1>
        <nav>
            <a href="index.php?route=dashboard">Tableau de bord</a>
            <?php if ($currentUser && $currentUser['role'] === \PharmaFEFO\Entity\User::ROLE_PREPARATEUR): ?>
                <a href="index.php?route=preparateur/receive">Réception</a>
                <a href="index.php?route=preparateur/dispatch">Sortie de stock</a>
            <?php endif; ?>
            <?php if ($currentUser && $currentUser['role'] === \PharmaFEFO\Entity\User::ROLE_ADMIN): ?>
                <a href="index.php?route=admin/users">Utilisateurs</a>
                <a href="index.php?route=admin/products">Médicaments</a>
                <a href="index.php?route=admin/reports">Rapport pertes</a>
            <?php endif; ?>
            <?php if ($currentUser && $currentUser['role'] === \PharmaFEFO\Entity\User::ROLE_PHARMACIEN): ?>
                <a href="index.php?route=pharmacien/inventory">Inventaire</a>
                <a href="index.php?route=pharmacien/thresholds">Seuils d'alerte</a>
            <?php endif; ?>
        </nav>
        <?php if ($currentUser): ?>
            <div class="user-box">
                <span class="user-name"><?= htmlspecialchars($currentUser['name']) ?></span>
                <span class="user-role badge-role-<?= htmlspecialchars($currentUser['role']) ?>">
                    <?= htmlspecialchars(ucfirst($currentUser['role'])) ?>
                </span>
                <a class="logout" href="index.php?route=logout">Déconnexion</a>
            </div>
        <?php endif; ?>
    </header>

    <main class="container">
        <?php require __DIR__ . '/../dashboard/index.php'; ?>
    </main>

    <footer class="footer">
        <p>PharmaFEFO &copy; <?= date('Y') ?> - Architecture MVC</p>
    </footer>
</body>
</html>
