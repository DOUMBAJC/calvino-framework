<?php

use Tests\Fixtures\User;

beforeEach(function () {
    freshDatabase();
    foreach ([['Aïssa', 'locataire'], ['Amadou', 'bailleur'], ['Bello', 'bailleur'], ['Calvino', 'agent']] as [$name, $role]) {
        User::create(['name' => $name, 'role' => $role]);
    }
});

it('filtre par égalité', function () {
    $landlords = User::where('role', 'bailleur')->get();

    expect($landlords)->toHaveCount(2)
        ->and($landlords[0])->toBeInstanceOf(User::class);
});

it('enchaîne plusieurs conditions avec AND', function () {
    $users = User::where('role', 'bailleur')->where('name', 'Bello')->get();

    expect($users)->toHaveCount(1)
        ->and($users[0]->name)->toBe('Bello');
});

it('accepte les opérateurs de comparaison', function () {
    expect(User::where('id', 2, '>')->get())->toHaveCount(2)
        ->and(User::where('name', 'A%', 'like')->get())->toHaveCount(2);
});

it('rend le premier résultat ou null', function () {
    expect(User::where('role', 'agent')->first()->name)->toBe('Calvino')
        ->and(User::where('role', 'admin')->first())->toBeNull();
});

it('limite le nombre de lignes et choisit les colonnes', function () {
    $users = User::where('role', 'bailleur')->select(['id', 'name'])->limit(1)->get();

    expect($users)->toHaveCount(1)
        ->and($users[0]->role)->toBeNull();
});

it('lie les valeurs au lieu de les concaténer', function () {
    expect(User::where('name', "x' OR '1'='1")->get())->toBeEmpty();
});

it('refuse un opérateur hors liste', function () {
    User::where('id', 1, '= 1 OR 1=1 --')->get();
})->throws(InvalidArgumentException::class);

it('refuse un nom de colonne qui n\'est pas un identifiant', function () {
    User::where('id = 1 OR 1', 1)->get();
})->throws(InvalidArgumentException::class);
