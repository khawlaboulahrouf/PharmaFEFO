<?php
// templates/admin/products.php
// Vue isolée - reçoit $products, $editProduct, $message, $error de AdminController
use PharmaFEFO\Controller\AuthController;
$currentUser = AuthController::currentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaFEFO - Gestion des médicaments</title>
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
        <h2>Gestion des médicaments</h2>

        <?php if ($message): ?><div class="alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <h3><?= $editProduct ? 'Modifier le médicament' : 'Ajouter un médicament' ?></h3>
        <form method="POST" action="index.php?route=admin/products" class="admin-form">
            <?php if ($editProduct): ?>
                <input type="hidden" name="action" value="update_product">
                <input type="hidden" name="id" value="<?= (int) $editProduct['id'] ?>">
            <?php else: ?>
                <input type="hidden" name="action" value="create_product">
            <?php endif; ?>
            <input type="text" name="name" placeholder="Nom du médicament" required
                   value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>">
            <input type="text" name="reference" placeholder="Référence (ex: PARA500)" required
                   value="<?= htmlspecialchars($editProduct['reference'] ?? '') ?>">
            <input type="number" step="0.01" min="0" name="unit_price" placeholder="Prix unitaire (DH)" required
                   value="<?= htmlspecialchars($editProduct['unit_price'] ?? '0.00') ?>">
            <button type="submit"><?= $editProduct ? 'Enregistrer' : 'Ajouter' ?></button>
            <?php if ($editProduct): ?>
                <a class="logout" href="index.php?route=admin/products">Annuler</a>
            <?php endif; ?>
        </form>

        <h3>Liste des médicaments</h3>
        <table class="batch-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Référence</th>
                    <th>Prix unitaire</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= (int) $p['id'] ?></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['reference']) ?></td>
                        <td><?= number_format((float) $p['unit_price'], 2) ?> DH</td>
                        <td>
                            <a href="index.php?route=admin/products&edit=<?= (int) $p['id'] ?>">Modifier</a>
                            &nbsp;|&nbsp;
                            <a class="logout" href="index.php?route=admin/products&delete=<?= (int) $p['id'] ?>"
                               onclick="return confirm('Supprimer ce médicament ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

    <footer class="footer">
        <p>PharmaFEFO &copy; <?= date('Y') ?> - Architecture MVC</p>
    </footer>
</body>
</html>
