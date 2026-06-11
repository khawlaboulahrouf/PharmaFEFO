<?php
class AuthController
{
    private $db;
    private $userModel;
    public function __construct($db)
    {
        $this->db = $db;
        $this->userModel = new User($db);
    }
    public function login()
    {
        if (isset($_SESSION['user_id'])) {
            header("Location: index.php?route=dashboard");
            exit;
        }
        $error = '';
        if ($_SERVER['REQUEST_URI'] && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
            if (empty($email) || empty($password)) {
                $error = "Please fill in all fields.";
            } else {
                $user = $this->userModel->authenticate($email, $password);
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['flash_success'] = "Welcome back, {$user['name']}! Logged in as {$user['role']}.";
                    
                    header("Location: index.php?route=dashboard");
                    exit;
                } else {
                    $error = "Invalid email or password.";
                }
            }
        }
        // Render login page directly without global layout (since we don't want sidebar for login)
        require_once __DIR__ . '/../views/auth/login.php';
    }
    public function logout()
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        
        session_start();
        $_SESSION['flash_success'] = "You have been logged out successfully.";
        header("Location: index.php?route=login");
        exit;
    }
}
