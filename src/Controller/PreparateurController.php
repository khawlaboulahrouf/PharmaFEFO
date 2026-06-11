<?php
// src/Controller/PreparateurController.php
// Pages Préparateur : réception de commande (US 1.1) et sortie FEFO (US 3.1)

namespace PharmaFEFO\Controller;

use PDO;
use PharmaFEFO\Config\Database;

class PreparateurController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * US 1.1 - Formulaire de réception de commande (lot + DLU).
     */
    public function receiveForm(): void
    {
        $products = $this->db->query("SELECT id, name, reference FROM products ORDER BY name ASC")->fetchAll();

        require __DIR__ . '/../../templates/preparateur/receive.php';
    }

    /**
     * US 3.1 - Formulaire de sortie de stock (dispensation FEFO).
     */
    public function dispatchForm(): void
    {
        $products = $this->db->query("SELECT id, name, reference FROM products ORDER BY name ASC")->fetchAll();

        require __DIR__ . '/../../templates/preparateur/dispatch.php';
    }
}
