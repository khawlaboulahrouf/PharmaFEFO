<?php
// templates/admin/reports.php
// Vue isolée - reçoit $expiredBatches de AdminController
use PharmaFEFO\Controller\AuthController;
$currentUser = AuthController::currentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaFEFO - Rapport des pertes</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="topbar">
        <h1>💊 PharmaFEFO</h1>
        <nav>
            <a href="index.php?route=dashboard">Tableau de bord</a>
            <a href="index.php?route=admin/users">Utilisateurs</a>
            <a href="index.php?route=admin/products">Médicaments</a>
            <a href="index.php?route=admin/reports">Rapport pertes</a>
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
        <h2>Rapport financier des pertes (US 4.2)</h2>
        <p>Liste des lots déclarés <strong>périmés</strong> (statut EXPIRED), à utiliser pour ajuster les futures commandes.</p>

        <?php if (empty($expiredBatches)): ?>
            <p>Aucune perte enregistrée pour le moment. 🎉</p>
        <?php else: ?>
            <div class="summary-cards">
                <div class="card card-red">
                    <span class="count"><?= number_format($totalLoss, 2) ?> DH</span>
                    <span class="label">Perte totale (tous lots périmés)</span>
                </div>
            </div>

            <table class="batch-table">
                <thead>
                    <tr>
                        <th>Médicament</th>
                        <th>Référence</th>
                        <th>Lot</th>
                        <th>Quantité perdue</th>
                        <th>Prix unitaire</th>
                        <th>Valeur perdue</th>
                        <th>Date péremption</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expiredBatches as $b): ?>
                        <tr class="row-critical">
                            <td><?= htmlspecialchars($b['name']) ?></td>
                            <td><?= htmlspecialchars($b['reference']) ?></td>
                            <td><?= htmlspecialchars($b['lot_number']) ?></td>
                            <td><?= (int) $b['quantity'] ?></td>
                            <td><?= number_format((float) $b['unit_price'], 2) ?> DH</td>
                            <td><strong><?= number_format((float) $b['lost_value'], 2) ?> DH</strong></td>
                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($b['expiry_date']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h3>Récapitulatif mensuel</h3>
            <table class="batch-table">
                <thead><tr><th>Mois</th><th>Valeur perdue</th></tr></thead>
                <tbody>
                    <?php foreach ($lossByMonth as $month => $value): ?>
                        <tr><td><?= htmlspecialchars($month) ?></td><td><?= number_format($value, 2) ?> DH</td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <p>PharmaFEFO &copy; <?= date('Y') ?> - Architecture MVC</p>
    </footer>
</body>
</html>
