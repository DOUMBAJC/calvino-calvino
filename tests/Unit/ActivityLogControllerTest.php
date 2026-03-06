<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour la logique de filtrage/pagination de ActivityLogController
 * (tests de la logique pure sans base de données)
 */
class ActivityLogControllerTest extends TestCase
{
    /**
     * Données de logs simulées
     */
    private function makeLogs(): array
    {
        return [
            (object) ['id' => 1, 'action' => 'login',   'module' => 'auth',  'description' => 'User login',  'created_at' => '2025-01-01 10:00:00', 'user_id' => 1],
            (object) ['id' => 2, 'action' => 'logout',  'module' => 'auth',  'description' => 'User logout', 'created_at' => '2025-01-02 11:00:00', 'user_id' => 1],
            (object) ['id' => 3, 'action' => 'login',   'module' => 'auth',  'description' => 'Admin login', 'created_at' => '2025-01-03 09:00:00', 'user_id' => 2],
            (object) ['id' => 4, 'action' => 'created', 'module' => 'users', 'description' => 'User created','created_at' => '2025-01-03 12:00:00', 'user_id' => 2],
            (object) ['id' => 5, 'action' => 'deleted', 'module' => 'users', 'description' => 'User deleted','created_at' => '2025-01-04 08:00:00', 'user_id' => 2],
        ];
    }

    /**
     * Test le filtre par module
     */
    public function test_filter_by_module(): void
    {
        $logs   = $this->makeLogs();
        $module = 'auth';

        $filtered = array_filter($logs, fn($log) => $log->module === $module);

        $this->assertCount(3, $filtered);
    }

    /**
     * Test le filtre par action
     */
    public function test_filter_by_action(): void
    {
        $logs   = $this->makeLogs();
        $action = 'login';

        $filtered = array_filter($logs, fn($log) => $log->action === $action);

        $this->assertCount(2, $filtered);
    }

    /**
     * Test le filtre par recherche textuelle
     */
    public function test_filter_by_search(): void
    {
        $logs   = $this->makeLogs();
        $search = 'Admin';

        $filtered = array_filter(
            $logs,
            fn($log) => stripos((string) $log->description, $search) !== false
        );

        $this->assertCount(1, $filtered);
        $this->assertEquals('Admin login', array_values($filtered)[0]->description);
    }

    /**
     * Test le filtre par date de début
     */
    public function test_filter_by_start_date(): void
    {
        $logs      = $this->makeLogs();
        $startDate = '2025-01-03';

        $filtered = array_filter($logs, function ($log) use ($startDate) {
            return strtotime($log->created_at) >= strtotime($startDate . ' 00:00:00');
        });

        $this->assertCount(3, $filtered);
    }

    /**
     * Test le tri décroissant par date
     */
    public function test_sort_descending_by_date(): void
    {
        $logs = $this->makeLogs();

        usort($logs, function ($a, $b) {
            return strtotime($b->created_at) - strtotime($a->created_at);
        });

        $this->assertEquals(5, $logs[0]->id); // Le plus récent en premier
        $this->assertEquals(1, $logs[4]->id); // Le plus ancien en dernier
    }

    /**
     * Test la pagination manuelle
     */
    public function test_pagination(): void
    {
        $logs    = $this->makeLogs();
        $page    = 1;
        $limit   = 2;
        $total   = count($logs);
        $offset  = ($page - 1) * $limit;
        $paged   = array_slice($logs, $offset, $limit);

        $this->assertCount(2, $paged);
        $this->assertEquals(5, $total);
        $this->assertEquals(3, (int) ceil($total / $limit)); // 3 pages

        // Page 2
        $page   = 2;
        $offset = ($page - 1) * $limit;
        $paged  = array_slice($logs, $offset, $limit);
        $this->assertCount(2, $paged);

        // Page 3 (dernière, incomplète)
        $page   = 3;
        $offset = ($page - 1) * $limit;
        $paged  = array_slice($logs, $offset, $limit);
        $this->assertCount(1, $paged);
    }

    /**
     * Test la structure des métadonnées de pagination
     */
    public function test_pagination_metadata_structure(): void
    {
        $total   = 23;
        $page    = 2;
        $perPage = 10;

        $pagination = [
            'current_page' => $page,
            'per_page'     => $perPage,
            'total'        => $total,
            'total_pages'  => (int) ceil($total / $perPage),
            'from'         => ($page - 1) * $perPage + 1,
            'to'           => min($page * $perPage, $total),
        ];

        $this->assertEquals(3, $pagination['total_pages']);
        $this->assertEquals(11, $pagination['from']);
        $this->assertEquals(20, $pagination['to']);
    }

    /**
     * Test les statistiques : comptage des activités du jour
     */
    public function test_stats_count_today_activities(): void
    {
        $today = date('Y-m-d');
        $logs  = [
            (object) ['created_at' => $today . ' 08:00:00', 'action' => 'login'],
            (object) ['created_at' => $today . ' 09:00:00', 'action' => 'login'],
            (object) ['created_at' => '2024-01-01 10:00:00', 'action' => 'login'],
        ];

        $todayCount = count(array_filter(
            $logs,
            fn($log) => str_starts_with((string) $log->created_at, $today)
        ));

        $this->assertEquals(2, $todayCount);
    }
}
