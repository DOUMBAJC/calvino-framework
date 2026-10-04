<?php

use Tests\Fixtures\Post;
use Tests\Fixtures\Token;
use Tests\Fixtures\User;

beforeEach(function () {
    $this->pdo = freshDatabase();
});

it('insère un modèle et récupère son identifiant', function () {
    $user = User::create(['name' => 'Aïssa', 'email' => 'aissa@dada.cm', 'role' => 'locataire']);

    expect((int) $user->id)->toBe(1)
        ->and($this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn())->toBe(1);
});

it('retrouve un modèle avec toutes ses colonnes, même hors fillable', function () {
    User::create(['name' => 'Amadou', 'role' => 'bailleur']);

    $user = User::find(1);

    expect($user->name)->toBe('Amadou')
        ->and($user->created_at)->not->toBeNull();
});

it('renvoie null pour un identifiant inconnu', function () {
    expect(User::find(404))->toBeNull();
});

it('met à jour un modèle existant', function () {
    $user = User::create(['name' => 'Amadou', 'role' => 'bailleur']);
    $user->role = 'agent';
    $user->save();

    expect(User::find($user->id)->role)->toBe('agent')
        ->and(User::all())->toHaveCount(1);
});

it('supprime un modèle', function () {
    $user = User::create(['name' => 'Temporaire']);

    expect($user->delete())->toBeTrue()
        ->and(User::find($user->id))->toBeNull();
});

it('ignore les attributs hors fillable à l\'affectation de masse', function () {
    $user = new User(['name' => 'Aïssa', 'is_admin' => true]);

    expect($user->jsonSerialize())->toBe(['name' => 'Aïssa']);
});

it('refuse de fixer la clé primaire par affectation de masse', function () {
    $victim = User::create(['name' => 'Victime', 'role' => 'admin']);

    // Un corps de requête qui porte un id ne doit pas réécrire une autre ligne.
    User::create(['id' => $victim->id, 'name' => 'Intrus', 'role' => 'locataire']);

    expect(User::find($victim->id)->name)->toBe('Victime')
        ->and(User::all())->toHaveCount(2);
});

it('génère une clé UUID v4 pour une clé non incrémentale', function () {
    $token = Token::create(['label' => 'api']);

    expect($token->id)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/')
        ->and(Token::find($token->id)->label)->toBe('api');
});

it('suit les relations hasMany et belongsTo', function () {
    $author = User::create(['name' => 'Calvino']);
    Post::create(['user_id' => $author->id, 'title' => 'Premier']);
    Post::create(['user_id' => $author->id, 'title' => 'Second']);

    expect($author->posts())->toHaveCount(2)
        ->and(Post::find(1)->user()->name)->toBe('Calvino');
});

it('sérialise ses attributs en JSON', function () {
    $user = new User(['name' => 'Aïssa', 'role' => 'locataire']);

    expect(json_encode($user, JSON_UNESCAPED_UNICODE))->toBe('{"name":"Aïssa","role":"locataire"}');
});
