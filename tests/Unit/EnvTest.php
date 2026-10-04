<?php

use Calvino\Core\Env;

beforeEach(function () {
    $this->file = tempnam(sys_get_temp_dir(), 'env');
    $this->keys = [];
    $this->load = function (string $content): void {
        file_put_contents($this->file, $content);
        preg_match_all('/^\s*([A-Z_]+)=/m', $content, $m);
        $this->keys = $m[1];
        (new Env())->load($this->file);
    };
});

afterEach(function () {
    foreach ($this->keys as $key) {
        putenv($key);
        unset($_ENV[$key], $_SERVER[$key]);
    }
    @unlink($this->file);
});

it('charge les paires clé=valeur et ignore les commentaires', function () {
    ($this->load)("# commentaire\nCLV_APP_NAME=Calvino\nCLV_PORT = 8000\n");

    expect(getenv('CLV_APP_NAME'))->toBe('Calvino')
        ->and(getenv('CLV_PORT'))->toBe('8000')
        ->and($_ENV['CLV_APP_NAME'])->toBe('Calvino');
});

it('retire les guillemets et garde le signe égal dans la valeur', function () {
    ($this->load)("CLV_QUOTED=\"Bonjour le monde\"\nCLV_SINGLE='abc'\nCLV_DSN=pgsql:host=db;port=5432\n");

    expect(getenv('CLV_QUOTED'))->toBe('Bonjour le monde')
        ->and(getenv('CLV_SINGLE'))->toBe('abc')
        ->and(getenv('CLV_DSN'))->toBe('pgsql:host=db;port=5432');
});

it('interpole une variable déjà définie', function () {
    ($this->load)("CLV_HOST=localhost\nCLV_URL=http://\${CLV_HOST}:8000\n");

    expect(getenv('CLV_URL'))->toBe('http://localhost:8000');
});

it('n\'écrase pas une variable déjà présente dans l\'environnement', function () {
    putenv('CLV_EXISTING=prod');

    ($this->load)("CLV_EXISTING=local\n");

    expect(getenv('CLV_EXISTING'))->toBe('prod');
});

it('ignore une ligne sans signe égal', function () {
    ($this->load)("CLV_BEFORE=1\nligne cassée\nCLV_AFTER=2\n");

    expect(getenv('CLV_BEFORE'))->toBe('1')
        ->and(getenv('CLV_AFTER'))->toBe('2');
});

it('ne fait rien quand le fichier n\'existe pas', function () {
    (new Env())->load('/chemin/qui/n/existe/pas/.env');

    expect(true)->toBeTrue();
});

it('traduit les valeurs spéciales dans le helper env()', function () {
    ($this->load)("CLV_DEBUG=true\nCLV_CACHE=false\nCLV_MISSING_DRIVER=null\n");

    expect(env('CLV_DEBUG'))->toBeTrue()
        ->and(env('CLV_CACHE'))->toBeFalse()
        ->and(env('CLV_ABSENT', 'défaut'))->toBe('défaut');
});
