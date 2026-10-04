<?php

it('expose la méthode, le chemin et la chaîne de requête', function () {
    $request = makeRequest('GET', '/search?q=garoua&page=2');

    expect($request->getMethod())->toBe('GET')
        ->and($request->getPath())->toBe('/search')
        ->and($request->query('q'))->toBe('garoua')
        ->and($request->query('missing', 'défaut'))->toBe('défaut');
});

it('lit le corps d\'un formulaire', function () {
    $request = makeRequest('POST', '/users', [], ['name' => 'Aïssa', 'role' => 'locataire']);

    expect($request->input('name'))->toBe('Aïssa')
        ->and($request->all())->toBe(['name' => 'Aïssa', 'role' => 'locataire']);
});

it('normalise les en-têtes HTTP', function () {
    $request = makeRequest('GET', '/', ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest', 'HTTP_ACCEPT_LANGUAGE' => 'fr']);

    expect($request->header('Accept-Language'))->toBe('fr')
        ->and($request->isAjax())->toBeTrue();
});

it('reconnaît une requête JSON par son Content-Type', function () {
    // PHP range Content-Type dans CONTENT_TYPE, sans le préfixe HTTP_.
    $request = makeRequest('POST', '/api', ['CONTENT_TYPE' => 'application/json; charset=utf-8']);

    expect($request->isJson())->toBeTrue()
        ->and($request->header('Content-Type'))->toBe('application/json; charset=utf-8');
});

it('accepte une surcharge de méthode en minuscules', function () {
    $request = makeRequest('POST', '/users/1', ['HTTP_X_HTTP_METHOD_OVERRIDE' => 'delete']);

    expect($request->getMethod())->toBe('DELETE');
});

it('valide des données conformes', function () {
    $request = makeRequest('POST', '/users', [], ['email' => 'a@dada.cm', 'role' => 'bailleur']);
    $request->setRules(['email' => 'required|email', 'role' => 'required|in:locataire,bailleur']);

    expect($request->validate())->toBeTrue()
        ->and($request->passes())->toBeTrue()
        ->and($request->getErrors())->toBe([]);
});

it('signale chaque champ invalide', function () {
    $request = makeRequest('POST', '/users', [], ['email' => 'pas-un-email', 'role' => 'admin']);
    $request->setRules(['name' => 'required', 'email' => 'email', 'role' => 'in:locataire,bailleur']);

    expect($request->validate())->toBeFalse()
        ->and($request->hasError('name'))->toBeTrue()
        ->and($request->hasError('email'))->toBeTrue()
        ->and($request->hasError('role'))->toBeTrue()
        ->and($request->getFirstError('name'))->toBeString();
});

it('utilise le message personnalisé d\'une règle', function () {
    $request = makeRequest('POST', '/users');
    $request->setRules(['phone' => 'required'])->setMessages(['phone.required' => 'Le :attribute est obligatoire.']);

    $request->validate();

    expect($request->getFirstError('phone'))->toBe('Le phone est obligatoire.');
});

it('compare min et max à la longueur d\'une chaîne', function () {
    $request = makeRequest('POST', '/users', [], ['password' => 'court']);
    $request->setRules(['password' => 'min:8']);

    expect($request->validate())->toBeFalse();
});

it('compare min et max à la valeur d\'un champ numérique venu d\'un formulaire', function () {
    // Tout champ de formulaire arrive en chaîne : « 25 » fait 2 caractères.
    $request = makeRequest('POST', '/users', [], ['age' => '25', 'rooms' => '12']);
    $request->setRules(['age' => 'required|numeric|min:18', 'rooms' => 'integer|max:10']);

    expect($request->validate())->toBeFalse()
        ->and($request->hasError('age'))->toBeFalse()
        ->and($request->hasError('rooms'))->toBeTrue();
});

it('ne rend que les champs validés', function () {
    $request = makeRequest('POST', '/users', [], ['name' => 'Amadou', 'is_admin' => '1']);
    $request->setRules(['name' => 'required']);

    expect($request->validated())->toBe(['name' => 'Amadou']);
});
