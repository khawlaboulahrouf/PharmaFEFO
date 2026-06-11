<?php
// templates/pharmacien/thresholds.php
// Vue isolée - reçoit $current, $message, $error de PharmacienController
use PharmaFEFO\Controller\AuthController;
$currentUser = AuthController::currentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaFEFO - Seuils d'alerte</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="topbar">
        <h1>💊 PharmaFEFO</h1>
        <nav>
            <a href="index.php?route=dashboard">Tableau de bord</a>
            <a href="index.php?route=pharmacien/inventory">Inventaire</a>
            <a href="index.php?route=pharmacien/thresholds">Seuils d'alerte</a>
        </nav>
        <div class="user-box">
            <span class="user-name"><?= htmlspecialchars($currentUser['name']) ?></span>
            <span class="user-role badge-role-<?= htmlspecialchars($currentUser['role']) ?>">
                <?= htmlspecialchars(ucfirst($currentUser['role'])) ?>
            </span>
            <a class="logout" href="index.php?route=logout">Déconnexion</a>
        </div>
    </header>

    <main class="container">
        <h2>Configurer les seuils d'alerte</h2>
        <p>Définit le nombre de jours restants avant péremption qui déclenchent les codes couleur sur le tableau de bord.</p>

        <?php if ($message): ?><div class="alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST" action="index.php?route=pharmacien/thresholds" class="admin-form">
            <label>
                Seuil Orange (jours)
                <input type="number" name="warning_days" min="1" value="<?= (int) $current['warning_days'] ?>" required>
            </label>
            <label>
                Seuil Rouge (jours)
                <input type="number" name="critical_days" min="1" value="<?= (int) $current['critical_days'] ?>" required>
            </label>
            <button type="submit">Enregistrer</button>
        </form>

        <p>
            <span class="badge badge-ok">Vert</span> &gt; <?= (int) $current['warning_days'] ?> jours &nbsp;|&nbsp;
            <span class="badge badge-warning">Orange</span> &lt; <?= (int) $current['warning_days'] ?> jours &nbsp;|&nbsp;
            <span class="badge badge-critical">Rouge</span> &lt; <?= (int) $current['critical_days'] ?> jours
        </p>
    </main>

    <footer class="footer">
        <p>PharmaFEFO &copy; <?= date('Y') ?> - Architecture MVC</p>
    </footer>
</body>
</html>
