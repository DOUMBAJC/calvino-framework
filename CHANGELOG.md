# Changelog

## Unreleased

### Security

- The primary key is no longer mass-assignable. `User::create($request->all())` with an `id` in the body used to overwrite another row on `save()`.
- `QueryBuilder::where()` only accepts whitelisted operators (`=`, `!=`, `<>`, `<`, `<=`, `>`, `>=`, `LIKE`, `NOT LIKE`) and plain identifiers as column names. Both used to be concatenated into the SQL as-is.

### Fixed

- Closure routes (`$router->get('/ping', fn () => …)`) threw `InvalidArgumentException`.
- Literal route segments were read as regular expressions: `/report.pdf` also matched `/reportXpdf`.
- A string action without `@` produced a warning and a broken route; it now throws `InvalidArgumentException`.
- `Content-Type` and `Content-Length` were never read, so `Request::isJson()` was always false.
- `X-HTTP-Method-Override` was case-sensitive: `delete` matched no route.
- `numeric|min:18` compared the length of `"25"` instead of its value. `min` and `max` now compare the value when the field also carries `numeric` or `integer`.
- Models read from the database dropped every column outside `$fillable` (`created_at`, for instance).
- A `.env` line without `=` raised a warning and defined an empty variable; it is now skipped.
- The default database name was `pharmacie`, left over from another project; it is now `calvino`.

### Added

- `Model::setConnection(PDO $pdo)` to inject the connection.
- `Model::fromRecord(array $record)` to build a model from a database row.
- A Pest suite on in-memory SQLite (`composer test`), run in CI on PHP 8.2, 8.3 and 8.4.

### Changed

- `new Model(['id' => 5, …])->save()` now inserts a new row instead of updating row 5. Load the model first (`Model::find(5)`), or assign the key explicitly (`$model->id = 5`).

## 1.0.1

First published version.
