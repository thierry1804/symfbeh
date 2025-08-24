# Test du Système d'Administration

## Prérequis
- Un utilisateur avec le rôle `ROLE_ADMIN`
- Base de données configurée et à jour

## Tests à effectuer

### 1. Test de la commande d'ajout de rôle admin
```bash
# Vérifier qu'un utilisateur existe
php bin/console doctrine:query:sql "SELECT email, roles FROM user LIMIT 1"

# Ajouter le rôle admin (si pas déjà présent)
php bin/console app:add-admin-role user@example.com
```

### 2. Test des routes d'administration
```bash
# Vérifier que les routes sont enregistrées
php bin/console debug:router | findstr admin
```

### 3. Test de l'accès aux pages d'administration
1. Connectez-vous avec un compte admin
2. Accédez à `/admin` - devrait afficher le tableau de bord
3. Accédez à `/admin/users` - devrait afficher la liste des utilisateurs
4. Cliquez sur un utilisateur pour voir ses détails
5. Testez la modification d'un utilisateur

### 4. Test des fonctionnalités
- [ ] Recherche d'utilisateurs
- [ ] Pagination
- [ ] Modification des informations utilisateur
- [ ] Changement de mot de passe
- [ ] Gestion des rôles
- [ ] Activation/désactivation d'utilisateur
- [ ] Vérification/dévérification d'email
- [ ] Suppression d'utilisateur (sauf son propre compte)

### 5. Test de sécurité
- [ ] Accès refusé pour les utilisateurs non-admin
- [ ] Protection CSRF sur les formulaires
- [ ] Confirmation pour les actions destructives
- [ ] Impossible de supprimer son propre compte

## Résultats attendus

### Interface utilisateur
- Navigation d'administration visible pour les admins
- Tableau de bord avec statistiques
- Liste des utilisateurs avec pagination
- Formulaires de modification fonctionnels
- Messages de confirmation/erreur appropriés

### Fonctionnalités
- Toutes les actions CRUD sur les utilisateurs
- Gestion des rôles et permissions
- Recherche et filtrage
- Actions rapides (toggle status, verification)

### Sécurité
- Contrôles d'accès appropriés
- Validation des données
- Protection contre les actions non autorisées

## Dépannage

### Problèmes courants

#### Erreur 403 - Accès refusé
- Vérifiez que l'utilisateur a le rôle `ROLE_ADMIN`
- Utilisez la commande pour ajouter le rôle

#### Erreur 404 - Page non trouvée
- Vérifiez que les routes sont bien enregistrées
- Videz le cache : `php bin/console cache:clear`

#### Problèmes de formulaire
- Vérifiez que le formulaire `UserEditFormType` est bien configuré
- Vérifiez les validations

#### Problèmes de base de données
- Vérifiez que les tables existent : `php bin/console doctrine:schema:validate`
- Mettez à jour le schéma si nécessaire : `php bin/console doctrine:schema:update --force`

## Commandes utiles pour les tests

```bash
# Vérifier l'état de la base de données
php bin/console doctrine:schema:validate

# Lister tous les utilisateurs
php bin/console doctrine:query:sql "SELECT id, email, roles, is_active FROM user"

# Vérifier les routes
php bin/console debug:router | findstr admin

# Vider le cache
php bin/console cache:clear

# Tester la commande d'ajout de rôle
php bin/console app:add-admin-role user@example.com
```
