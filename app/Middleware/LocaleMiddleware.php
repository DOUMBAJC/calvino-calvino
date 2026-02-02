<?php

namespace App\Middleware;

class LocaleMiddleware
{
    public function handle($request, $next)
    {
        // Récupérer la langue depuis les headers HTTP
        $locale = $request->getHeader('Accept-Language');
        
        if ($locale) {
            // Extraire le code de langue principal (ex: 'fr-FR' => 'fr')
            $locale = substr($locale, 0, 2);
            
            // Vérifier si la langue est disponible
            $availableLocales = config('app.available_locales', ['fr', 'en']);
            
            if (in_array($locale, $availableLocales)) {
                // Définir la langue pour cette requête
                app()->setLocale($locale);
            }
        }
        
        return $next($request);
    }
} 