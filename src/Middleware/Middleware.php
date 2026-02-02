<?php

namespace Calvino\Middleware;

use Calvino\Core\Request;

/**
 * Interface Middleware
 * Interface pour tous les middlewares
 */
interface Middleware
{
    /**
     * Traite la requête
     *
     * @param Request $request
     * @param callable $next
     * @return mixed
     */
    public function handle(Request $request, callable $next);
}
