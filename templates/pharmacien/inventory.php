<?php
// templates/pharmacien/inventory.php
// Vue isolée - reçoit $batches (StockBatch[]), $thresholds de PharmacienController
use PharmaFEFO\Controller\AuthController;
$currentUser = AuthController::currentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaFEFO - Validation inventaire</title>
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
        <h2>Validation de l'inventaire</h2>
        <p>Lots triés FEFO (date de péremption la plus proche en premier). Déclarez les lots périmés ou initiez un retour fournisseur pour les lots proches de la péremption.</p>

        <div id="action-message"></div>

        <table class="batch-table">
            <thead>
                <tr>
                    <th>Lot</th>
                    <th>Quantité</th>
                    <th>DLU</th>
                    <th>Jours restants</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($batches as $batch): ?>
                    <?php $crit = $batch->getCriticality((int) $thresholds['warning_days'], (int) $thresholds['critical_days']); ?>
                    <tr class="row-<?= strtolower($crit) ?>" id="batch-row-<?= $batch->getId() ?>">
                        <td><?= htmlspecialchars($batch->getLotNumber()) ?></td>
                        <td><?= $batch->getQuantity() ?></td>
                        <td><?= $batch->getExpiryDate()->format('d/m/Y') ?></td>
                        <td><?= $batch->getDaysToExpiry() ?> j</td>
                        <td><span class="badge badge-<?= strtolower($crit) ?>"><?= $crit ?></span></td>
                        <td>
                            <?php if ($batch->isExpired()): ?>
                                <button type="button" class="btn-action" data-action="declare-expired" data-id="<?= $batch->getId() ?>">
                                    Déclarer périmé
                                </button>
                            <?php elseif (in_array($crit, ['WARNING', 'CRITICAL'], true)): ?>
                                <button type="button" class="btn-action" data-action="return-supplier" data-id="<?= $batch->getId() ?>">
                                    Retour fournisseur
                                </button>
                            <?php else: ?>
                                <em>—</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

    <footer class="footer">
        <p>PharmaFEFO &copy; <?= date('Y') ?> - Architecture MVC</p>
    </footer>

    <script>
    document.querySelectorAll('.btn-action').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            const action = btn.dataset.action;
            const route = action === 'declare-expired' ? 'stock/declare-expired' : 'pharmacien/return-supplier';

            const formData = new FormData();
            formData.append('batch_id', id);

            const res = await fetch('index.php?route=' + route, { method: 'POST', body: formData });
            const data = await res.json();

            const box = document.getElementById('action-message');
            box.innerHTML = '<div class="alert-' + (data.success ? 'success' : 'error') + '">' +
                            (data.message || data.error) + '</div>';

            if (data.success) {
                document.getElementById('batch-row-' + id).remove();
            }
        });
    });
    </script>
</body>
</html>
