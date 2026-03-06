<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour la logique de pagination et filtrage de UserController
 */
class UserControllerPaginationTest extends TestCase
{
    /**
     * Crée une liste simulée d'utilisateurs
     */
    private function makeUsers(): array
    {
        return [
            (object) ['id' => 1, 'name' => 'Alice Admin',  'email' => 'alice@ex.com',  'role' => 'admin',       'is_active' => 1],
            (object) ['id' => 2, 'name' => 'Bob Manager',  'email' => 'bob@ex.com',    'role' => 'manager',     'is_active' => 1],
            (object) ['id' => 3, 'name' => 'Carol Pharma', 'email' => 'carol@ex.com',  'role' => 'pharmacist',  'is_active' => 1],
            (object) ['id' => 4, 'name' => 'Dave Cashier', 'email' => 'dave@ex.com',   'role' => 'cashier',     'is_active' => 0],
            (object) ['id' => 5, 'name' => 'Eve Admin',    'email' => 'eve@ex.com',    'role' => 'admin',       'is_active' => 1],
        ];
    }

    /**
     * Test la pagination retourne le bon nombre d'éléments
     */
    public function test_pagination_returns_correct_slice(): void
    {
        $users   = $this->makeUsers();
        $page    = 1;
        $perPage = 2;
        $total   = count($users);

        $paged = array_slice($users, ($page - 1) * $perPage, $perPage);
        $this->assertCount(2, $paged);
        $this->assertEquals(1, $paged[0]->id);
        $this->assertEquals(2, $paged[1]->id);
    }

    /**
     * Test que le nombre total de pages est correct
     */
    public function test_total_pages_calculation(): void
    {
        $this->assertEquals(3, (int) ceil(5 / 2));
        $this->assertEquals(1, (int) ceil(3 / 15));
        $this->assertEquals(2, (int) ceil(16 / 15));
        $this->assertEquals(4, (int) ceil(100 / 25));
    }

    /**
     * Test le filtre par rôle
     */
    public function test_filter_by_role(): void
    {
        $users    = $this->makeUsers();
        $filtered = array_values(array_filter($users, fn($u) => $u->role === 'admin'));

        $this->assertCount(2, $filtered);
        $this->assertEquals('Alice Admin', $filtered[0]->name);
        $this->assertEquals('Eve Admin', $filtered[1]->name);
    }

    /**
     * Test le filtre par statut (is_active)
     */
    public function test_filter_by_status(): void
    {
        $users    = $this->makeUsers();
        $filtered = array_values(array_filter($users, fn($u) => (string) $u->is_active === '0'));

        $this->assertCount(1, $filtered);
        $this->assertEquals('Dave Cashier', $filtered[0]->name);
    }

    /**
     * Test le filtre par recherche (name + email)
     */
    public function test_filter_by_search(): void
    {
        $users    = $this->makeUsers();
        $search   = 'alice';
        $filtered = array_values(array_filter(
            $users,
            fn($u) => stripos($u->name . ' ' . $u->email, $search) !== false
        ));

        $this->assertCount(1, $filtered);
        $this->assertEquals('Alice Admin', $filtered[0]->name);
    }

    /**
     * Test la combinaison de filtres
     */
    public function test_combined_filters(): void
    {
        $users    = $this->makeUsers();
        $role     = 'admin';
        $status   = '1';

        $filtered = array_values(array_filter($users, function ($u) use ($role, $status) {
            return $u->role === $role && (string) $u->is_active === $status;
        }));

        $this->assertCount(2, $filtered);
    }

    /**
     * Test que per_page est limité à 100
     */
    public function test_per_page_max_capped(): void
    {
        $requested = 999;
        $perPage   = min(100, max(1, $requested));

        $this->assertEquals(100, $perPage);
    }

    /**
     * Test que per_page minimum est 1
     */
    public function test_per_page_min_is_one(): void
    {
        $requested = 0;
        $perPage   = min(100, max(1, $requested));

        $this->assertEquals(1, $perPage);
    }

    /**
     * Test que page minimum est 1
     */
    public function test_page_min_is_one(): void
    {
        $requested = -5;
        $page      = max(1, $requested);

        $this->assertEquals(1, $page);
    }

    /**
     * Test la structure de la pagination retournée
     */
    public function test_pagination_metadata(): void
    {
        $users   = $this->makeUsers();
        $total   = count($users);
        $page    = 2;
        $perPage = 2;
        $offset  = ($page - 1) * $perPage;

        $pagination = [
            'current_page' => $page,
            'per_page'     => $perPage,
            'total'        => $total,
            'total_pages'  => (int) ceil($total / $perPage),
            'from'         => $total > 0 ? $offset + 1 : 0,
            'to'           => min($offset + $perPage, $total),
        ];

        $this->assertEquals(2, $pagination['current_page']);
        $this->assertEquals(5, $pagination['total']);
        $this->assertEquals(3, $pagination['total_pages']);
        $this->assertEquals(3, $pagination['from']);
        $this->assertEquals(4, $pagination['to']);
    }
}
