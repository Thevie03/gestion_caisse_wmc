# Déploiement et mises à jour — LWS / GitHub (ERP SOGESTRA)

Ce document décrit comment mettre à jour l’application en production sur **LWS** à partir du dépôt **GitHub**, sans perdre les données existantes (base MySQL, `.env`, fichiers dans `storage`).

**Dépôt GitHub :** `https://github.com/Thevie03/Erp-Sogestraci.git`  
**Dossier application sur le serveur :** `~/public_html/crm`  
**Sous-domaine (exemple) :** `erp.sogestraci.com`  
**Racine web du sous-domaine (Laravel) :** `public_html/crm/public` — **obligatoire** (dossier `public`, pas seulement `crm`).

---

## Rappels importants

| Élément | Rôle |
|--------|------|
| **Git / GitHub** | Met à jour le **code** (PHP, vues, migrations, etc.). |
| **Base MySQL** | Reste sur le serveur ; les migrations **ajoutent** tables/colonnes, elles ne remplacent pas toute la base. |
| **`.env`** | Reste sur le serveur (ne pas le commiter sur GitHub). À copier depuis l’ancienne install lors du premier déploiement Git. |
| **`storage/`** | Uploads, logs, cache : à préserver ; copier depuis l’ancienne app si besoin. |
| **`public/build/`** | Généré par **Vite** ; il est dans **`.gitignore`** → **pas** sur GitHub. Il faut le créer en local ou sur le serveur après chaque changement front. |

---

## Authentification GitHub (HTTPS)

GitHub n’accepte plus le mot de passe du compte pour `git clone` / `git pull`.

- **Username :** votre identifiant GitHub (ex. `Thevie03`).
- **Password :** un **Personal Access Token** (PAT), pas le mot de passe du site.

Token **fine-grained** : accès au dépôt `Erp-Sogestraci` + permission **Contents : Read** (minimum pour pull).  
Token **classic** : cocher **`repo`** si le dépôt est privé.

### Mémoriser le token (optionnel)

```bash
git config --global credential.helper store
```

Un premier `git pull` où vous saisissez user + token ; les suivants peuvent ne plus redemander.

---

## Première mise en place (résumé)

1. Cloner dans `~/public_html/crm` depuis GitHub.
2. Copier **`.env`** et **`storage/`** depuis l’ancienne app (`~/public_html/Erp`) vers `crm`.
3. Dans `crm` : `composer install`, `php artisan migrate --force`, `php artisan storage:link`, `php artisan optimize`.
4. cPanel → sous-domaine → **répertoire racine** = **`crm/public`** (pas `crm` seul).
5. Corriger les permissions : `bash fix_permissions_simple.sh` (voir plus bas).
6. **Assets Vite :** `npm run build` en local puis upload du dossier **`public/build`** vers `crm/public/build` (ou `npm run build` sur le serveur si Node est disponible).

---

## Mises à jour courantes (routine)

### Étape 1 — Sur votre PC

```bash
git add .
git commit -m "Description de la mise à jour"
git push origin main
```

Remplacez `main` par le nom de votre branche si différent.

### Étape 2 — Si vous avez modifié le front (JS, CSS, `@vite`)

En local, à la racine du projet :

```bash
npm ci
npm run build
```

Puis **uploader** tout le dossier **`public/build`** sur le serveur dans :

`public_html/crm/public/build/`

*(Tant que `public/build` est ignoré par Git, cette étape reste nécessaire sauf build sur le serveur.)*

### Étape 3 — Sur le serveur (SSH)

```bash
cd ~/public_html/crm
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

### Étape 4 — Permissions (si besoin)

À la racine Laravel (`crm`, là où se trouve `artisan` et `fix_permissions_simple.sh`) :

```bash
bash fix_permissions_simple.sh
```

Ce script applique notamment :

- dossiers **755**, fichiers **644** (hors exclusions du script) ;
- **`storage/`** et **`bootstrap/cache/`** en **775** ;
- **`artisan`** en **755**.

---

## Dépannage rapide

### Erreur 403 Forbidden

- Vérifier que le sous-domaine pointe vers **`…/crm/public`**, pas **`…/crm`**.
- Vérifier la présence de **`crm/public/index.php`** et **`.htaccess`**.
- Vérifier les droits (script ci-dessus).

### Erreur « Vite manifest not found » (`public/build/manifest.json`)

Le build front est absent. Lancer **`npm run build`** en local (ou sur le serveur) et déployer le dossier **`public/build`**.

### Erreur SQL « colonne inconnue » après mise à jour

Lancer les migrations :

```bash
php artisan migrate --force
```

---

## Commandes utiles (copier-coller)

Mise à jour serveur complète :

```bash
cd ~/public_html/crm
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
bash fix_permissions_simple.sh
```

Build front en local (Windows, PowerShell, dans le dossier du projet) :

```powershell
npm ci
npm run build
```

Puis transférer **`public\build`** vers le serveur : **`crm/public/build/`**.

---

## Sécurité

- Ne **jamais** coller un token GitHub dans un chat, un ticket ou une URL publique. En cas de fuite : **révoquer** le token sur GitHub et en créer un nouveau.
- Ne **jamais** committer le fichier **`.env`** sur GitHub.

---

*Document généré pour faciliter les déploiements LWS ; adaptez les chemins (`main` / `master`, nom du dossier) si votre hébergement diffère.*
    