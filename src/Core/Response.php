<?php

namespace Calvino\Core;

/**
 * Classe Response
 * Gère les réponses HTTP
 */
class Response
{
    /**
     * Code de statut HTTP
     *
     * @var int
     */
    private int $statusCode = 200;

    /**
     * En-têtes de la réponse
     *
     * @var array
     */
    private array $headers = [];

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->headers = [
            'Content-Type' => 'text/html; charset=UTF-8'
        ];
    }

    /**
     * Définit le code de statut HTTP
     *
     * @param int $statusCode
     * @return self
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Ajoute un en-tête HTTP
     *
     * @param string $name
     * @param string $value
     * @return self
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Envoie tous les en-têtes
     */
    private function sendHeaders(): void
    {
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
    }

    /**
     * Envoie le contenu et les en-têtes
     *
     * @param mixed $content
     * @return void
     */
    public function send($content): void
    {
        $this->sendHeaders();
        echo $content;
        exit;
    }

    /**
     * Envoie une réponse JSON
     *
     * @param mixed $data
     * @return void
     */
    public function json($data): void
    {
        $this->setHeader('Content-Type', 'application/json');
        $this->send(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Redirige vers une URL
     *
     * @param string $url
     * @return void
     */
    public function redirect(string $url): void
    {
        $this->setStatusCode(302);
        $this->setHeader('Location', $url);
        $this->send('');
    }

    /**
     * Réponse non trouvée (404)
     *
     * @param string $message
     * @return void
     */
    public function notFound(string $message = 'Not Found'): void
    {
        $this->setStatusCode(404);
        $this->json(['error' => true, 'message' => $message, 'status' => 404]);
    }

    /**
     * Réponse non autorisée (401)
     *
     * @param string $message
     * @return void
     */
    public function unauthorized(string $message = 'Unauthorized'): void
    {
        $this->setStatusCode(401);
        $this->json(['error' => true, 'message' => $message, 'status' => 401]);
    }

    /**
     * Réponse interdite (403)
     *
     * @param string $message
     * @return void
     */
    public function forbidden(string $message = 'Forbidden'): void
    {
        $this->setStatusCode(403);
        $this->json(['error' => true, 'message' => $message, 'status' => 403]);
    }

    /**
     * Réponse erreur serveur (500)
     *
     * @param string $message
     * @return void
     */
    public function error(string $message = 'Internal Server Error'): void
    {
        $this->setStatusCode(500);
        $this->json(['error' => true, 'message' => $message, 'status' => 500]);
    }
} 