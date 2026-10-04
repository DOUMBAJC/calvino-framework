# Chantier : rendre calvino-framework présentable

Fichier temporaire : il se supprime à la dernière étape, avant le merge.
Il sert à reprendre le travail si une session s'arrête en cours de route.

**Branche** : `calvino_claude/tender-keller-7ouwh0`
**But** : pouvoir épingler le repo sur le profil GitHub, puis décommenter le bloc
« Also » du README de profil (`DOUMBAJC/DOUMBAJC`).

## Étapes (dans l'ordre, une étape = un commit poussé)

- [x] 1. Outillage de test : Pest 3 en `require-dev`, `phpunit.xml`, `tests/Pest.php`, script `composer test`.
- [x] 2. Tests du routeur (`src/Core/Router.php`, `Route.php`) : correspondance, paramètres, méthodes, 404.
- [ ] 3. Tests de `Env`, `Request`, `Response` (sans réseau ni base).
- [ ] 4. Tests du `QueryBuilder` et du `Model` sur SQLite en mémoire.
- [ ] 5. CI GitHub Actions : PHP 8.2, 8.3, 8.4, `composer test`.
- [ ] 6. README en anglais : ce que c'est, installation, démarrage rapide, routes, modèles, migrations, CLI, tests.
- [ ] 7. Relecture finale, suppression de ce fichier, compte rendu à Calvino.

## Journal

Les bugs trouvés par les tests et leur correctif s'écrivent ici, une ligne chacun.
- Routeur : une route closure (`$router->get('/x', fn () => …)`) levait InvalidArgumentException. Corrigé dans `Router::addRoute` et `Route::__construct`.
- Routeur : les segments littéraux n'étaient pas échappés, `/report.pdf` acceptait `/reportXpdf`. Corrigé par `preg_quote` dans `Route::pathToRegex`.
- Routeur : une action sans `@` produisait un avertissement et une route cassée au lieu d'une erreur. Elle lève maintenant InvalidArgumentException.
