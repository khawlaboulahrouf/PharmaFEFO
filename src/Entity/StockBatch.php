<?php
// src/Entity/StockBatch.php
// Objet métier PHP pur (encapsulé) - coeur de la règle FEFO

namespace PharmaFEFO\Entity;

use DateTime;
use PharmaFEFO\Enum\BatchStatus;

class StockBatch
{
    private int $id;
    private int $productId;
    private string $lotNumber;
    private int $quantity;
    private DateTime $expiryDate;
    private BatchStatus $status;

    public function __construct(
        int $id,
        int $productId,
        string $lotNumber,
        int $quantity,
        DateTime $expiryDate,
        BatchStatus $status = BatchStatus::OK
    ) {
        $this->id = $id;
        $this->productId = $productId;
        $this->lotNumber = $lotNumber;
        $this->quantity = $quantity;
        $this->expiryDate = $expiryDate;
        $this->status = $status;
    }

    // ------------------ Getters / Setters ------------------

    public function getId(): int
    {
        return $this->id;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getLotNumber(): string
    {
        return $this->lotNumber;
    }

    public function setLotNumber(string $lotNumber): void
    {
        $this->lotNumber = $lotNumber;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getExpiryDate(): DateTime
    {
        return $this->expiryDate;
    }

    public function setExpiryDate(DateTime $expiryDate): void
    {
        $this->expiryDate = $expiryDate;
    }

    public function getStatus(): BatchStatus
    {
        return $this->status;
    }

    public function setStatus(BatchStatus $status): void
    {
        $this->status = $status;
    }

    // ------------------ Logique métier ------------------

    /**
     * Nombre de jours restants avant péremption (peut être négatif si périmé)
     */
    public function getDaysToExpiry(): int
    {
        $now = new DateTime('today');
        $diff = $now->diff($this->expiryDate);
        $days = (int) $diff->format('%a');

        return $this->expiryDate < $now ? -$days : $days;
    }

    /**
     * Lot expiré ?
     */
    public function isExpired(): bool
    {
        return $this->getDaysToExpiry() < 0;
    }

    /**
     * Niveau de criticité selon les seuils métier :
     * Vert  : > 6 mois (180j)
     * Orange: < 90 jours
     * Rouge : < 30 jours
     */
    public function getCriticality(int $warningDays = 90, int $criticalDays = 30): string
    {
        if ($this->isExpired()) {
            return 'EXPIRED';
        }

        $days = $this->getDaysToExpiry();

        if ($days < $criticalDays) {
            return 'CRITICAL'; // Rouge
        }

        if ($days < $warningDays) {
            return 'WARNING'; // Orange
        }

        return 'OK'; // Vert
    }

    /**
     * Décrémente le stock du lot (sortie FEFO).
     *
     * @throws \InvalidArgumentException si la quantité demandée dépasse le stock disponible
     */
    public function decreaseStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('La quantité doit être positive.');
        }

        if ($quantity > $this->quantity) {
            throw new \InvalidArgumentException(
                "Stock insuffisant sur le lot {$this->lotNumber} (disponible: {$this->quantity}, demandé: {$quantity})."
            );
        }

        $this->quantity -= $quantity;
    }

    /**
     * Marque le lot comme périmé / à détruire.
     */
    public function markAsExpired(): void
    {
        $this->status = BatchStatus::EXPIRED;
        $this->quantity = 0;
    }
}
