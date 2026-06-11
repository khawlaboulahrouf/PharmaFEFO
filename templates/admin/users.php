<?php
// templates/admin/users.php
// Vue isolée - reçoit $users, $message, $error de AdminController
use PharmaFEFO\Controller\AuthController;
$currentUser = AuthController::currentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaFEFO - Gestion des utilisateurs</title>
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
        <h2>Gestion des utilisateurs</h2>

        <?php if ($message): ?><div class="alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <h3>Ajouter un utilisateur</h3>
        <form method="POST" action="index.php?route=admin/users" class="admin-form">
            <input type="hidden" name="action" value="create_user">
            <input type="text" name="name" placeholder="Nom complet" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <select name="role" required>
                <option value="">-- Rôle --</option>
                <option value="preparateur">Préparateur</option>
                <option value="pharmacien">Pharmacien titulaire</option>
                <option value="administrateur">Administrateur</option>
            </select>
            <button type="submit">Ajouter</button>
        </form>

        <h3>Liste des utilisateurs</h3>
        <table class="batch-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Créé le</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= (int) $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="user-role badge-role-<?= htmlspecialchars($u['role']) ?>"><?= htmlspecialchars(ucfirst($u['role'])) ?></span></td>
                        <td><?= htmlspecialchars($u['created_at']) ?></td>
                        <td>
                            <?php if ($currentUser['id'] !== (int) $u['id']): ?>
                                <a class="logout" href="index.php?route=admin/users&delete=<?= (int) $u['id'] ?>"
                                   onclick="return confirm('Supprimer cet utilisateur ?');">Supprimer</a>
                            <?php else: ?>
                                <em>(vous)</em>
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
</body>
</html>
