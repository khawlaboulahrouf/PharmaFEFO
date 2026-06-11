<?php
class BatchController
{
    private $db;
    private $batchModel;
    private $medicineModel;
    public function __construct($db)
    {
        $this->db = $db;
        $this->batchModel = new Batch($db);
        $this->medicineModel = new Medicine($db);
    }
    public function index()
    {
        $batches = $this->batchModel->getAll();
        $title = "Batches Management";
        $contentView = __DIR__ . '/../views/batches/list.php';
        require_once __DIR__ . '/../views/layouts/layout.php';
    }
    public function create()
    {
        checkRole(['Admin', 'Stock Manager']);
        $medicines = $this->medicineModel->getAll();
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $medicine_id = filter_input(INPUT_POST, 'medicine_id', FILTER_VALIDATE_INT);
            $batch_number = trim($_POST['batch_number'] ?? '');
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
            $expiration_date = $_POST['expiration_date'] ?? '';
            if (!$medicine_id) {
                $error = "Please select a medicine.";
            } elseif (empty($batch_number)) {
                $error = "Batch number is required.";
            } elseif ($quantity === false || $quantity <= 0) {
                $error = "Quantity must be greater than zero.";
            } elseif (empty($expiration_date)) {
                $error = "Expiration date is required.";
            } else {
                if ($this->batchModel->create($medicine_id, $batch_number, $quantity, $expiration_date)) {
                    $_SESSION['flash_success'] = "Batch '{$batch_number}' added successfully.";
                    header("Location: index.php?route=batches");
                    exit;
                } else {
                    $error = "Could not save batch. Make sure batch number is unique.";
                }
            }
        }
        // Keep selected medicine if directed from Medicine list "Add Batch" button
        $selected_medicine_id = filter_input(INPUT_GET, 'medicine_id', FILTER_VALIDATE_INT);
        $title = "Add Batch";
        $contentView = __DIR__ . '/../views/batches/create.php';
        require_once __DIR__ . '/../views/layouts/layout.php';
    }
    public function edit()
    {
        checkRole(['Admin', 'Stock Manager']);
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            $_SESSION['flash_error'] = "Invalid batch ID.";
            header("Location: index.php?route=batches");
            exit;
        }
        $batch = $this->batchModel->getById($id);
        if (!$batch) {
            $_SESSION['flash_error'] = "Batch not found.";
            header("Location: index.php?route=batches");
            exit;
        }
        $medicines = $this->medicineModel->getAll();
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $medicine_id = filter_input(INPUT_POST, 'medicine_id', FILTER_VALIDATE_INT);
            $batch_number = trim($_POST['batch_number'] ?? '');
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
            $expiration_date = $_POST['expiration_date'] ?? '';
            if (!$medicine_id) {
                $error = "Please select a medicine.";
            } elseif (empty($batch_number)) {
                $error = "Batch number is required.";
            } elseif ($quantity === false || $quantity < 0) {
                $error = "Quantity cannot be negative.";
            } elseif (empty($expiration_date)) {
                $error = "Expiration date is required.";
            } else {
                if ($this->batchModel->update($id, $medicine_id, $batch_number, $quantity, $expiration_date)) {
                    $_SESSION['flash_success'] = "Batch '{$batch_number}' updated successfully.";
                    header("Location: index.php?route=batches");
                    exit;
                } else {
                    $error = "Could not update batch.";
                }
            }
        }
        $title = "Edit Batch";
        $contentView = __DIR__ . '/../views/batches/edit.php';
        require_once __DIR__ . '/../views/layouts/layout.php';
    }
    public function delete()
    {
        checkRole(['Admin', 'Stock Manager']);
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $batch = $this->batchModel->getById($id);
            if ($batch) {
                if ($this->batchModel->delete($id)) {
                    $_SESSION['flash_success'] = "Batch '{$batch['batch_number']}' deleted successfully.";
                } else {
                    $_SESSION['flash_error'] = "Could not delete batch.";
                }
            } else {
                $_SESSION['flash_error'] = "Batch not found.";
            }
        } else {
            $_SESSION['flash_error'] = "Invalid batch ID.";
        }
        header("Location: index.php?route=batches");
        exit;
    }
}
