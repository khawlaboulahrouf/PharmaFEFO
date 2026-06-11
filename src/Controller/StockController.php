<?php
// src/Controller/StockController.php
// Gère la réception (US 1.1) et la sortie FEFO (US 3.1)

namespace PharmaFEFO\Controller;

use DateTime;
use PharmaFEFO\Entity\StockBatch;
use PharmaFEFO\Enum\BatchStatus;
use PharmaFEFO\Repository\StockBatchRepository;

class StockController
{
    private StockBatchRepository $repository;

    public function __construct()
    {
        $this->repository = new StockBatchRepository();
    }

    /**
     * US 1.1 - Réception d'une commande / enregistrement d'un nouveau lot.
     *
     * Critère d'acceptation : refuse si la date de péremption est vide
     * ou antérieure à la date du jour.
     */
    public function receive(int $productId, string $lotNumber, int $quantity, string $expiryDate): array
    {
        if (empty($expiryDate)) {
            return ['success' => false, 'error' => "La date de péremption (DLU) est obligatoire."];
        }

        $expiry = DateTime::createFromFormat('Y-m-d', $expiryDate);
        $today = new DateTime('today');

        if ($expiry === false || $expiry < $today) {
            return ['success' => false, 'error' => "La date de péremption ne peut pas être vide ou antérieure à aujourd'hui."];
        }

        $batch = new StockBatch(0, $productId, $lotNumber, $quantity, $expiry, BatchStatus::OK);
        $this->repository->save($batch);

        return ['success' => true, 'batch' => $batch];
    }

    /**
     * US 3.1 - Sortie de stock intelligente : applique la règle FEFO.
     * Décrémente automatiquement le(s) lot(s) dont la DLU est la plus courte.
     */
    public function dispatch(int $productId, int $quantityRequested): array
    {
        $batches = $this->repository->findFefoBatchesForProduct($productId, $quantityRequested);

        if (empty($batches)) {
            return ['success' => false, 'error' => "Aucun lot disponible pour ce produit."];
        }

        $remaining = $quantityRequested;
        $movements = [];

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $taken = min($remaining, $batch->getQuantity());
            $batch->decreaseStock($taken);
            $this->repository->save($batch);

            $movements[] = [
                'lot_number' => $batch->getLotNumber(),
                'quantity'   => $taken,
                'expiry'     => $batch->getExpiryDate()->format('Y-m-d'),
            ];

            $remaining -= $taken;
        }

        if ($remaining > 0) {
            return [
                'success'   => false,
                'error'     => "Stock insuffisant : il manque {$remaining} unité(s).",
                'movements' => $movements,
            ];
        }

        return ['success' => true, 'movements' => $movements];
    }

    /**
     * US 4.1 - Le pharmacien titulaire déclare un lot comme "Périmé / À détruire".
     * Le statut passe à EXPIRED et le stock disponible tombe à 0.
     */
    public function declareExpired(int $batchId): array
    {
        $batches = $this->repository->findAllOrderedByFefo();

        foreach ($batches as $batch) {
            if ($batch->getId() === $batchId) {
                if (!$batch->isExpired()) {
                    return ['success' => false, 'error' => "Ce lot n'a pas encore atteint sa date de péremption."];
                }

                $batch->markAsExpired();
                $this->repository->save($batch);

                return ['success' => true, 'message' => "Lot {$batch->getLotNumber()} déclaré périmé. Stock remis à 0."];
            }
        }

        return ['success' => false, 'error' => "Lot introuvable."];
    }
}
