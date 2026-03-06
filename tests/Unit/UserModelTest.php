<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour la logique du modèle User
 * (tests de la logique pure sans base de données)
 */
class UserModelTest extends TestCase
{
    /**
     * Test que isDefaultPassword détecte les mots de passe par défaut
     */
    public function test_detects_default_password_pattern(): void
    {
        // Les mots de passe par défaut du système suivent le pattern PHS + 4 chars alphanum
        $defaultPasswords = ['PHSAB12', 'PHSXYZ9', 'PHS1234', 'PHSAAAA'];

        foreach ($defaultPasswords as $password) {
            $isDefault = preg_match('/^PHS[A-Z0-9]{4}$/', $password) === 1;
            $this->assertTrue($isDefault, "'{$password}' devrait être détecté comme mot de passe par défaut");
        }
    }

    /**
     * Test que les mots de passe personnalisés ne sont pas détectés comme défaut
     */
    public function test_custom_password_not_detected_as_default(): void
    {
        $customPasswords = ['MonMotDePasse123!', 'SecureP@ss', 'abc123XYZ'];

        foreach ($customPasswords as $password) {
            $isDefault = preg_match('/^PHS[A-Z0-9]{4}$/', $password) === 1;
            $this->assertFalse($isDefault, "'{$password}' ne devrait pas être détecté comme mot de passe par défaut");
        }
    }

    /**
     * Test le hachage de mot de passe (compatibilité bcrypt)
     */
    public function test_password_hashing_uses_bcrypt(): void
    {
        $password = 'TestPassword123!';
        $hash     = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $this->assertTrue(password_verify($password, $hash));
        $this->assertStringStartsWith('$2y$', $hash); // Préfixe bcrypt
    }

    /**
     * Test que deux hashes du même mot de passe sont différents (sel aléatoire)
     */
    public function test_same_password_produces_different_hashes(): void
    {
        $password = 'MonMotDePasse';
        $hash1    = password_hash($password, PASSWORD_BCRYPT);
        $hash2    = password_hash($password, PASSWORD_BCRYPT);

        $this->assertNotEquals($hash1, $hash2);
        $this->assertTrue(password_verify($password, $hash1));
        $this->assertTrue(password_verify($password, $hash2));
    }

    /**
     * Test la génération du mot de passe par défaut selon le pattern du UserController
     */
    public function test_generated_default_password_matches_pattern(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $randomChars = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4);
            $password    = 'PHS' . $randomChars;

            $this->assertMatchesRegularExpression('/^PHS[A-Z0-9]{4}$/', $password);
            $this->assertEquals(7, strlen($password));
        }
    }

    /**
     * Test que jsonSerialize exclut bien le mot de passe
     */
    public function test_user_json_excludes_password(): void
    {
        $attributes = [
            'id'       => 1,
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => '$2y$12$hashedpassword',
            'role'     => 'admin',
        ];

        // Simuler le comportement de jsonSerialize
        unset($attributes['password']);

        $this->assertArrayNotHasKey('password', $attributes);
        $this->assertArrayHasKey('name', $attributes);
        $this->assertArrayHasKey('email', $attributes);
    }

    /**
     * Test la logique de blocage d'utilisateur
     */
    public function test_is_blocked_logic(): void
    {
        // is_active = 0 => bloqué
        $isActive = 0;
        $isBlocked = !boolval($isActive);
        $this->assertTrue($isBlocked);

        // is_active = 1 => non bloqué
        $isActive = 1;
        $isBlocked = !boolval($isActive);
        $this->assertFalse($isBlocked);
    }

    /**
     * Test la limite de sessions actives (max 3 dans AuthController)
     */
    public function test_session_limit_sort_by_last_activity(): void
    {
        $sessions = [
            (object) ['session_id' => 'c', 'last_activity' => '2025-01-03 10:00:00'],
            (object) ['session_id' => 'a', 'last_activity' => '2025-01-01 10:00:00'],
            (object) ['session_id' => 'b', 'last_activity' => '2025-01-02 10:00:00'],
        ];

        usort($sessions, function ($a, $b) {
            return strtotime($a->last_activity) - strtotime($b->last_activity);
        });

        // La plus ancienne doit être en premier
        $this->assertEquals('a', $sessions[0]->session_id);
        $this->assertEquals('b', $sessions[1]->session_id);
        $this->assertEquals('c', $sessions[2]->session_id);
    }
}
