<?php
class AlertController
{
    private $db;
    private $alertModel;
    public function __construct($db)
    {
        $this->db = $db;
        $this->alertModel = new Alert($db);
    }
    public function index()
    {
        // Fetch all alerts (including Green/Safe ones for comprehensive list view)
        $alerts = $this->alertModel->getAll();
        $title = "Expiration Alerts";
        $contentView = __DIR__ . '/../views/alerts/list.php';
        require_once __DIR__ . '/../views/layouts/layout.php';
    }
}