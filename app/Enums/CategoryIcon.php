<?php

namespace App\Enums;

/**
 * Énumération des icônes Font Awesome disponibles pour les catégories
 */
class CategoryIcon
{
    // Liste des icônes disponibles
    public const CAPSULES = 'fa-capsules';
    public const SYRINGE = 'fa-syringe';
    public const BANDAGE = 'fa-bandage';
    public const PILLS = 'fa-pills';
    public const HEART = 'fa-heart';
    public const AMBULANCE = 'fa-ambulance';
    public const FLASK = 'fa-flask';
    public const PRESCRIPTION = 'fa-prescription';
    public const USER_MD = 'fa-user-md';
    public const STETHOSCOPE = 'fa-stethoscope';
    public const OTHER = 'fa-plus-circle';

    /**
     * Retourne toutes les icônes disponibles
     *
     * @return array
     */
    public static function all(): array
    {
        return [
            self::CAPSULES,
            self::SYRINGE,
            self::BANDAGE,
            self::PILLS,
            self::HEART,
            self::AMBULANCE,
            self::FLASK,
            self::PRESCRIPTION,
            self::USER_MD,
            self::STETHOSCOPE,
            self::OTHER
        ];
    }

    /**
     * Retourne toutes les icônes disponibles avec leurs libellés
     *
     * @return array
     */
    public static function options(): array
    {
        return [
            self::CAPSULES => 'Capsules',
            self::SYRINGE => 'Seringue',
            self::BANDAGE => 'Bandage',
            self::PILLS => 'Pilules',
            self::HEART => 'Cœur',
            self::AMBULANCE => 'Ambulance',
            self::FLASK => 'Fiole',
            self::PRESCRIPTION => 'Ordonnance',
            self::USER_MD => 'Médecin',
            self::STETHOSCOPE => 'Stéthoscope',
            self::OTHER => 'Autre'
        ];
    }

    /**
     * Vérifie si une icône est valide
     *
     * @param string $icon
     * @return bool
     */
    public static function isValid(string $icon): bool
    {
        return in_array($icon, self::all());
    }
} 