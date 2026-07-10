# Déploiement et mises à jour — LWS + GitHub (Gestion Caisse WMC)

Ce guide explique comment **relier ton site déjà en ligne (LWS)** à **GitHub** et comment **mettre à jour** le code en production sans perdre la base MySQL, le fichier **`.env`** ni le dossier **`storage/`**.

---

## En deux mots : comment c’est « lié »

Il n’y a **pas de bouton dans LWS** qui connecte GitHub automatiquement. Le lien, c’est **Git** :

| Où | Ce que tu fais |
|----|----------------|
| **Ton PC** | Tu enregistres les changements et tu les **envoies** sur GitHub : `git push`. |
| **GitHub** | Garde une **copie du code** (référence pour tout le monde). |
| **Serveur LWS** | Tu **télécharges** cette copie dans le dossier du site : `git pull`. |

Donc pour chaque mise à jour : **PC → `push` → GitHub → sur le serveur → `pull`**.

Les données (MySQL, `.env`, fichiers dans `storage`) **restent sur le serveur** ; Git ne les remplace pas si tu ne les commits pas.

---

## À adapter chez toi (note ces valeurs)

| Élément | Valeur actuelle du projet (à modifier si besoin) |
|--------|---------------------------------------------------|
| **Dépôt GitHub** | `https://github.com/Thevie03/gestion_caisse_wmc.git` |
| **Branche** | `main` (si ton dépôt utilise `master`, remplace `main` par `master` partout dans ce doc) |
| **Dossier Laravel sur le serveur** | Exemple : `~/public_html/crm` — **remplace par le vrai chemin** de ton app (ex. `~/public_html/gestion-caisse`). |
| **Racine web (cPanel / sous-domaine)** | Doit pointer vers le dossier **`public`** de Laravel, ex. `public_html/crm/public` — **obligatoire**, pas le dossier parent seul. |

---

## Rappels importants

| Élément | Rôle |
|--------|------|
| **Git / GitHub** | Met à jour le **code** (PHP, vues, migrations, etc.). |
| **Base MySQL** | Reste sur le serveur ; les migrations **ajoutent** tables ou colonnes, elles ne vident pas la base. |
| **`.env`** | Uniquement sur le serveur (et en local sur ton PC). **Ne pas** le mettre sur GitHub. |
| **`storage/`** | Uploads, logs, cache : à **garder** sur le serveur ; à recopier depuis l’ancienne install si tu recrées le dossier. |
| **`public/build/`** | Généré par **Vite**, souvent **hors Git** (`.gitignore`) → à regénérer ou uploader après changement du front. |

---

## Authentification GitHub sur le serveur (HTTPS)

Pour `git clone` ou `git pull`, GitHub **n’accepte plus** le mot de passe du compte.

- **Nom d’utilisateur :** ton identifiant GitHub (ex. `Thevie03`).
- **Mot de passe demandé par Git :** un **Personal Access Token** (PAT), créé sur GitHub → *Settings* → *Developer settings* → *Personal access tokens*.

Pour un dépôt **privé** : token **classic** avec la case **`repo`**, ou token **fine-grained** avec accès au dépôt **`gestion_caisse_wmc`** et permission **Contents : Read** (minimum pour `pull`).

### Mémoriser le token (optionnel, sur le serveur)

```bash
git config --global credential.helper store
```

Au premier `git pull`, saisis identifiant + token ; les prochains `pull` pourront ne plus redemander.

---

## Cas A — Tu avais déjà le site en ligne **sans** Git (FTP, zip, etc.)

Objectif : le dossier sur LWS devient une copie pilotée par GitHub.

1. **Sauvegarde** `.env` et tout le dossier `storage` (copie sur ton PC ou autre dossier sur le serveur).
2. Sur le serveur (SSH), va dans `public_html` (ou équivalent).
3. **Soit** tu renommes l’ancien dossier (ex. `crm` → `crm_ancien`), **soit** tu clones dans un **nouveau** dossier (ex. `crm`).
4. Clone le dépôt :

   ```bash
   git clone https://github.com/Thevie03/gestion_caisse_wmc.git crm
   cd crm
   ```

5. Recolle **`.env`** et **`storage/`** (et `storage/app/public` si besoin) depuis la sauvegarde.
6. Puis enchaîne comme en **« Première installation après clone »** ci-dessous.

---

## Cas B — Le dossier sur le serveur est **déjà** un dépôt Git

Vérifie que le dépôt distant est **le tien** :

```bash
cd ~/public_html/crm
git remote -v
```

Tu dois voir `https://github.com/Thevie03/gestion_caisse_wmc.git` pour `origin`.  
Si l’URL est fausse (ex. autre compte GitHub) :

```bash
git remote set-url origin https://github.com/Thevie03/gestion_caisse_wmc.git
git remote -v
```

---

## Première installation après clone (une fois)

À la racine Laravel (là où se trouve `artisan`) :

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Dans **cPanel** : sous-domaine / domaine → **document root** = **`…/ton_dossier/public`** (pas seulement `ton_dossier`).

**Permissions** (si tu as le script dans le projet) :

```bash
bash fix_permissions_simple.sh
```

**Front (Vite)** : en local (PowerShell, à la racine du projet) :

```powershell
npm ci
npm run build
```

Puis envoie le dossier **`public/build`** sur le serveur dans **`crm/public/build/`** (adapte `crm` à ton chemin), **sauf** si tu lances `npm run build` directement sur le serveur (Node requis).

---

## Mises à jour courantes (à chaque changement de code)

### Étape 1 — Sur ton PC (Windows / PowerShell)

```powershell
cd "C:\Users\WMC\Desktop\APP GCAISSE WMC 2026\APP GCAISSE WMC 2026"
git add .
git commit -m "Description courte de la mise à jour"
git push origin main
```

*(Adapte le chemin et remplace `main` si ta branche a un autre nom.)*

### Étape 2 — Front modifié ? (JS, CSS, `@vite`, Blade avec assets)

En local :

```powershell
npm ci
npm run build
```

Puis **transfère** tout le dossier **`public\build`** vers le serveur : **`ton_dossier/public/build/`**.

### Étape 3 — Sur le serveur (SSH)

```bash
cd ~/public_html/crm
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

### Étape 4 — Si le site affiche des erreurs de droits

```bash
bash fix_permissions_simple.sh
```

---

## Bloc copier-coller : mise à jour serveur complète

```bash
cd ~/public_html/crm
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
bash fix_permissions_simple.sh
```

*(Change `~/public_html/crm` si ton installation est ailleurs.)*

---

## Dépannage rapide

### Erreur **403** sur `git push` (PC)

Souvent : le `remote` pointe vers le **mauvais** dépôt (autre organisation / autre utilisateur). Vérifie avec `git remote -v` et corrige avec `git remote set-url origin https://github.com/Thevie03/gestion_caisse_wmc.git`.

### **403 Forbidden** dans le navigateur

- Le domaine doit pointer vers **`…/public`**, pas le dossier parent seul.
- Présence de **`public/index.php`** et **`.htaccess`**.
- Droits fichiers (script `fix_permissions_simple.sh`).

### **Vite manifest not found**

Build manquant : `npm run build` puis déployer **`public/build`**.

### Colonne SQL inconnue après mise à jour

```bash
php artisan migrate --force
```

---

## Sécurité

- Ne **jamais** partager un token GitHub ; en cas de fuite : le **révoquer** sur GitHub et en créer un autre.
- Ne **jamais** committer **`.env`** sur GitHub.

---

*Adapte les chemins (`crm`, `main` / `master`) à ton hébergement réel.*
