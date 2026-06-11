<?php
// src/Entity/Alert.php

namespace PharmaFEFO\Entity;

use DateTime;

class Alert
{
    private int $id;
    private int $stockBatchId;
    private string $message;
    private string $level; // INFO | WARNING | CRITICAL
    private DateTime $createdAt;

    public function __construct(int $id, int $stockBatchId, string $message, string $level, DateTime $createdAt)
    {
        $this->id = $id;
        $this->stockBatchId = $stockBatchId;
        $this->message = $message;
        $this->level = $level;
        $this->createdAt = $createdAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStockBatchId(): int
    {
        return $this->stockBatchId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * Une alerte est critique si son niveau vaut CRITICAL.
     */
    public function isCritical(): bool
    {
        return $this->level === 'CRITICAL';
    }
}
