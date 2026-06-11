<?php
class MedicineController
{
    private $db;
    private $medicineModel;
    private $batchModel;
    public function __construct($db)
    {
        $this->db = $db;
        $this->medicineModel = new Medicine($db);
        $this->batchModel = new Batch($db);
    }
    public function index()
    {
        $search = $_GET['search'] ?? '';
        $medicines = $this->medicineModel->getWithStockDetails($search);
        $title = "Medicines Management";
        $contentView = __DIR__ . '/../views/medicines/list.php';
        require_once __DIR__ . '/../views/layouts/layout.php';
    }
    public function create()
    {
        // Role check: Admin or Stock Manager
        checkRole(['Admin', 'Stock Manager']);
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            if (empty($name)) {
                $error = "Medicine name is required.";
            } else {
                if ($this->medicineModel->create($name, $description)) {
                    $_SESSION['flash_success'] = "Medicine '{$name}' created successfully.";
                    header("Location: index.php?route=medicines");
                    exit;
                } else {
                    $error = "Something went wrong. Could not save medicine.";
                }
            }
        }
        $title = "Add Medicine";
        $contentView = __DIR__ . '/../views/medicines/create.php';
        require_once __DIR__ . '/../views/layouts/layout.php';
    }
    public function edit()
    {
        // Role check: Admin or Stock Manager
        checkRole(['Admin', 'Stock Manager']);
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            $_SESSION['flash_error'] = "Invalid medicine ID.";
            header("Location: index.php?route=medicines");
            exit;
        }
        $medicine = $this->medicineModel->getById($id);
        if (!$medicine) {
            $_SESSION['flash_error'] = "Medicine not found.";
            header("Location: index.php?route=medicines");
            exit;
        }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            if (empty($name)) {
                $error = "Medicine name is required.";
            } else {
                if ($this->medicineModel->update($id, $name, $description)) {
                    $_SESSION['flash_success'] = "Medicine '{$name}' updated successfully.";
                    header("Location: index.php?route=medicines");
                    exit;
                } else {
                    $error = "Could not update medicine details.";
                }
            }
        }
        $title = "Edit Medicine";
        $contentView = __DIR__ . '/../views/medicines/edit.php';
        require_once __DIR__ . '/../views/layouts/layout.php';
    }
    public function delete()
    {
        // Role check: Admin or Stock Manager
        checkRole(['Admin', 'Stock Manager']);
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $medicine = $this->medicineModel->getById($id);
            if ($medicine) {
                if ($this->medicineModel->delete($id)) {
                    $_SESSION['flash_success'] = "Medicine '{$medicine['name']}' and its associated batches deleted successfully.";
                } else {
                    $_SESSION['flash_error'] = "Could not delete medicine.";
                }
            } else {
                $_SESSION['flash_error'] = "Medicine not found.";
            }
        } else {
            $_SESSION['flash_error'] = "Invalid medicine ID.";
        }
        header("Location: index.php?route=medicines");
        exit;
    }
    public function dispense()
    {
        // Role check: Admin or Pharmacist
        checkRole(['Admin', 'Pharmacist']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $medicineId = filter_input(INPUT_POST, 'medicine_id', FILTER_VALIDATE_INT);
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
            $medicine = $this->medicineModel->getById($medicineId);
            if (!$medicine) {
                $_SESSION['flash_error'] = "Invalid medicine selected.";
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit;
            }
            if ($quantity <= 0) {
                $_SESSION['flash_error'] = "Dispense quantity must be greater than zero.";
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit;
            }
            try {
                // Execute FEFO dispensing
                $this->batchModel->dispense($medicineId, $quantity);
                $_SESSION['flash_success'] = "Successfully dispensed {$quantity} units of {$medicine['name']}.";
            } catch (Exception $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            }
        }
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php?route=dashboard'));
        exit;
    }
}
