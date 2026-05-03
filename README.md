# MJ Vaubécourt — Site Web

Site officiel du club de handball MJ Vaubécourt. Construit avec Symfony 7.4, Tailwind CSS, Alpine.js et Turbo.

## Stack technique

- **Backend** : PHP 8.2 + Symfony 7.4
- **Frontend** : Webpack Encore, Tailwind CSS 4, DaisyUI, Alpine.js, Hotwired Turbo
- **Base de données** : PostgreSQL 16
- **Conteneurisation** : Docker + Docker Compose

## Prérequis

- [Docker](https://www.docker.com/) et Docker Compose
- [Make](https://www.gnu.org/software/make/)

## Installation avec Docker (recommandé)

```bash
# Cloner le dépôt
git clone <url-du-repo>
cd mjv-web

# Construire les images Docker
make install

# Démarrer les conteneurs
make up

# Lancer les migrations
make migrate
```

L'application est disponible sur [http://localhost:8000](http://localhost:8000).

## Installation manuelle (sans Docker)

### Prérequis supplémentaires

- PHP 8.2 avec les extensions `pdo_pgsql`, `intl`, `zip`
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) 20+ et npm
- PostgreSQL 16

### Étapes

```bash
# 1. Installer les dépendances PHP
composer install

# 2. Installer les dépendances Node.js
npm install

# 3. Configurer l'environnement
cp .env .env.local
# Éditer .env.local et renseigner les variables ci-dessous

# 4. Créer la base de données et lancer les migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# 5. Charger les données de test (optionnel)
php bin/console doctrine:fixtures:load

# 6. Compiler les assets frontend (en mode watch)
npm run watch

# 7. Démarrer le serveur de développement (dans un autre terminal)
php -S 127.0.0.1:8000 -t public
```

## Variables d'environnement

Copier `.env` en `.env.local` et adapter les valeurs :

| Variable | Description | Valeur par défaut |
|---|---|---|
| `APP_ENV` | Environnement (`dev` / `prod`) | `dev` |
| `APP_SECRET` | Clé secrète Symfony | — |
| `DATABASE_URL` | URL de connexion PostgreSQL | `postgresql://app:!ChangeMe!@localhost:5432/app?serverVersion=16&charset=utf8` |
| `POSTGRES_DB` | Nom de la base de données | `app` |
| `POSTGRES_USER` | Utilisateur PostgreSQL | `app` |
| `POSTGRES_PASSWORD` | Mot de passe PostgreSQL | `!ChangeMe!` |

## Commandes Make disponibles

| Commande | Description |
|---|---|
| `make install` | Construit les images Docker |
| `make build` | Reconstruit et démarre les conteneurs |
| `make up` | Démarre les conteneurs existants |
| `make stop` | Arrête les conteneurs |
| `make migrate` | Applique les migrations de base de données |
| `make shell` | Ouvre un shell dans le conteneur PHP |

## Scripts npm disponibles

| Commande | Description |
|---|---|
| `npm run dev` | Compile les assets une fois (développement) |
| `npm run watch` | Recompile automatiquement à chaque modification |
| `npm run dev-server` | Serveur de développement avec hot reload |
| `npm run build` | Compile les assets pour la production |

## Fonctionnalités

- **Équipes** — présentation des équipes par catégorie (jeunes, seniors, etc.)
- **Actualités** — articles de blog avec pagination et commentaires
- **Forum** — discussions hiérarchiques
- **Calendrier** — planning des matchs à venir
- **Contact** — formulaire de contact avec protection reCAPTCHA
- **Administration** — back-office pour la gestion du contenu
