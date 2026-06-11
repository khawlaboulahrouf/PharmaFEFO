<?php
// templates/dashboard/index.php
// Vue HTML isolée - reçoit les variables préparées par DashboardController :
// $okBatches, $warningBatches, $criticalBatches, $expiredBatches, $expiringSoon
?>
<section class="dashboard">
    <h2>Alertes de péremption (FEFO)</h2>

    <div class="summary-cards">
        <div class="card card-green">
            <span class="count"><?= count($okBatches) ?></span>
            <span class="label">Lots OK (&gt; 6 mois)</span>
        </div>
        <div class="card card-orange">
            <span class="count"><?= count($warningBatches) ?></span>
            <span class="label">Lots à surveiller (&lt; 90 jours)</span>
        </div>
        <div class="card card-red">
            <span class="count"><?= count($criticalBatches) ?></span>
            <span class="label">Lots critiques (&lt; 30 jours)</span>
        </div>
        <div class="card card-gray">
            <span class="count"><?= count($expiredBatches) ?></span>
            <span class="label">Lots périmés</span>
        </div>
    </div>

    <div class="filter-bar">
        <a href="?route=dashboard" class="<?= $filterRed ? '' : 'active' ?>">Tous les lots en alerte</a>
        <a href="?route=dashboard&filter=red" class="<?= $filterRed ? 'active' : '' ?>">🔴 Alerte Rouge uniquement</a>
    </div>

    <h3>Lots à péremption critique (&lt; 30 jours) - Priorité de sortie FEFO</h3>
    <?php
        $batchesToShow = $filterRed ? $criticalBatches : array_merge($criticalBatches, $warningBatches);
    ?>
    <?php if (empty($batchesToShow)): ?>
        <p>Aucun lot critique ou en alerte pour le moment.</p>
    <?php else: ?>
        <table class="batch-table">
            <thead>
                <tr>
                    <th>Lot</th>
                    <th>Quantité</th>
                    <th>DLU</th>
                    <th>Jours restants</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($batchesToShow as $batch): ?>
                    <tr class="row-<?= strtolower($batch->getCriticality()) ?>">
                        <td><?= htmlspecialchars($batch->getLotNumber()) ?></td>
                        <td><?= $batch->getQuantity() ?></td>
                        <td><?= $batch->getExpiryDate()->format('d/m/Y') ?></td>
                        <td><?= $batch->getDaysToExpiry() ?> j</td>
                        <td>
                            <span class="badge badge-<?= strtolower($batch->getCriticality()) ?>">
                                <?= $batch->getCriticality() ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h3>Notifications - Péremption le mois prochain (US 2.2)</h3>
    <?php if (empty($expiringSoon)): ?>
        <p>Aucun produit n'expire le mois prochain.</p>
    <?php else: ?>
        <ul class="alert-list">
            <?php foreach ($expiringSoon as $batch): ?>
                <li>
                    Lot <strong><?= htmlspecialchars($batch->getLotNumber()) ?></strong>
                    expire dans <strong><?= $batch->getDaysToExpiry() ?> jour(s)</strong>
                    (<?= $batch->getExpiryDate()->format('d/m/Y') ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
