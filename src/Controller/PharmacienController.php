<?php
// src/Controller/PharmacienController.php
// Espace Pharmacien Titulaire : configuration des seuils d'alerte
// et gestion des retours fournisseur (lots proches péremption -> remboursement)

namespace PharmaFEFO\Controller;

use PDO;
use PharmaFEFO\Config\Database;
use PharmaFEFO\Enum\BatchStatus;
use PharmaFEFO\Repository\StockBatchRepository;

class PharmacienController
{
    private PDO $db;
    private StockBatchRepository $repository;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->repository = new StockBatchRepository();
    }

    /**
     * Configurer les seuils d'alerte (jours avant péremption -> Orange / Rouge).
     */
    public function thresholds(): void
    {
        $message = null;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $warning = (int) ($_POST['warning_days'] ?? 0);
            $critical = (int) ($_POST['critical_days'] ?? 0);

            if ($warning <= $critical || $critical <= 0) {
                $error = "Le seuil 'Orange' doit être supérieur au seuil 'Rouge', et le seuil 'Rouge' doit être positif.";
            } else {
                $stmt = $this->db->prepare("UPDATE alert_thresholds SET warning_days = :w, critical_days = :c WHERE id = 1");
                $stmt->execute(['w' => $warning, 'c' => $critical]);
                $message = "Seuils d'alerte mis à jour avec succès.";
            }
        }

        $current = $this->db->query("SELECT * FROM alert_thresholds WHERE id = 1")->fetch();

        require __DIR__ . '/../../templates/pharmacien/thresholds.php';
    }

    /**
     * US (Pharmacien) - Validation des inventaires : vue de tous les lots
     * triés FEFO avec leur niveau de criticité, pour validation/contrôle.
     */
    public function inventory(): void
    {
        $thresholds = $this->db->query("SELECT * FROM alert_thresholds WHERE id = 1")->fetch();
        $batches = $this->repository->findAllOrderedByFefo();

        require __DIR__ . '/../../templates/pharmacien/inventory.php';
    }

    /**
     * US (Pharmacien) - Initie un retour fournisseur pour un lot proche de la
     * péremption (en vue d'un remboursement), au lieu de le laisser périmer.
     * Statut du lot -> RETURN_PROCESS.
     */
    public function returnToSupplier(int $batchId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM stock_batches WHERE id = :id");
        $stmt->execute(['id' => $batchId]);
        $row = $stmt->fetch();

        if (!$row) {
            return ['success' => false, 'error' => "Lot introuvable."];
        }

        $update = $this->db->prepare("UPDATE stock_batches SET status = :status, quantity = 0 WHERE id = :id");
        $update->execute(['status' => BatchStatus::RETURN_PROCESS->value, 'id' => $batchId]);

        return ['success' => true, 'message' => "Lot {$row['lot_number']} mis en retour fournisseur (remboursement)."];
    }
}
