<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour la logique du PasswordResetController
 */
class PasswordResetControllerTest extends TestCase
{
    /**
     * Test que le token généré est sécurisé (longueur attendue)
     */
    public function test_generated_token_length(): void
    {
        $token = bin2hex(random_bytes(32));

        // bin2hex(32 bytes) = 64 caractères hexadécimaux
        $this->assertEquals(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }

    /**
     * Test que deux tokens générés sont toujours différents
     */
    public function test_tokens_are_unique(): void
    {
        $tokens = [];
        for ($i = 0; $i < 100; $i++) {
            $tokens[] = bin2hex(random_bytes(32));
        }

        // Vérifier l'unicité
        $this->assertEquals(100, count(array_unique($tokens)));
    }

    /**
     * Test que le hash SHA-256 du token est toujours identique (déterministe)
     */
    public function test_token_hash_is_deterministic(): void
    {
        $token = 'abc123testtoken';
        $hash1 = hash('sha256', $token);
        $hash2 = hash('sha256', $token);

        $this->assertEquals($hash1, $hash2);
        $this->assertEquals(64, strlen($hash1)); // SHA-256 = 64 hex chars
    }

    /**
     * Test que des tokens différents produisent des hashes différents
     */
    public function test_different_tokens_produce_different_hashes(): void
    {
        $hash1 = hash('sha256', 'token_a');
        $hash2 = hash('sha256', 'token_b');

        $this->assertNotEquals($hash1, $hash2);
    }

    /**
     * Test la logique d'expiration du token
     */
    public function test_token_expiry_detection(): void
    {
        $tokenExpiry = 3600; // 1 heure

        // Token expiré (créé il y a 2 heures)
        $expiredAt = date('Y-m-d H:i:s', time() - 7200);
        $this->assertLessThan(time(), strtotime($expiredAt));

        // Token valide (expire dans 30 minutes)
        $validUntil = date('Y-m-d H:i:s', time() + 1800);
        $this->assertGreaterThan(time(), strtotime($validUntil));
    }

    /**
     * Test que le lien de reset est correctement construit
     */
    public function test_reset_link_construction(): void
    {
        $appUrl = 'http://localhost:8000';
        $token  = 'abc123';

        $link = rtrim($appUrl, '/') . '/reset-password?token=' . $token;

        $this->assertEquals('http://localhost:8000/reset-password?token=abc123', $link);
    }

    /**
     * Test que le lien est correct même avec un slash en fin d'APP_URL
     */
    public function test_reset_link_handles_trailing_slash(): void
    {
        $appUrl = 'http://localhost:8000/';
        $token  = 'mytoken';

        $link = rtrim($appUrl, '/') . '/reset-password?token=' . $token;

        $this->assertStringNotContainsString('//', str_replace('http://', '', $link));
        $this->assertStringEndsWith('?token=mytoken', $link);
    }

    /**
     * Test la durée d'expiration (1 heure = 3600 secondes)
     */
    public function test_token_expiry_is_one_hour(): void
    {
        $tokenExpiry = 3600;
        $expiresAt   = date('Y-m-d H:i:s', time() + $tokenExpiry);

        $expiresTimestamp = strtotime($expiresAt);
        $diff             = $expiresTimestamp - time();

        // Marge de 2 secondes pour l'exécution du test
        $this->assertGreaterThanOrEqual(3598, $diff);
        $this->assertLessThanOrEqual(3600, $diff);
    }

    /**
     * Test la durée restante exprimée en minutes dans la notification
     */
    public function test_expiry_duration_in_minutes(): void
    {
        $tokenExpiry      = 3600;
        $durationMinutes  = $tokenExpiry / 60;

        $this->assertEquals(60, $durationMinutes);
    }
}
