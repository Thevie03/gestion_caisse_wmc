# Déploiement LWS — Gestion Caisse WMC (Laravel 11)

Guide prêt à l'emploi pour l'hébergement **LWS** avec le dossier **`public_html/gestioncaisse`**.

---

## Références du projet

| Élément | Valeur |
|---------|--------|
| Dépôt GitHub | `https://github.com/Thevie03/gestion_caisse_wmc.git` |
| Branche actuelle | `upgrade/laravel-11` (puis `main` après fusion) |
| Dossier serveur | `~/public_html/gestioncaisse` |
| URL application | `https://votre-domaine.com` (document root → `gestioncaisse/public`) |
| PHP requis | **8.2+** |
| Laravel | **11.x** |

---

## 1. Prérequis cPanel LWS

### PHP 8.2 ou 8.3

cPanel → **Select PHP Version** → choisir **8.2** minimum.

### Extensions PHP obligatoires

Activez dans cPanel → **Extensions** :

```
openssl, pdo, pdo_mysql, mbstring, tokenizer, xml, ctype, json,
fileinfo, gd, zip, curl, bcmath
```

### Document root (2 options)

| Option | Configuration |
|--------|---------------|
| **A — Recommandée** | Document root = `public_html/gestioncaisse/public` |
| **B — Sous-dossier** | Document root = `public_html` + `.htaccess` à la racine de `gestioncaisse/` (déjà dans le projet) |

---

## 2. Fichier `.env` sur le serveur

### Création

```bash
cd ~/public_html/gestioncaisse
cp .env.lws.example .env
nano .env   # ou éditeur cPanel
php artisan key:generate
```

Le modèle complet est dans **`.env.lws.example`** à la racine du projet.

### Valeurs obligatoires

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

DB_HOST=127.0.0.1
DB_DATABASE=<base_lws>
DB_USERNAME=<user_lws>
DB_PASSWORD=<mdp_lws>

SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
LOG_LEVEL=error
```

> Sur LWS, testez `DB_HOST=127.0.0.1` puis `localhost` si la connexion échoue.

---

## 3. Première installation (SSH)

```bash
cd ~/public_html

# Clone (nouvelle install)
git clone https://github.com/Thevie03/gestion_caisse_wmc.git gestioncaisse
cd gestioncaisse
git checkout upgrade/laravel-11

# Configuration
cp .env.lws.example .env
# Éditez .env avec vos identifiants MySQL LWS
php artisan key:generate

# Installation
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan optimize
bash fix_permissions_simple.sh
```

### Base MySQL

1. cPanel → **Bases de données MySQL** → créer base + utilisateur
2. Associer l'utilisateur à la base (tous privilèges)
3. Reporter les identifiants dans `.env`
4. Lancer `php artisan migrate --force`

---

## 4. Mise à jour (routine)

### Étape A — Sur votre PC (Windows)

```powershell
cd "C:\Users\WMC\Desktop\APP GCAISSE WMC 2026\APP GCAISSE WMC 2026"
.\deploy-lws.ps1 -Message "Correction POS"
```

Ou manuellement :

```powershell
git add .
git commit -m "Description"
git push origin upgrade/laravel-11
```

### Étape B — Sur le serveur LWS (SSH)

```bash
cd ~/public_html/gestioncaisse
bash deploy-lws.sh
```

Le script `deploy-lws.sh` exécute automatiquement :

1. `git pull`
2. `composer install --no-dev`
3. `php artisan migrate --force`
4. `php artisan optimize`
5. `fix_permissions_simple.sh`

---

## 5. Permissions

| Élément | Permission |
|---------|------------|
| Dossiers généraux | `755` |
| Fichiers | `644` |
| `storage/` | `775` |
| `bootstrap/cache/` | `775` |
| `artisan` | `755` |

```bash
bash fix_permissions_simple.sh
```

---

## 6. Authentification GitHub (serveur)

GitHub n'accepte plus le mot de passe du compte pour `git pull`.

1. GitHub → **Settings** → **Developer settings** → **Personal access tokens**
2. Créer un token avec accès **`repo`**
3. Sur le serveur :

```bash
git config --global credential.helper store
git pull origin upgrade/laravel-11
# Utilisateur : Thevie03
# Mot de passe : <votre_token>
```

---

## 7. Checklist avant mise en ligne

- [ ] PHP **8.2+** activé
- [ ] Extensions PHP activées (dont **gd**)
- [ ] `.env` configuré (`APP_DEBUG=false`)
- [ ] `APP_KEY` générée (`php artisan key:generate`)
- [ ] `APP_URL` = domaine sans slash final (ajouter `/gestioncaisse` seulement si accès via sous-dossier)
- [ ] Base MySQL créée et testée
- [ ] `composer install --no-dev` exécuté
- [ ] `php artisan migrate --force` exécuté
- [ ] `storage/` et `bootstrap/cache/` en écriture
- [ ] HTTPS activé (Let's Encrypt)
- [ ] `.env` **jamais** sur GitHub

---

## 8. Dépannage

| Problème | Solution |
|----------|----------|
| **Erreur 500** | Consulter `storage/logs/laravel.log` |
| **403 Forbidden** | Vérifier document root → `gestioncaisse/public` ou `.htaccess` racine |
| **No application encryption key** | `php artisan key:generate` |
| **Connexion MySQL** | Vérifier `DB_HOST`, identifiants cPanel |
| **Colonne SQL manquante** | `php artisan migrate --force` |
| **Class not found** | `composer install --no-dev --optimize-autoloader` |
| **PWA offline** | HTTPS obligatoire + `service-worker.js` accessible |
| **404 sur /gestioncaisse** | Voir `CONFIGURATION_RACINE.md` et `GUIDE_RESOLUTION_404.md` |

### Debug temporaire

```env
APP_DEBUG=true
```

Remettez `false` après diagnostic.

---

## 9. Fichiers utiles du projet

| Fichier | Rôle |
|---------|------|
| `.env.lws.example` | Modèle `.env` production |
| `deploy-lws.sh` | Script mise à jour serveur |
| `deploy-lws.ps1` | Script push depuis Windows |
| `fix_permissions_simple.sh` | Correction permissions |
| `.htaccess` (racine) | Redirection vers `public/` |
| `public/.htaccess` | Règles Laravel + sécurité + PWA |
| `CONFIGURATION_RACINE.md` | Config sous-dossier `/gestioncaisse` |
| `DEPLOIEMENT-LWS.md` | Workflow GitHub ↔ LWS détaillé |

---

## 10. Notes Laravel 11

- Pas de build Vite requis (Bootstrap CDN).
- Branche `upgrade/laravel-11` : après validation, fusionner dans `main` et utiliser `main` dans `deploy-lws.sh`.
- Les données (`storage/`, `.env`, MySQL) restent sur le serveur ; Git ne les écrase pas.

---

*Dernière mise à jour : Laravel 11.54 — juillet 2026*
