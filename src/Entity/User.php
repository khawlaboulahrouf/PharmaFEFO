<?php
// src/Entity/User.php

namespace PharmaFEFO\Entity;

class User
{
    public const ROLE_PREPARATEUR = 'preparateur';
    public const ROLE_PHARMACIEN = 'pharmacien';
    public const ROLE_ADMIN = 'administrateur';

    private int $id;
    private string $name;
    private string $email;
    private string $password; // hash
    private string $role;

    public function __construct(int $id, string $name, string $email, string $password, string $role)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->password;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isPharmacien(): bool
    {
        return $this->role === self::ROLE_PHARMACIEN;
    }

    public function isPreparateur(): bool
    {
        return $this->role === self::ROLE_PREPARATEUR;
    }
}
