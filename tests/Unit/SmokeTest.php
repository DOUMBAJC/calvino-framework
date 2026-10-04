<?php

it('charge le framework par l\'autoloader', function () {
    expect(class_exists(\Calvino\Core\Router::class))->toBeTrue();
});
