# Chantier : rendre calvino-framework présentable

Fichier temporaire : il se supprime à la dernière étape, avant le merge.
Il sert à reprendre le travail si une session s'arrête en cours de route.

**Branche** : `calvino_claude/tender-keller-7ouwh0`
**But** : pouvoir épingler le repo sur le profil GitHub, puis décommenter le bloc
« Also » du README de profil (`DOUMBAJC/DOUMBAJC`).

## Étapes (dans l'ordre, une étape = un commit poussé)

- [x] 1. Outillage de test : Pest 3 en `require-dev`, `phpunit.xml`, `tests/Pest.php`, script `composer test`.
- [x] 2. Tests du routeur (`src/Core/Router.php`, `Route.php`) : correspondance, paramètres, méthodes, 404.
- [x] 3. Tests de `Env` et `Request` (sans réseau ni base). `Response` n'est pas testé : `send()` appelle `exit`, il faudrait d'abord le refactorer.
- [x] 4. Tests du `QueryBuilder` et du `Model` sur SQLite en mémoire.
- [ ] 5. CI GitHub Actions : PHP 8.2, 8.3, 8.4, `composer test`.
- [ ] 6. README en anglais : ce que c'est, installation, démarrage rapide, routes, modèles, migrations, CLI, tests.
- [ ] 7. Relecture finale, suppression de ce fichier, compte rendu à Calvino.

## Journal

Les bugs trouvés par les tests et leur correctif s'écrivent ici, une ligne chacun.
- Routeur : une route closure (`$router->get('/x', fn () => …)`) levait InvalidArgumentException. Corrigé dans `Router::addRoute` et `Route::__construct`.
- Routeur : les segments littéraux n'étaient pas échappés, `/report.pdf` acceptait `/reportXpdf`. Corrigé par `preg_quote` dans `Route::pathToRegex`.
- Routeur : une action sans `@` produisait un avertissement et une route cassée au lieu d'une erreur. Elle lève maintenant InvalidArgumentException.
- Request : `Content-Type` et `Content-Length` n'étaient jamais lus (PHP les range sans préfixe `HTTP_`), donc `isJson()` restait faux. Corrigé dans `getHeaderParams`.
- Request : `X-HTTP-Method-Override: delete` en minuscules ne correspondait à aucune route. La méthode est passée en majuscules.
- Request : `numeric|min:18` comparait la longueur de « 25 » (2) au lieu de sa valeur. `min`/`max` comparent la valeur quand le champ porte `numeric` ou `integer`.
- Env : une ligne sans `=` levait un avertissement et créait une variable vide. Elle est ignorée.
- `phpunit.xml` échoue désormais sur tout avertissement ou dépréciation PHP.
- Model : aucun moyen d'injecter une connexion. Ajout de `Model::setConnection(PDO)`.
- Model (sécurité) : `fill()` acceptait la clé primaire, donc `User::create($request->all())` avec un `id` dans le corps réécrivait une autre ligne. La clé primaire n'est plus mass-assignable.
- Model : une ligne lue en base passait par le constructeur, filtré par `fillable` : `created_at` et toute colonne hors fillable étaient perdus. Nouveau `Model::fromRecord()`, utilisé partout où une ligne de base devient un modèle (Model, QueryBuilder, User, UserSession, Auth).
- QueryBuilder (sécurité) : l'opérateur et la colonne de `where()` entraient tels quels dans le SQL. Opérateurs en liste blanche, colonne validée comme identifiant.
