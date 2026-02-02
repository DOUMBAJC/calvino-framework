# Calvino Framework Core

[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%5E8.2-blue)](https://php.net)

Le cœur du mini-framework PHP **Calvino**. Ce package contient les composants fondamentaux nécessaires pour faire fonctionner une application basée sur Calvino.

## 🏗️ Architecture

Le framework est conçu pour être modulaire et extensible. Il délègue la définition des modèles à l'application consommatrice tout en fournissant la logique métier via des **Traits**.

### Composants Principaux

- **Core** : Application, Router, Request, Response, Controller, Model, QueryBuilder.
- **Auth** : Gestion de l'authentification JWT et sessions via `Authenticatable`.
- **Traits** : Logiciels réutilisables pour les modèles (`Notifiable`, `ManageSessions`, `LoggableActivity`, `BaseNotification`).
- **Console** : Moteur de CLI avec support d'animations et de génération de code.
- **Providers** : Système de chargement de services (Database, Migration, etc.).

## 📦 Installation

Généralement, vous devriez utiliser le [Calvino Skeleton](https://github.com/calvino/calvino) pour démarrer un nouveau projet. Mais vous pouvez installer le cœur séparément :

```bash
composer require calvino/framework
```

## 🛠️ Utilisation des Traits

Le framework fournit plusieurs traits pour enrichir vos modèles applicatifs :

| Trait | Namespace | Utilisation |
| :--- | :--- | :--- |
| `Authenticatable` | `Calvino\Auth` | Login, vérification password, création de tokens JWT. |
| `Notifiable` | `Calvino\Traits` | Envoi de notifications persistantes à un utilisateur. |
| `ManageSessions` | `Calvino\Traits` | Gestion avancée des sessions (IP, UA, Geo). |
| `LoggableActivity` | `Calvino\Traits` | Enregistrement automatique des logs d'audit. |
| `BaseNotification` | `Calvino\Traits` | Logique CRUD pour le modèle interne de notifications. |

## ⚖️ Licence

Ce projet est sous licence MIT.
