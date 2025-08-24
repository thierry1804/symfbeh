# Guide d'Administration - Système de Gestion des Utilisateurs

## Vue d'ensemble

Ce système de gestion des utilisateurs permet aux administrateurs de gérer tous les utilisateurs de l'application, leurs rôles et leurs privilèges.

## Fonctionnalités principales

### 1. Tableau de bord d'administration
- **Accès** : `/admin` (nécessite le rôle `ROLE_ADMIN`)
- **Fonctionnalités** :
  - Statistiques des utilisateurs (total, actifs, vérifiés)
  - Liste des utilisateurs récents
  - Actions rapides

### 2. Gestion des utilisateurs
- **Accès** : `/admin/users`
- **Fonctionnalités** :
  - Liste paginée de tous les utilisateurs
  - Recherche en temps réel
  - Actions rapides (voir, modifier, supprimer)
  - Filtrage par statut
  - Création de nouveaux utilisateurs

### 3. Création d'un utilisateur
- **Accès** : `/admin/users/new`
- **Fonctionnalités** :
  - Formulaire de création complet
  - Validation des données
  - Attribution des rôles
  - Configuration du statut initial

### 4. Détails d'un utilisateur
- **Accès** : `/admin/users/{id}`
- **Fonctionnalités** :
  - Informations complètes de l'utilisateur
  - Actions rapides (activer/désactiver, vérifier)
  - Statistiques de l'utilisateur

### 5. Modification d'un utilisateur
- **Accès** : `/admin/users/{id}/edit`
- **Fonctionnalités** :
  - Modification des informations personnelles
  - Changement de mot de passe
  - Gestion des rôles
  - Modification du statut

## Rôles disponibles

### ROLE_USER
- Rôle de base pour tous les utilisateurs
- Accès aux fonctionnalités publiques

### ROLE_MODERATOR
- Rôle intermédiaire
- Permissions étendues (à définir selon vos besoins)

### ROLE_ADMIN
- Rôle administrateur complet
- Accès à toutes les fonctionnalités d'administration
- Gestion des utilisateurs et des rôles

## Configuration initiale

### 1. Créer un administrateur

Pour promouvoir un utilisateur existant en administrateur, utilisez la commande :

```bash
php bin/console app:add-admin-role user@example.com
```

### 2. Vérifier les permissions

Assurez-vous que votre utilisateur a bien le rôle `ROLE_ADMIN` :

```bash
php bin/console doctrine:query:sql "SELECT email, roles FROM user WHERE email = 'user@example.com'"
```

## Utilisation

### Navigation
1. Connectez-vous avec un compte administrateur
2. Dans la barre latérale, vous verrez une section "Administration"
3. Cliquez sur "Tableau de bord" ou "Gestion utilisateurs"

### Gestion des utilisateurs

#### Lister les utilisateurs
- Accédez à `/admin/users`
- Utilisez la barre de recherche pour filtrer
- Naviguez avec la pagination

#### Créer un nouvel utilisateur
- Cliquez sur le bouton "Créer un utilisateur" dans la liste
- Ou accédez directement à `/admin/users/new`
- Remplissez le formulaire avec les informations requises
- Sélectionnez les rôles et le statut initial
- Cliquez sur "Créer l'utilisateur"

#### Voir les détails d'un utilisateur
- Cliquez sur l'icône "œil" dans la liste
- Ou accédez directement à `/admin/users/{id}`

#### Modifier un utilisateur
- Cliquez sur l'icône "crayon" dans la liste
- Ou accédez à `/admin/users/{id}/edit`
- Modifiez les champs souhaités
- Cliquez sur "Enregistrer les modifications"

#### Actions rapides
- **Activer/Désactiver** : Change le statut de l'utilisateur
- **Vérifier/Non vérifié** : Change le statut de vérification email
- **Supprimer** : Supprime définitivement l'utilisateur (sauf votre propre compte)

### Gestion des rôles

#### Ajouter un rôle
1. Accédez à la page de modification de l'utilisateur
2. Dans la section "Rôles", cochez les rôles souhaités
3. Enregistrez les modifications

#### Rôles disponibles dans l'interface
- **Utilisateur** : `ROLE_USER` (automatiquement attribué)
- **Administrateur** : `ROLE_ADMIN`
- **Modérateur** : `ROLE_MODERATOR`

## Sécurité

### Contrôles d'accès
- Toutes les routes d'administration sont protégées par `ROLE_ADMIN`
- Un utilisateur ne peut pas supprimer son propre compte
- Confirmation requise pour les actions destructives

### Validation
- Validation des formulaires côté client et serveur
- Vérification des permissions avant chaque action
- Protection CSRF sur tous les formulaires

## Personnalisation

### Ajouter de nouveaux rôles
1. Modifiez le formulaire `UserEditFormType.php`
2. Ajoutez le nouveau rôle dans les choix
3. Mettez à jour les templates si nécessaire

### Modifier les permissions
1. Modifiez les annotations `#[IsGranted()]` dans les contrôleurs
2. Ajustez les conditions dans les templates avec `is_granted()`

### Personnaliser l'interface
- Modifiez les templates dans `templates/admin/`
- Ajustez les styles CSS dans `templates/base.html.twig`

## Dépannage

### Problèmes courants

#### "Accès refusé" sur les pages d'administration
- Vérifiez que votre utilisateur a le rôle `ROLE_ADMIN`
- Utilisez la commande pour ajouter le rôle si nécessaire

#### Erreur 404 sur les routes d'administration
- Vérifiez que le contrôleur `AdminController` est bien créé
- Vérifiez que les routes sont bien définies

#### Problèmes de formulaire
- Vérifiez que le formulaire `UserEditFormType` est bien configuré
- Vérifiez les validations côté serveur

### Commandes utiles

```bash
# Lister tous les utilisateurs
php bin/console doctrine:query:sql "SELECT id, email, roles, is_active FROM user"

# Vérifier les rôles d'un utilisateur
php bin/console doctrine:query:sql "SELECT email, roles FROM user WHERE email = 'user@example.com'"

# Ajouter le rôle admin
php bin/console app:add-admin-role user@example.com

# Vider le cache
php bin/console cache:clear
```

## Support

Pour toute question ou problème, consultez :
- La documentation Symfony
- Les logs d'erreur dans `var/log/`
- Les annotations dans le code source
