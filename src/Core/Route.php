<?php

namespace Calvino\Core;

/**
 * Classe Route
 * Représente une route de l'application
 */
class Route
{
    /**
     * Méthode HTTP
     *
     * @var string
     */
    private string $method;

    /**
     * Chemin de la route
     *
     * @var string
     */
    private string $path;

    /**
     * Action de la route (contrôleur et méthode)
     *
     * @var array
     */
    private array $action;

    /**
     * Paramètres de la route
     *
     * @var array
     */
    private array $params = [];

    /**
     * Middlewares de la route
     *
     * @var array
     */
    private array $middlewares = [];

    /**
     * Constructeur
     *
     * @param string $method
     * @param string $path
     * @param array|string $action
     */
    public function __construct(string $method, string $path, $action)
    {
        $this->method = $method;
        $this->path = $path;
        
        if ($action instanceof \Closure) {
            $this->action = [$action];
        } elseif (is_array($action)) {
            $this->action = $action;
        } elseif (is_string($action) && strpos($action, '@') !== false) {
            list($controller, $method) = explode('@', $action);
            $this->action = [
                'controller' => $controller,
                'method' => $method
            ];
        } else {
            throw new \InvalidArgumentException("L'action doit être une closure, un tableau ou une chaîne au format 'Controller@method'");
        }
    }

    /**
     * Vérifie si la route correspond à la méthode et au chemin
     *
     * @param string $method
     * @param string $path
     * @return bool
     */
    public function matches(string $method, string $path): bool
    {
        // Vérifier si la méthode correspond ou si c'est ANY
        if ($this->method !== 'ANY' && $this->method !== $method) {
            return false;
        }

        // Nettoyer les slashes de début et de fin pour une comparaison cohérente
        $normalizedPath = trim($path, '/');
        $normalizedRoutePath = trim($this->path, '/');
        
        if ($normalizedPath === $normalizedRoutePath) {
            $this->params = [];
            return true;
        }

        if (preg_match($this->pathToRegex($normalizedRoutePath), $normalizedPath, $matches)) {
            array_shift($matches);
            $this->params = $matches;
            return true;
        }

        return false;
    }

    /**
     * Convertit un chemin de route en expression régulière.
     * Les segments littéraux sont échappés : sans preg_quote, le point de
     * « /report.pdf » acceptait n'importe quel caractère.
     */
    private function pathToRegex(string $path): string
    {
        $regex = preg_replace('/\\\\\{([a-zA-Z0-9_]+)\\\\\}/', '([^/]+)', preg_quote($path, '#'));

        return "#^{$regex}$#";
    }

    /**
     * Ajoute un middleware à la route
     *
     * @param string $middleware
     * @return self
     */
    public function middleware(string $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Ajoute des middlewares à la route
     *
     * @param array $middlewares
     * @return self
     */
    public function middlewares(array $middlewares): self
    {
        $this->middlewares = array_merge($this->middlewares, $middlewares);
        return $this;
    }

    /**
     * Obtenir la méthode HTTP
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Obtenir le chemin
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Obtenir le contrôleur
     *
     * @return string
     */
    public function getController(): string
    {
        // Si c'est une closure, il n'y a pas de contrôleur
        if (isset($this->action[0]) && $this->action[0] instanceof \Closure) {
            return '';
        }
        
        return $this->action['controller'] ?? '';
    }

    /**
     * Obtenir l'action
     *
     * @return string
     */
    public function getAction(): string
    {
        // Si c'est une closure, il n'y a pas de méthode
        if (isset($this->action[0]) && $this->action[0] instanceof \Closure) {
            return '';
        }
        
        return $this->action['method'] ?? '';
    }

    /**
     * Vérifie si la route utilise une closure
     *
     * @return bool
     */
    public function hasClosure(): bool
    {
        return isset($this->action[0]) && $this->action[0] instanceof \Closure;
    }
    
    /**
     * Récupère la closure si disponible
     *
     * @return \Closure|null
     */
    public function getClosure(): ?\Closure
    {
        return isset($this->action[0]) && $this->action[0] instanceof \Closure ? $this->action[0] : null;
    }

    /**
     * Obtenir les paramètres
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Obtenir les middlewares
     *
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
} 