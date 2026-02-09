# LifeSync — Projet ECF Développeur Web & Web Mobile

LifeSync est une application web de gestion de tâches partagées, développée dans le cadre de l’ECF.
Elle permet à plusieurs utilisateurs de créer, suivre et réaliser des tâches afin d’améliorer
l’organisation quotidienne et de rendre visible la répartition des responsabilités.

---

##  Fonctionnalités principales

- Authentification via API (`/api/login`)
- Gestion complète des tâches (CRUD)
- Statuts :
  - TODO
  - IN_PROGRESS
  - DONE
- Historique automatique :
  - commencé par (`startedAt`, `startedBy`)
  - fait par (`doneAt`, `doneBy`)
- Partage des tâches :
  - tous les utilisateurs voient toutes les tâches
  - tous peuvent changer le statut d’une tâche
- Permissions :
  - un utilisateur peut supprimer uniquement ses propres tâches
  - un administrateur peut supprimer toutes les tâches

---

##  Technologies utilisées

### Backend
- Symfony 7.4 (framework PHP moderne)
- Doctrine ORM (base de données et entités)
- MySQL (stockage des données)
- API REST JSON (communication front/back)

### Frontend
- HTML / CSS moderne (interface simple)
- JavaScript Vanilla (Fetch API)
- Interface accessible via `/public/app`

### Outils
- Postman (tests API)
- GitHub (versioning + gestion de projet)

---

##  Installation du projet

### 1. Cloner le dépôt


git clone https://github.com/Revi-Drag/lifesync-ecf.git
cd lifesync-ecf
2. Installer les dépendances
composer install
3. Configurer la base de données
Créer un fichier .env.local :

DATABASE_URL="mysql://root:password@127.0.0.1:3306/lifesync"
Créer la base :

php bin/console doctrine:database:create
4. Exécuter les migrations
php bin/console doctrine:migrations:migrate
5. Charger les fixtures (utilisateurs + données)
php bin/console doctrine:fixtures:load

- Comptes de test
Administrateur
Email : admin@lifesync.local

Mot de passe : Admin123!

Rôle : ROLE_ADMIN

Utilisateur standard
Email : user@lifesync.local

Mot de passe : User123!

Rôle : ROLE_USER

- Lancer le serveur Symfony
symfony serve
Application disponible sur :

API : http://127.0.0.1:8000/api

Front : http://127.0.0.1:8000/app/login.html

- Routes API principales
Méthode	Route	Description
POST	/api/login	Connexion
GET	/api/me	Infos utilisateur connecté
GET	/api/me/stats	Statistiques utilisateur
GET	/api/tasks	Liste des tâches
POST	/api/tasks	Créer une tâche
PATCH	/api/tasks/{id}	Modifier statut ou contenu
DELETE	/api/tasks/{id}	Supprimer une tâche

- Sécurité
Authentification via session Symfony (PHPSESSID)

Protection des routes API par firewall

Validation backend des champs (titre, durée, difficulté)

Contrôle des permissions :

suppression limitée au créateur

admin autorisé sur toutes les tâches

- Gestion de projet
Dépôt GitHub :
https://github.com/Revi-Drag/lifesync-ecf

Tableau de gestion de projet :
https://github.com/users/Revi-Drag/projects/1/views/1

- Auteur
Projet réalisé par Guillaume VALSEMEY
ECF Développeur Web & Web Mobile

