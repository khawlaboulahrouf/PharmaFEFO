<?php
class DashboardController
{
    private $db;
    private $medicineModel;
    private $batchModel;
    private $alertModel;
    public function __construct($db)
    {
        $this->db = $db;
        $this->medicineModel = new Medicine($db);
        $this->batchModel = new Batch($db);
        $this->alertModel = new Alert($db);
    }
    public function index()
    {
        // 1. Total Medicines
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM medicines");
        $totalMedicines = $stmt->fetch()['total'];
        // 2. Total Batches
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM batches");
        $totalBatches = $stmt->fetch()['total'];
        // 3. Available Stock (Total unexpired quantity)
        $stmt = $this->db->query("SELECT SUM(quantity) as total FROM batches WHERE expiration_date >= CURRENT_DATE()");
        $availableStock = $stmt->fetch()['total'] ?? 0;
        // 4. Expired Products (Batches with positive quantity that are expired)
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM batches WHERE expiration_date < CURRENT_DATE() AND quantity > 0");
        $expiredProducts = $stmt->fetch()['total'];
        // 5. Near Expiration Products (Batches that trigger Orange or Red alerts and have quantity > 0)
        $stmt = $this->db->query("
            SELECT COUNT(DISTINCT a.id) as total 
            FROM alerts a
            JOIN batches b ON a.batch_id = b.id
            WHERE a.alert_level IN ('Red', 'Orange') AND b.quantity > 0
        ");
        $nearExpirationProducts = $stmt->fetch()['total'];
        // Get alerts for the alert section (Expired, Red, Orange)
        $dashboardAlerts = $this->alertModel->getDashboardAlerts();
        
        // List of all medicines for the Quick Dispense select dropdown
        $medicinesList = $this->medicineModel->getAll();
        $title = "Dashboard";
        $contentView = __DIR__ . '/../views/dashboard/index.php';
        require_once __DIR__ . '/../views/layouts/layout.php';
    }
}
