<?php
// src/Controller/DashboardController.php
// Pas de SQL ici : appelle uniquement le Repository, prépare les données pour la vue

namespace PharmaFEFO\Controller;

use PharmaFEFO\Repository\StockBatchRepository;

class DashboardController
{
    private StockBatchRepository $repository;

    public function __construct()
    {
        $this->repository = new StockBatchRepository();
    }

    /**
     * US 2.1 + US 2.2 - Tableau de bord : lots groupés par criticité + alertes du mois.
     */
    public function index(): void
    {
        $grouped = $this->repository->findGroupedByCriticality();
        $expiringSoon = $this->repository->findExpiringNextMonth();

        // Variables utilisées par la vue templates/dashboard/index.php
        $okBatches = $grouped['OK'];
        $warningBatches = $grouped['WARNING'];
        $criticalBatches = $grouped['CRITICAL'];
        $expiredBatches = $grouped['EXPIRED'];

        // US 2.1 - Filtre "Alerte Rouge" uniquement
        $filterRed = isset($_GET['filter']) && $_GET['filter'] === 'red';

        require __DIR__ . '/../../templates/layout/base.php';
    }
}
