<?php
// templates/preparateur/dispatch.php
// US 3.1 - Vue isolée - reçoit $products de PreparateurController
use PharmaFEFO\Controller\AuthController;
$currentUser = AuthController::currentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaFEFO - Sortie de stock (FEFO)</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="topbar">
        <h1>💊 PharmaFEFO</h1>
        <nav>
            <a href="index.php?route=dashboard">Tableau de bord</a>
            <a href="index.php?route=preparateur/receive">Réception</a>
            <a href="index.php?route=preparateur/dispatch">Sortie de stock</a>
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
        <h2>Sortie de stock - Règle FEFO (US 3.1)</h2>
        <p>Choisis le médicament et la quantité dispensée. Le système désigne automatiquement
           le(s) lot(s) à sortir en priorité (DLU la plus courte).</p>

        <div id="form-message"></div>

        <form id="dispatch-form" class="admin-form">
            <select name="product_id" required>
                <option value="">-- Médicament --</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= (int) $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['reference']) ?>)</option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="quantity" placeholder="Quantité demandée" min="1" required>
            <button type="submit">Valider la sortie</button>
        </form>

        <div id="result"></div>
    </main>

    <footer class="footer">
        <p>PharmaFEFO &copy; <?= date('Y') ?> - Architecture MVC</p>
    </footer>

    <script>
    document.getElementById('dispatch-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const res = await fetch('index.php?route=stock/dispatch', { method: 'POST', body: formData });
        const data = await res.json();

        const box = document.getElementById('form-message');
        const result = document.getElementById('result');

        if (data.success) {
            box.innerHTML = '<div class="alert-success">Sortie validée. Voici le(s) lot(s) à prélever :</div>';
            let html = '<table class="batch-table"><thead><tr><th>Lot à prélever</th><th>Quantité</th><th>DLU</th></tr></thead><tbody>';
            data.movements.forEach(m => {
                html += `<tr><td><strong>${m.lot_number}</strong></td><td>${m.quantity}</td><td>${m.expiry}</td></tr>`;
            });
            html += '</tbody></table>';
            result.innerHTML = html;
            e.target.reset();
        } else {
            box.innerHTML = '<div class="alert-error">' + data.error + '</div>';
            result.innerHTML = '';
        }
    });
    </script>
</body>
</html>
