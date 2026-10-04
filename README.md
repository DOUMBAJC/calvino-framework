# Calvino Framework

[![tests](https://github.com/DOUMBAJC/calvino-framework/actions/workflows/tests.yml/badge.svg)](https://github.com/DOUMBAJC/calvino-framework/actions/workflows/tests.yml)

A small PHP framework for JSON APIs, written from scratch: a router, models over a query builder, schema migrations and a CLI. About 10,000 lines of PHP, with no runtime dependency beyond PDO.

## Requirements

PHP 8.2 or later, with `pdo`, `json` and `mbstring`. The connection uses MySQL by default (`DB_CONNECTION`).

## Installation

```bash
composer require calvino/framework
```

To start a new project, use the template: [calvino-calvino](https://github.com/DOUMBAJC/calvino-calvino).

## Routes

Routes live in `routes/api.php`. They load under the API prefix, with controllers resolved in `Calvino\Controllers\Api`.

```php
$router->get('/health', fn () => ['status' => 'ok']);

$router->get('/users/{id}', 'UserController@show');
$router->post('/users', 'UserController@store');

$router->group(['prefix' => 'admin', 'middleware' => ['AuthMiddleware', 'AdminMiddleware']], function () use ($router) {
    $router->get('/stats', 'StatsController@index');
});
```

A handler that returns an array is sent as JSON. Path parameters are passed to the controller in order, after the request.

## Controllers and validation

```php
use Calvino\Core\Controller;
use Calvino\Core\Request;

class UserController extends Controller
{
    public function show(Request $request, string $id): array
    {
        return ['data' => User::find($id)];
    }

    public function store(Request $request): array
    {
        $request->setRules([
            'name'  => 'required|max:120',
            'email' => 'required|email',
            'age'   => 'required|integer|min:18',
        ]);

        if ($request->fails()) {
            return ['errors' => $request->getErrors()];
        }

        return ['data' => User::create($request->validated())];
    }
}
```

The rules are `required`, `min`, `max`, `email`, `numeric`, `integer`, `date`, `in`, `boolean` and `url`. `min` and `max` compare a string's length, and a field's value when it also carries `numeric` or `integer`.

## Models

```php
use Calvino\Core\Model;

class User extends Model
{
    protected string $table = 'users';
    protected array $fillable = ['name', 'email', 'role'];

    public function posts(): array
    {
        return $this->hasMany(Post::class);
    }
}

$user = User::create(['name' => 'Aïssa', 'email' => 'aissa@example.com']);
$user->role = 'tenant';
$user->save();

$landlords = User::where('role', 'landlord')
    ->where('id', 100, '>')
    ->limit(20)
    ->get();
```

`where()` takes the value before the operator. Values are always bound through prepared statements. The operator must be one of `=`, `!=`, `<>`, `<`, `<=`, `>`, `>=`, `LIKE` or `NOT LIKE`, and the column must be a plain identifier.

Only `$fillable` attributes are mass-assignable. The primary key never is, so a request body carrying an `id` cannot overwrite another row. For UUID keys, set `protected string $keyType = 'string'` and `public bool $incrementing = false`.

## Migrations

```php
use Calvino\Core\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $this->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->enum('role', ['tenant', 'landlord'])->default('tenant');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->dropIfExists('users');
    }
};
```

## CLI

```bash
vendor/bin/calvino help                              # every command
vendor/bin/calvino make:migration User
vendor/bin/calvino migrate
vendor/bin/calvino route:list
vendor/bin/calvino serve
```

| Group | Commands |
|---|---|
| Scaffolding | `make:controller` · `make:model` · `make:migration` · `make:middleware` · `make:command` |
| Migrations | `migrate` · `migrate:rollback` · `db:seed` · `db:refresh` |
| Database | `db:create` · `db:check` · `db:tables` · `db:structure` · `db:query` · `db:export` · `db:import` · `db:add-column` · `db:modify-column` · `db:drop-column` · `db:drop-table` |
| Tools | `route:list` · `serve` · `help` |

## Tests

```bash
composer test
```

The Pest suite runs on an in-memory SQLite database, so it needs no server. CI runs it on PHP 8.2, 8.3 and 8.4.

## Releases

Date the top section of `CHANGELOG.md` (`## 2.1.0 — 2026-11-02`) and merge into `main`. Once the tests pass, the `release` workflow creates the tag and the GitHub release, and Packagist picks up the version.

## License

MIT. Contributions: see [CONTRIBUTING.md](CONTRIBUTING.md).
