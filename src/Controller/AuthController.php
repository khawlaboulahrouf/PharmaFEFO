<?php
// src/Controller/AuthController.php
// Authentification + RBAC (3 rôles : preparateur, pharmacien, administrateur)

namespace PharmaFEFO\Controller;

use PDO;
use PharmaFEFO\Config\Database;
use PharmaFEFO\Entity\User;

class AuthController
{
    private PDO $db;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = Database::getConnection();
    }

    /**
     * Affiche le formulaire de connexion.
     */
    public function showLogin(): void
    {
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        require __DIR__ . '/../../templates/auth/login.php';
    }

    /**
     * Traite le formulaire de connexion (POST).
     */
    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($password, $row['password'])) {
            $_SESSION['login_error'] = "Email ou mot de passe incorrect.";
            header('Location: index.php?route=login');
            exit;
        }

        // Stocke uniquement les infos nécessaires en session
        $_SESSION['user'] = [
            'id'    => (int) $row['id'],
            'name'  => $row['name'],
            'email' => $row['email'],
            'role'  => $row['role'],
        ];

        header('Location: index.php?route=dashboard');
        exit;
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        header('Location: index.php?route=login');
        exit;
    }

    /**
     * Retourne l'utilisateur courant (array) ou null si non connecté.
     */
    public static function currentUser(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user'] ?? null;
    }

    /**
     * Garde de route : exige une connexion. Redirige vers /login sinon.
     */
    public static function requireLogin(): array
    {
        $user = self::currentUser();
        if ($user === null) {
            header('Location: index.php?route=login');
            exit;
        }
        return $user;
    }

    /**
     * Garde de route : exige un (ou plusieurs) rôle(s) précis.
     * Renvoie 403 si l'utilisateur connecté n'a pas le bon rôle.
     */
    public static function requireRole(string ...$roles): array
    {
        $user = self::requireLogin();

        if (!in_array($user['role'], $roles, true)) {
            http_response_code(403);
            echo "<h1>403 - Accès refusé</h1><p>Cette page est réservée à : " . implode(', ', $roles) . ".</p>";
            echo '<p><a href="index.php?route=dashboard">Retour au tableau de bord</a></p>';
            exit;
        }

        return $user;
    }
}
