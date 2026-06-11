<?php
// templates/preparateur/receive.php
// US 1.1 - Vue isolée - reçoit $products de PreparateurController
use PharmaFEFO\Controller\AuthController;
$currentUser = AuthController::currentUser();
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaFEFO - Réception de commande</title>
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
        <h2>Réception de commande (US 1.1)</h2>
        <p>Enregistre l'arrivée d'un lot avec son numéro de lot et sa date de péremption (DLU).
           La date ne peut pas être vide ni antérieure à aujourd'hui.</p>

        <div id="form-message"></div>

        <form id="receive-form" class="admin-form">
            <select name="product_id" required>
                <option value="">-- Médicament --</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= (int) $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['reference']) ?>)</option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="lot_number" placeholder="Numéro de lot (ex: LOT-D004)" required>
            <input type="number" name="quantity" placeholder="Quantité" min="1" required>
            <label>
                Date de péremption (DLU)
                <input type="date" name="expiry_date" min="<?= $today ?>" required>
            </label>
            <button type="submit">Enregistrer la réception</button>
        </form>
    </main>

    <footer class="footer">
        <p>PharmaFEFO &copy; <?= date('Y') ?> - Architecture MVC</p>
    </footer>

    <script>
    document.getElementById('receive-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const res = await fetch('index.php?route=stock/receive', { method: 'POST', body: formData });
        const data = await res.json();

        const box = document.getElementById('form-message');
        if (data.success) {
            box.innerHTML = '<div class="alert-success">Lot enregistré avec succès et placé dans la file FEFO.</div>';
            e.target.reset();
        } else {
            box.innerHTML = '<div class="alert-error">' + data.error + '</div>';
        }
    });
    </script>
</body>
</html>
