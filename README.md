# LifeSync — Projet ECF Développeur Web & Web Mobile

LifeSync est une application web de gestion de tâches partagées, développée dans le cadre de l’ECF.  
Elle permet à plusieurs utilisateurs authentifiés de créer, suivre et réaliser des tâches afin d’améliorer
l’organisation quotidienne et de rendre visible la répartition des responsabilités.

---

## Fonctionnalités principales

- Authentification via API (`/api/login`)
- Gestion complète des tâches (CRUD)
- Statuts disponibles :
  - TODO
  - IN_PROGRESS
  - DONE
- Historique automatique :
  - commencé par (`startedAt`, `startedBy`)
  - terminé par (`doneAt`, `doneBy`)
- Partage des tâches :
  - tous les utilisateurs voient toutes les tâches
  - tous peuvent changer le statut d’une tâche
- Permissions :
  - un utilisateur peut supprimer uniquement ses propres tâches
  - un administrateur peut supprimer toutes les tâches

---

## Technologies utilisées

### Backend

- Symfony 7 (framework PHP moderne)
- Doctrine ORM (entités + accès base de données)
- MySQL en local → PostgreSQL en production (Render)
- API REST JSON

### Frontend

- HTML / CSS moderne
- JavaScript Vanilla (Fetch API)
- Interface disponible dans `public/app`

### Outils

- Postman (tests API)
- GitHub (versioning)
- GitHub Projects (gestion des étapes)
- Render (déploiement Docker)

---

## Liens du projet (ECF)

- Dépôt GitHub :  
  https://github.com/Revi-Drag/lifesync-ecf

- Tableau de gestion de projet :  
  https://github.com/users/Revi-Drag/projects/1/views/1

- Déploiement Render :  
  https://lifesync-ecf.onrender.com

---

## Compte administrateur (ECF)

Identifiants demandés dans le dossier de rendu :

- Email : **admin@lifesync.local**
- Mot de passe : **Admin-123!**
- Rôle : **ROLE_ADMIN**

---

## Installation du projet (local)

### 1. Cloner le dépôt
```bash
git clone https://github.com/Revi-Drag/lifesync-ecf.git
cd lifesync-ecf

```
### 2. Installer les dépendances
```bash
composer install

```
### 3. Configurer la base de données (local)
Créer un fichier `.env.local` à la racine du projet :
```env 
DATABASE_URL="mysql://root:password@127.0.0.1:3306/lifesync"
```

### 4. Créer la base et exécuter les migrations
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Charger les fixtures (local uniquement)
```bash
php bin/console doctrine:fixtures:load
```
---

## Comptes de test (local)

### Administrateur
Email : **admin@lifesync.local**
Mot de passe : **Admin-123!**
Rôle : **ROLE_ADMIN**

---

## Lancer le serveur Symfony
```bash
symfony serve
```

## Application disponible sur :

- API : http://127.0.0.1:8000/api

- Front : http://127.0.0.1:8000/app/login.html

### Routes API principales

|Méthode	|  Route	       |      Description               | 
|---------|----------------|--------------------------------|
|POST	    |  /api/login	   |  Connexion                     |
|GET 	    |   /api/me	     |  Infos utilisateur connecté    |
|GET	    |  /api/me/stats |    Statistiques utilisateur    |
|GET	    |  /api/tasks	   |    Liste des tâches            | 
|POST	    |/api/tasks	     |  Créer une tâche               |
|PATCH    |	/api/tasks/{id}|	   Modifier statut ou contenu |
|DELETE  	|/api/tasks/{id} |   Supprimer une tâche          |

---

## Sécurité
- Authentification via session Symfony (cookie PHPSESSID)
- Cookies sécurisés :
  - HttpOnly
  - SameSite=Lax

- Protection des routes API via firewall Symfony
- Validation backend des champs :
  - titre obligatoire
  - difficulté entre 1 et 5
  - statut contrôlé

- Contrôle des permissions :
  - suppression limitée au créateur
  - administrateur autorisé sur toutes les tâches

---

## Déploiement en production (Render)
Le projet est déployé via Docker sur Render :

https://lifesync-ecf.onrender.com

Base de données en production :

 - PostgreSQL (Render)
 - configurée via la variable DATABASE_URL

## Création des comptes en production (Seed)
En production, les comptes ne sont pas chargés par fixtures.
Cette route est utilisée uniquement dans le cadre de l'ECF pour initialiser les comptes en production.
Ils sont créés via une route de seed protégée :
```bash
POST /admin/_seed_user
```
Cette route nécessite un header obligatoire :
```bash
X-SEED-TOKEN: <token_secret>
```
Les identifiants sont définis via variables Render :
 - ADMIN_EMAIL
 - ADMIN_PASSWORD
 - USER_EMAIL
 - USER_PASSWORD
 - SEED_TOKEN

### Commande PowerShell utilisée :
```powershell
Invoke-WebRequest -UseBasicParsing `
  -Uri "https://lifesync-ecf.onrender.com/admin/_seed_user" `
  -Method Post `
  -Headers @{ "X-SEED-TOKEN" = "VOTRE_TOKEN" }
```


Projet réalisé par Guillaume VALSEMEY

ECF Développeur Web & Web Mobile