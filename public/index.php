<?php
// public/index.php
// Point d'entrée unique (DocumentRoot) - CONTRÔLEUR FRONTAL / ROUTEUR

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

// Autoload simple PSR-4 (sans Composer)
spl_autoload_register(function (string $class) {
    $prefix = 'PharmaFEFO\\';
    $baseDir = __DIR__ . '/../src/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use PharmaFEFO\Controller\AdminController;
use PharmaFEFO\Controller\AuthController;
use PharmaFEFO\Controller\PharmacienController;
use PharmaFEFO\Controller\PreparateurController;
use PharmaFEFO\Controller\DashboardController;
use PharmaFEFO\Controller\StockController;
use PharmaFEFO\Entity\User;

$route = $_GET['route'] ?? 'dashboard';

switch ($route) {

    // ----------------------------------------------------------------
    // Authentification (accessible à tous, même non connectés)
    // ----------------------------------------------------------------
    case 'login':
        (new AuthController())->showLogin();
        break;

    case 'login/submit':
        (new AuthController())->login();
        break;

    case 'logout':
        (new AuthController())->logout();
        break;

    // ----------------------------------------------------------------
    // Tableau de bord - accessible aux 3 rôles connectés
    // ----------------------------------------------------------------
    case 'dashboard':
        AuthController::requireLogin();
        (new DashboardController())->index();
        break;

    case 'preparateur/receive':
        AuthController::requireRole(User::ROLE_PREPARATEUR);
        (new PreparateurController())->receiveForm();
        break;

    case 'preparateur/dispatch':
        AuthController::requireRole(User::ROLE_PREPARATEUR);
        (new PreparateurController())->dispatchForm();
        break;

    // ----------------------------------------------------------------
    // Réception de commande (US 1.1) - Préparateur uniquement
    // ----------------------------------------------------------------
    case 'stock/receive':
        AuthController::requireRole(User::ROLE_PREPARATEUR);
        $controller = new StockController();
        $result = $controller->receive(
            (int) ($_POST['product_id'] ?? 0),
            (string) ($_POST['lot_number'] ?? ''),
            (int) ($_POST['quantity'] ?? 0),
            (string) ($_POST['expiry_date'] ?? '')
        );
        header('Content-Type: application/json');
        echo json_encode($result);
        break;

    // ----------------------------------------------------------------
    // Sortie FEFO (US 3.1) - Préparateur uniquement
    // ----------------------------------------------------------------
    case 'stock/dispatch':
        AuthController::requireRole(User::ROLE_PREPARATEUR);
        $controller = new StockController();
        $result = $controller->dispatch(
            (int) ($_POST['product_id'] ?? 0),
            (int) ($_POST['quantity'] ?? 0)
        );
        header('Content-Type: application/json');
        echo json_encode($result);
        break;

    // ----------------------------------------------------------------
    // Déclaration de lot périmé (US 4.1) - Pharmacien titulaire uniquement
    // ----------------------------------------------------------------
    case 'stock/declare-expired':
        AuthController::requireRole(User::ROLE_PHARMACIEN);
        $controller = new StockController();
        $result = $controller->declareExpired((int) ($_POST['batch_id'] ?? 0));
        header('Content-Type: application/json');
        echo json_encode($result);
        break;

    // ----------------------------------------------------------------
    // Espace Administrateur (utilisateurs / rapports / seuils)
    // - Administrateur uniquement
    // ----------------------------------------------------------------
    case 'admin/users':
        AuthController::requireRole(User::ROLE_ADMIN);
        (new AdminController())->users();
        break;

    case 'admin/products':
        AuthController::requireRole(User::ROLE_ADMIN);
        (new AdminController())->products();
        break;

    case 'admin/reports':
        AuthController::requireRole(User::ROLE_ADMIN);
        (new AdminController())->reports();
        break;

    case 'pharmacien/inventory':
        AuthController::requireRole(User::ROLE_PHARMACIEN);
        (new PharmacienController())->inventory();
        break;

    case 'pharmacien/thresholds':
        AuthController::requireRole(User::ROLE_PHARMACIEN);
        (new PharmacienController())->thresholds();
        break;

    case 'pharmacien/return-supplier':
        AuthController::requireRole(User::ROLE_PHARMACIEN);
        $result = (new PharmacienController())->returnToSupplier((int) ($_POST['batch_id'] ?? 0));
        header('Content-Type: application/json');
        echo json_encode($result);
        break;

    default:
        http_response_code(404);
        echo "Page non trouvée.";
        break;
}
