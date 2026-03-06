<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Middleware\ThrottleMiddleware;

/**
 * Tests unitaires pour ThrottleMiddleware
 */
class ThrottleMiddlewareTest extends TestCase
{
    private string $storageDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storageDir = sys_get_temp_dir() . '/calvino_throttle_tests';
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Nettoyer les fichiers de throttle de test
        foreach (glob($this->storageDir . '/*.json') as $file) {
            unlink($file);
        }
        parent::tearDown();
    }

    /**
     * Test que le middleware accepte les requêtes sous la limite
     */
    public function test_allows_requests_under_limit(): void
    {
        $middleware = new ThrottleMiddlewareTestable(3, 60, $this->storageDir);
        $ip         = '127.0.0.1';
        $key        = $middleware->exposeKey($ip);

        // Simuler 2 tentatives (sous la limite de 3)
        $middleware->exposeIncrement($key);
        $middleware->exposeIncrement($key);

        $this->assertFalse($middleware->exposeTooMany($key));
        $this->assertEquals(2, $middleware->exposeAttempts($key));
    }

    /**
     * Test que la limite est atteinte après maxAttempts requêtes
     */
    public function test_blocks_requests_at_limit(): void
    {
        $middleware = new ThrottleMiddlewareTestable(3, 60, $this->storageDir);
        $ip         = '192.168.1.1';
        $key        = $middleware->exposeKey($ip);

        $middleware->exposeIncrement($key);
        $middleware->exposeIncrement($key);
        $middleware->exposeIncrement($key);

        $this->assertTrue($middleware->exposeTooMany($key));
    }

    /**
     * Test que les tentatives expirent après la fenêtre de temps
     */
    public function test_resets_after_decay_window(): void
    {
        $middleware = new ThrottleMiddlewareTestable(3, 1, $this->storageDir); // 1 seconde
        $ip         = '10.0.0.1';
        $key        = $middleware->exposeKey($ip);

        $middleware->exposeIncrement($key);
        $middleware->exposeIncrement($key);
        $middleware->exposeIncrement($key);

        $this->assertTrue($middleware->exposeTooMany($key));

        // Attendre que la fenêtre expire
        sleep(2);

        $this->assertFalse($middleware->exposeTooMany($key));
        $this->assertEquals(0, $middleware->exposeAttempts($key));
    }

    /**
     * Test que des IPs différentes ont des compteurs séparés
     */
    public function test_different_ips_have_separate_counters(): void
    {
        $middleware = new ThrottleMiddlewareTestable(2, 60, $this->storageDir);
        $key1       = $middleware->exposeKey('1.1.1.1');
        $key2       = $middleware->exposeKey('2.2.2.2');

        $middleware->exposeIncrement($key1);
        $middleware->exposeIncrement($key1);

        $this->assertTrue($middleware->exposeTooMany($key1));
        $this->assertFalse($middleware->exposeTooMany($key2));
    }

    /**
     * Test le calcul de retryAfter
     */
    public function test_retry_after_is_positive(): void
    {
        $middleware = new ThrottleMiddlewareTestable(1, 60, $this->storageDir);
        $ip         = '3.3.3.3';
        $key        = $middleware->exposeKey($ip);

        $middleware->exposeIncrement($key);

        $retryAfter = $middleware->exposeRetryAfter($key);
        $this->assertGreaterThan(0, $retryAfter);
        $this->assertLessThanOrEqual(60, $retryAfter);
    }
}

/**
 * Version testable du ThrottleMiddleware avec méthodes exposées
 */
class ThrottleMiddlewareTestable extends ThrottleMiddleware
{
    public function __construct(int $maxAttempts, int $decaySeconds, string $customStorageDir)
    {
        parent::__construct($maxAttempts, $decaySeconds);
        // Rediriger vers le répertoire de test via réflexion
        $reflection = new \ReflectionClass($this);
        $prop = $reflection->getProperty('storageDir');
        $prop->setAccessible(true);
        $prop->setValue($this, $customStorageDir);
    }

    public function exposeKey(string $ip): string
    {
        $reflection = new \ReflectionClass($this);
        $method = $reflection->getMethod('buildKey');
        $method->setAccessible(true);
        return $method->invoke($this, $ip);
    }

    public function exposeIncrement(string $key): void
    {
        $reflection = new \ReflectionClass($this);
        $method = $reflection->getMethod('incrementAttempts');
        $method->setAccessible(true);
        $method->invoke($this, $key);
    }

    public function exposeAttempts(string $key): int
    {
        $reflection = new \ReflectionClass($this);
        $method = $reflection->getMethod('getAttempts');
        $method->setAccessible(true);
        return $method->invoke($this, $key);
    }

    public function exposeTooMany(string $key): bool
    {
        $reflection = new \ReflectionClass($this);
        $method = $reflection->getMethod('tooManyAttempts');
        $method->setAccessible(true);
        return $method->invoke($this, $key);
    }

    public function exposeRetryAfter(string $key): int
    {
        $reflection = new \ReflectionClass($this);
        $method = $reflection->getMethod('getRetryAfter');
        $method->setAccessible(true);
        return $method->invoke($this, $key);
    }
}
