<?php
// src/Entity/StockMovement.php

namespace PharmaFEFO\Entity;

use DateTime;

class StockMovement
{
    private int $id;
    private int $stockBatchId;
    private string $type; // IN | OUT
    private int $quantity;
    private DateTime $date;

    public function __construct(int $id, int $stockBatchId, string $type, int $quantity, DateTime $date)
    {
        $this->id = $id;
        $this->stockBatchId = $stockBatchId;
        $this->type = $type;
        $this->quantity = $quantity;
        $this->date = $date;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStockBatchId(): int
    {
        return $this->stockBatchId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function isInput(): bool
    {
        return $this->type === 'IN';
    }

    public function isOutput(): bool
    {
        return $this->type === 'OUT';
    }
}
