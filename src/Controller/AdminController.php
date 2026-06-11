<?php
// src/Controller/AdminController.php
// Espace Administrateur : gestion des utilisateurs et des produits (médicaments)

namespace PharmaFEFO\Controller;

use PDO;
use PharmaFEFO\Config\Database;

class AdminController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ================================================================
    // UTILISATEURS
    // ================================================================

    public function users(): void
    {
        $message = null;
        $error = null;

        // Ajout d'un utilisateur
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_user') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? '';

            if ($name === '' || $email === '' || $password === '' || !in_array($role, ['preparateur', 'pharmacien', 'administrateur'], true)) {
                $error = "Tous les champs sont obligatoires (rôle valide requis).";
            } else {
                try {
                    $stmt = $this->db->prepare(
                        "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)"
                    );
                    $stmt->execute([
                        'name'     => $name,
                        'email'    => $email,
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'role'     => $role,
                    ]);
                    $message = "Utilisateur « $name » créé avec succès.";
                } catch (\PDOException $e) {
                    $error = $e->getCode() == 23000
                        ? "Cet email existe déjà."
                        : "Erreur lors de la création : " . $e->getMessage();
                }
            }
        }

        // Suppression d'un utilisateur
        if (isset($_GET['delete'])) {
            $id = (int) $_GET['delete'];
            $current = AuthController::currentUser();

            if ($current && $current['id'] === $id) {
                $error = "Vous ne pouvez pas supprimer votre propre compte.";
            } else {
                $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $message = "Utilisateur supprimé.";
            }
        }

        $users = $this->db->query("SELECT id, name, email, role, created_at FROM users ORDER BY id ASC")->fetchAll();

        require __DIR__ . '/../../templates/admin/users.php';
    }

    // ================================================================
    // PRODUITS (MÉDICAMENTS)
    // ================================================================

    public function products(): void
    {
        $message = null;
        $error = null;

        // Ajout / modification
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $reference = trim($_POST['reference'] ?? '');
            $unitPrice = (float) str_replace(',', '.', $_POST['unit_price'] ?? '0');

            if ($name === '' || $reference === '') {
                $error = "Le nom et la référence sont obligatoires.";
            } else {
                try {
                    if ($action === 'create_product') {
                        $stmt = $this->db->prepare("INSERT INTO products (name, reference, unit_price) VALUES (:name, :reference, :price)");
                        $stmt->execute(['name' => $name, 'reference' => $reference, 'price' => $unitPrice]);
                        $message = "Médicament « $name » ajouté avec succès.";
                    } elseif ($action === 'update_product') {
                        $id = (int) ($_POST['id'] ?? 0);
                        $stmt = $this->db->prepare("UPDATE products SET name = :name, reference = :reference, unit_price = :price WHERE id = :id");
                        $stmt->execute(['name' => $name, 'reference' => $reference, 'price' => $unitPrice, 'id' => $id]);
                        $message = "Médicament mis à jour.";
                    }
                } catch (\PDOException $e) {
                    $error = $e->getCode() == 23000
                        ? "Cette référence existe déjà."
                        : "Erreur : " . $e->getMessage();
                }
            }
        }

        // Suppression
        if (isset($_GET['delete'])) {
            $id = (int) $_GET['delete'];
            try {
                $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $message = "Médicament supprimé.";
            } catch (\PDOException $e) {
                $error = "Impossible de supprimer : ce médicament a des lots de stock associés.";
            }
        }

        // Édition (pré-remplissage du formulaire)
        $editProduct = null;
        if (isset($_GET['edit'])) {
            $id = (int) $_GET['edit'];
            $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $editProduct = $stmt->fetch() ?: null;
        }

        $products = $this->db->query("SELECT * FROM products ORDER BY name ASC")->fetchAll();

        require __DIR__ . '/../../templates/admin/products.php';
    }

    // ================================================================
    // RAPPORT FINANCIER DES PERTES (US 4.2)
    // ================================================================

    public function reports(): void
    {
        $stmt = $this->db->query(
            "SELECT p.name, p.reference, p.unit_price, sb.lot_number, sb.quantity, sb.expiry_date,
                    DATE_FORMAT(sb.expiry_date, '%Y-%m') AS expiry_month,
                    (sb.quantity * p.unit_price) AS lost_value
             FROM stock_batches sb
             INNER JOIN products p ON p.id = sb.product_id
             WHERE sb.status = 'EXPIRED'
             ORDER BY sb.expiry_date DESC"
        );
        $expiredBatches = $stmt->fetchAll();

        $totalLoss = 0.0;
        $lossByMonth = [];
        foreach ($expiredBatches as $row) {
            $totalLoss += (float) $row['lost_value'];
            $lossByMonth[$row['expiry_month']] = ($lossByMonth[$row['expiry_month']] ?? 0) + (float) $row['lost_value'];
        }

        require __DIR__ . '/../../templates/admin/reports.php';
    }
}
