# Mahajanga-univ API

API Backend pour la plateforme de l'Université de Mahajanga. Ce projet est basé sur Laravel 12 et fournit une interface robuste pour la gestion des contenus, des documents et de la communication de l'université.

## 🚀 Technologies

- **Framework:** Laravel 12.x
- **PHP:** 8.2+
- **Authentification:** Laravel Sanctum
- **Permissions:** Spatie Laravel Permission
- **Base de données:** MySQL / MariaDB
- **Frontend Assets:** Vite & Tailwind CSS

## 📋 Fonctionnalités

### Core CMS
- **Articles (Posts):** Gestion complète des actualités et articles de l'université.
- **Médias:** Système centralisé de gestion des fichiers et images.
- **Catégories & Tags:** Organisation flexible des contenus.

### Gestion Documentaire
- **Documents:** Dépôt et partage de documents officiels.
- **Catégories de Documents:** Hiérarchie pour l'organisation des ressources.

### Communication
- **Newsletter:** Gestion des abonnés et des campagnes d'envoi.
- **Partenaires:** Vitrine des partenaires institutionnels.

## 🛠️ Installation

1. **Cloner le projet**
   ```bash
   git clone https://github.com/GasyCoder/mahajanga-univ.git
   cd mahajanga-univ
   ```

2. **Installer les dépendances**
   ```bash
   composer install
   npm install
   ```

3. **Configuration de l'environnement**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Base de données**
   Configurez vos accès dans le fichier `.env`, puis :
   ```bash
   php artisan migrate --seed
   ```

5. **Lancer le serveur**
   ```bash
   php artisan serve
   # Et dans un autre terminal pour les assets
   npm run dev
   ```

## 📡 API Endpoints

### Public API (`/api/v1/...`)
- `GET /posts` - Liste des articles
- `GET /categories` - Liste des catégories
- `GET /documents` - Accès aux documents publics
- `POST /newsletter/subscribe` - Inscription à la newsletter

### Admin API (`/api/v1/admin/...`)
*Nécessite une authentification via Sanctum et des permissions appropriées.*

## 📄 Licence

Ce projet est sous licence [MIT](LICENSE).
