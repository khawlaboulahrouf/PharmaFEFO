<?php
// src/Repository/StockBatchRepository.php
// Repository (Data Mapper) - Responsabilité unique : abstraction SQL pour StockBatch

namespace PharmaFEFO\Repository;

use DateTime;
use PDO;
use PharmaFEFO\Config\Database;
use PharmaFEFO\Entity\StockBatch;
use PharmaFEFO\Enum\BatchStatus;

class StockBatchRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Hydrate un StockBatch à partir d'une ligne SQL.
     */
    private function hydrate(array $row): StockBatch
    {
        return new StockBatch(
            (int) $row['id'],
            (int) $row['product_id'],
            (string) $row['lot_number'],
            (int) $row['quantity'],
            new DateTime($row['expiry_date']),
            BatchStatus::from($row['status'])
        );
    }

    /**
     * Retourne tous les lots, triés FEFO (date de péremption la plus proche en premier).
     *
     * @return StockBatch[]
     */
    public function findAllOrderedByFefo(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM stock_batches
             WHERE status != 'EXPIRED'
             ORDER BY expiry_date ASC"
        );

        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    /**
     * US 3.1 - Trouve le(s) lot(s) à utiliser en priorité (FEFO) pour un produit donné,
     * tant que la quantité demandée n'est pas couverte.
     *
     * @return StockBatch[] Liste ordonnée des lots à décrémenter (le premier est le plus prioritaire)
     */
    public function findFefoBatchesForProduct(int $productId, int $quantityNeeded): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM stock_batches
             WHERE product_id = :product_id
               AND quantity > 0
               AND status NOT IN ('EXPIRED', 'RETURN_PROCESS')
             ORDER BY expiry_date ASC"
        );
        $stmt->execute(['product_id' => $productId]);

        $batches = [];
        $remaining = $quantityNeeded;

        foreach ($stmt->fetchAll() as $row) {
            if ($remaining <= 0) {
                break;
            }
            $batches[] = $this->hydrate($row);
            $remaining -= (int) $row['quantity'];
        }

        return $batches;
    }

    /**
     * Retourne les lots groupés par criticité (Vert / Orange / Rouge / Périmé)
     * pour le tableau de bord (US 2.1).
     *
     * @return array{OK: StockBatch[], WARNING: StockBatch[], CRITICAL: StockBatch[], EXPIRED: StockBatch[]}
     */
    public function findGroupedByCriticality(int $warningDays = 90, int $criticalDays = 30): array
    {
        $groups = ['OK' => [], 'WARNING' => [], 'CRITICAL' => [], 'EXPIRED' => []];

        foreach ($this->findAllOrderedByFefo() as $batch) {
            $groups[$batch->getCriticality($warningDays, $criticalDays)][] = $batch;
        }

        return $groups;
    }

    /**
     * US 2.2 - Lots qui vont périmer le mois prochain (<= 30 jours).
     *
     * @return StockBatch[]
     */
    public function findExpiringNextMonth(): array
    {
        return array_filter(
            $this->findAllOrderedByFefo(),
            fn (StockBatch $b) => $b->getDaysToExpiry() >= 0 && $b->getDaysToExpiry() <= 30
        );
    }

    /**
     * Sauvegarde un lot (insert ou update selon la présence d'un id).
     */
    public function save(StockBatch $batch): void
    {
        if ($batch->getId() > 0) {
            $stmt = $this->db->prepare(
                "UPDATE stock_batches
                 SET lot_number = :lot, quantity = :qty, expiry_date = :expiry, status = :status
                 WHERE id = :id"
            );
            $stmt->execute([
                'lot'    => $batch->getLotNumber(),
                'qty'    => $batch->getQuantity(),
                'expiry' => $batch->getExpiryDate()->format('Y-m-d'),
                'status' => $batch->getStatus()->value,
                'id'     => $batch->getId(),
            ]);
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO stock_batches (product_id, lot_number, quantity, expiry_date, status)
                 VALUES (:product_id, :lot, :qty, :expiry, :status)"
            );
            $stmt->execute([
                'product_id' => $batch->getProductId(),
                'lot'        => $batch->getLotNumber(),
                'qty'        => $batch->getQuantity(),
                'expiry'     => $batch->getExpiryDate()->format('Y-m-d'),
                'status'     => $batch->getStatus()->value,
            ]);
        }
    }

    /**
     * US 4.1 - Trouve tous les lots dont la date de péremption est dépassée
     * et dont le statut n'est pas déjà EXPIRED.
     *
     * @return StockBatch[]
     */
    public function findNewlyExpired(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM stock_batches
             WHERE expiry_date < CURDATE() AND status != 'EXPIRED'"
        );

        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    /**
     * US 4.2 - Rapport financier mensuel des pertes (valeur des lots périmés).
     * Le prix unitaire est supposé stocké/joint via la table products
     * (ici simplifié : retourne quantité périmée par produit, le calcul
     * de la valeur monétaire se fait au niveau du service avec le prix unitaire).
     */
    public function getMonthlyExpiredLossReport(): array
    {
        $stmt = $this->db->query(
            "SELECT p.id AS product_id, p.name, p.reference,
                    SUM(sb.quantity) AS lost_quantity,
                    DATE_FORMAT(sb.expiry_date, '%Y-%m') AS expiry_month
             FROM stock_batches sb
             INNER JOIN products p ON p.id = sb.product_id
             WHERE sb.status = 'EXPIRED'
             GROUP BY p.id, expiry_month
             ORDER BY expiry_month DESC"
        );

        return $stmt->fetchAll();
    }
}
