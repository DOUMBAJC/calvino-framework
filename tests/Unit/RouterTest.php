<?php

use Calvino\Core\Router;

beforeEach(function () {
    $this->router = new Router();
});

it('résout une route statique pour la bonne méthode', function () {
    $this->router->get('/health', 'HealthController@show');

    $route = $this->router->resolve(makeRequest('GET', '/health'));

    expect($route)->not->toBeNull()
        ->and($route->getController())->toBe('HealthController')
        ->and($route->getAction())->toBe('show');
});

it('ne résout pas une route enregistrée pour une autre méthode', function () {
    $this->router->post('/users', 'UserController@store');

    expect($this->router->resolve(makeRequest('GET', '/users')))->toBeNull();
});

it('renvoie null quand aucune route ne correspond', function () {
    $this->router->get('/users', 'UserController@index');

    expect($this->router->resolve(makeRequest('GET', '/nope')))->toBeNull();
});

it('extrait les paramètres de chemin dans l\'ordre', function () {
    $this->router->get('/users/{user}/posts/{post}', 'PostController@show');

    $route = $this->router->resolve(makeRequest('GET', '/users/42/posts/7'));

    expect($route->getParams())->toBe(['42', '7']);
});

it('ignore la chaîne de requête et le slash final', function () {
    $this->router->get('/users/{id}', 'UserController@show');

    $route = $this->router->resolve(makeRequest('GET', '/users/42/?tab=posts'));

    expect($route)->not->toBeNull()
        ->and($route->getParams())->toBe(['42']);
});

it('ne laisse pas un paramètre déborder sur le segment suivant', function () {
    $this->router->get('/users/{id}', 'UserController@show');

    expect($this->router->resolve(makeRequest('GET', '/users/42/posts')))->toBeNull();
});

it('traite les caractères du chemin comme littéraux', function () {
    $this->router->get('/files/report.pdf', 'FileController@show');

    expect($this->router->resolve(makeRequest('GET', '/files/report.pdf')))->not->toBeNull()
        ->and($this->router->resolve(makeRequest('GET', '/files/reportXpdf')))->toBeNull();
});

it('accepte toutes les méthodes sur une route any', function (string $method) {
    $this->router->any('/webhook', 'WebhookController@handle');

    expect($this->router->resolve(makeRequest($method, '/webhook')))->not->toBeNull();
})->with(['GET', 'POST', 'PUT', 'DELETE']);

it('applique la surcharge de méthode sur un POST', function () {
    $this->router->put('/users/{id}', 'UserController@update');

    $request = makeRequest('POST', '/users/3', ['HTTP_X_HTTP_METHOD_OVERRIDE' => 'PUT']);

    expect($this->router->resolve($request))->not->toBeNull();
});

it('préfixe et range dans un espace de noms les routes d\'un groupe', function () {
    $this->router->group(['prefix' => '/api/v1', 'namespace' => 'App\\Controllers'], function () {
        $this->router->get('/users', 'UserController@index');
    });

    $route = $this->router->resolve(makeRequest('GET', '/api/v1/users'));

    expect($route->getPath())->toBe('/api/v1/users')
        ->and($route->getController())->toBe('App\\Controllers\\UserController');
});

it('emboîte les groupes et restaure les attributs à la sortie', function () {
    $this->router->group(['prefix' => 'api', 'middleware' => ['auth']], function () {
        $this->router->group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
            $this->router->get('/stats', 'StatsController@index');
        });
        $this->router->get('/me', 'MeController@show');
    });
    $this->router->get('/public', 'PublicController@index');

    [$stats, $me, $public] = $this->router->getRoutes();

    expect($stats->getPath())->toBe('api/admin/stats')
        ->and($stats->getMiddlewares())->toBe(['auth', 'admin'])
        ->and($me->getMiddlewares())->toBe(['auth'])
        ->and($public->getPath())->toBe('/public')
        ->and($public->getMiddlewares())->toBe([]);
});

it('enregistre une route sous forme de closure', function () {
    $this->router->get('/ping', fn () => 'pong');

    $route = $this->router->resolve(makeRequest('GET', '/ping'));

    expect($route->hasClosure())->toBeTrue()
        ->and(($route->getClosure())())->toBe('pong');
});

it('refuse une action qui n\'est ni un tableau ni Controller@method', function () {
    $this->router->get('/broken', 'NotAnAction');
})->throws(InvalidArgumentException::class);
