# Test de la Création d'Utilisateur - Administration

## 🎯 Nouvelle fonctionnalité ajoutée

La fonctionnalité de création d'utilisateurs a été ajoutée à l'interface d'administration.

## 📍 Accès à la fonctionnalité

### Méthode 1 : Via le tableau de bord
1. Connectez-vous avec un compte admin
2. Accédez à `/admin`
3. Cliquez sur le bouton "Créer un utilisateur" dans la section "Actions rapides"

### Méthode 2 : Via la liste des utilisateurs
1. Accédez à `/admin/users`
2. Cliquez sur le bouton "Créer un utilisateur" en haut à droite

### Méthode 3 : Accès direct
- URL : `/admin/users/new`

## 🧪 Tests à effectuer

### 1. Test de base
- [ ] Accès à la page de création
- [ ] Affichage du formulaire
- [ ] Navigation et boutons fonctionnels

### 2. Test de validation
- [ ] Tentative de soumission avec formulaire vide
- [ ] Validation de l'email (format invalide)
- [ ] Validation du mot de passe (trop court)
- [ ] Validation des champs obligatoires

### 3. Test de création réussie
- [ ] Création d'un utilisateur avec données valides
- [ ] Vérification de la redirection vers les détails
- [ ] Vérification du message de succès
- [ ] Vérification en base de données

### 4. Test des valeurs par défaut
- [ ] Vérification que ROLE_USER est coché par défaut
- [ ] Vérification que "Actif" est sélectionné par défaut
- [ ] Vérification que "Non vérifié" est sélectionné par défaut

### 5. Test des rôles
- [ ] Création avec ROLE_USER uniquement
- [ ] Création avec ROLE_ADMIN
- [ ] Création avec ROLE_MODERATOR
- [ ] Création avec plusieurs rôles

## 📋 Scénarios de test

### Scénario 1 : Utilisateur standard
```
Email : test@example.com
Prénom : Jean
Nom : Dupont
Mot de passe : password123
Rôles : Utilisateur
Statut : Actif
Vérification : Non vérifié
```

### Scénario 2 : Administrateur
```
Email : admin2@example.com
Prénom : Marie
Nom : Martin
Mot de passe : admin123456
Rôles : Utilisateur + Administrateur
Statut : Actif
Vérification : Vérifié
```

### Scénario 3 : Utilisateur inactif
```
Email : inactive@example.com
Prénom : Pierre
Nom : Durand
Mot de passe : test123
Rôles : Utilisateur
Statut : Inactif
Vérification : Non vérifié
```

## 🔍 Vérifications après création

### En base de données
```sql
-- Vérifier que l'utilisateur a été créé
SELECT id, email, first_name, last_name, roles, is_active, is_verified, created_at 
FROM user 
WHERE email = 'test@example.com';

-- Vérifier le hachage du mot de passe
SELECT password FROM user WHERE email = 'test@example.com';
```

### Dans l'interface
- [ ] L'utilisateur apparaît dans la liste `/admin/users`
- [ ] Les détails sont corrects sur `/admin/users/{id}`
- [ ] Le statut et les rôles sont bien affichés

## ⚠️ Cas d'erreur à tester

### Email déjà existant
- [ ] Tentative de création avec un email déjà utilisé
- [ ] Vérification du message d'erreur approprié

### Données invalides
- [ ] Email avec format incorrect
- [ ] Mot de passe trop court (< 6 caractères)
- [ ] Champs vides

### Sécurité
- [ ] Accès sans être connecté (doit rediriger vers login)
- [ ] Accès sans rôle admin (doit refuser l'accès)

## 🎨 Test de l'interface

### Design
- [ ] Formulaire responsive sur mobile
- [ ] Validation en temps réel
- [ ] Messages d'erreur clairs
- [ ] Confirmation avant soumission

### UX
- [ ] Navigation intuitive
- [ ] Boutons d'action clairs
- [ ] Informations d'aide présentes
- [ ] Retour facile vers la liste

## 📊 Commandes de vérification

```bash
# Vérifier les routes
php bin/console debug:router | findstr admin

# Vérifier les utilisateurs créés
php bin/console doctrine:query:sql "SELECT email, roles, is_active FROM user ORDER BY created_at DESC LIMIT 5"

# Vider le cache si nécessaire
php bin/console cache:clear
```

## ✅ Critères de succès

La fonctionnalité est considérée comme fonctionnelle si :

1. ✅ Le formulaire s'affiche correctement
2. ✅ La validation fonctionne pour tous les champs
3. ✅ La création d'utilisateur fonctionne
4. ✅ Les valeurs par défaut sont correctes
5. ✅ La redirection fonctionne après création
6. ✅ L'utilisateur apparaît dans la liste
7. ✅ Les rôles et statuts sont correctement appliqués
8. ✅ La sécurité est respectée (accès admin uniquement)

## 🐛 Problèmes connus

Aucun problème connu pour le moment.

## 📝 Notes

- Le mot de passe est automatiquement haché avant sauvegarde
- La date de création est automatiquement définie
- Le rôle ROLE_USER est automatiquement ajouté même si non coché
- L'utilisateur est redirigé vers la page de détails après création
