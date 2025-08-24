# Retail App - Système d'Authentification

## Description

Application de gestion retail moderne avec un système d'authentification complet incluant :
- Inscription et connexion utilisateurs
- Vérification d'email
- Réinitialisation de mot de passe
- Gestion de profil utilisateur
- Interface moderne et responsive

## Fonctionnalités

### 🔐 Authentification
- **Inscription** : Création de compte avec validation d'email
- **Connexion** : Authentification sécurisée avec "Se souvenir de moi"
- **Déconnexion** : Déconnexion sécurisée
- **Vérification d'email** : Confirmation d'email obligatoire
- **Mot de passe oublié** : Réinitialisation via email

### 👤 Gestion de Profil
- **Modification des informations** : Prénom, nom, email
- **Statut du compte** : Actif/Inactif, Vérifié/Non vérifié
- **Historique** : Date de création, dernière connexion
- **Sécurité** : Changement de mot de passe

### 🎨 Interface
- **Design moderne** : Bootstrap 5 + Font Awesome
- **Responsive** : Compatible mobile et desktop
- **Navigation intuitive** : Menu adaptatif selon l'état de connexion
- **Messages flash** : Notifications utilisateur

## Installation

### Prérequis
- PHP 8.2+
- Composer
- Symfony CLI (optionnel)

### Étapes d'installation

1. **Cloner le projet**
```bash
git clone <repository-url>
cd retail
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Configurer la base de données**
```bash
# Créer le fichier .env.local
echo 'DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"' > .env.local
echo 'MAILER_DSN=smtp://localhost:1025' >> .env.local
```

4. **Créer la base de données et les tables**
```bash
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

5. **Lancer le serveur de développement**
```bash
symfony server:start
# ou
php -S localhost:8000 -t public
```

## Configuration Email

### Pour le développement
Utilisez MailHog ou Mailtrap pour capturer les emails :

```bash
# Installer MailHog (optionnel)
# Les emails seront capturés et visibles dans l'interface web
```

### Pour la production
Configurez un vrai service SMTP dans `.env.local` :

```env
MAILER_DSN=smtp://user:pass@smtp.example.com:587
```

## Utilisation

### Routes disponibles

| Route | Description | Accès |
|-------|-------------|-------|
| `/` | Page d'accueil | Public |
| `/login` | Connexion | Public |
| `/register` | Inscription | Public |
| `/profile` | Mon profil | Connecté |
| `/reset-password` | Mot de passe oublié | Public |
| `/logout` | Déconnexion | Connecté |

### Créer un premier utilisateur

1. Allez sur `/register`
2. Remplissez le formulaire d'inscription
3. Vérifiez votre email (ou consultez MailHog)
4. Cliquez sur le lien de vérification
5. Connectez-vous sur `/login`

### Créer un utilisateur administrateur

```bash
# Via la console Symfony
php bin/console doctrine:query:sql "UPDATE user SET roles = '[\"ROLE_ADMIN\"]' WHERE email = 'admin@example.com'"
```

## Structure du Code

### Entités
- `User` : Utilisateur principal avec tous les champs nécessaires
- `ResetPasswordRequest` : Gestion des demandes de réinitialisation

### Contrôleurs
- `HomeController` : Page d'accueil
- `SecurityController` : Gestion de la connexion/déconnexion
- `RegistrationController` : Inscription et vérification email
- `ResetPasswordController` : Réinitialisation de mot de passe
- `ProfileController` : Gestion du profil utilisateur

### Formulaires
- `RegistrationFormType` : Formulaire d'inscription
- `ProfileFormType` : Formulaire de modification de profil
- `ResetPasswordRequestFormType` : Demande de réinitialisation
- `ChangePasswordFormType` : Changement de mot de passe

### Templates
- `base.html.twig` : Template de base avec navigation
- `home/index.html.twig` : Page d'accueil
- `security/login.html.twig` : Page de connexion
- `registration/register.html.twig` : Page d'inscription
- `profile/index.html.twig` : Page de profil
- `reset_password/*.html.twig` : Pages de réinitialisation

## Sécurité

### Fonctionnalités implémentées
- ✅ Hachage sécurisé des mots de passe (bcrypt)
- ✅ Protection CSRF sur tous les formulaires
- ✅ Validation des emails
- ✅ Tokens de réinitialisation sécurisés
- ✅ Sessions sécurisées
- ✅ "Se souvenir de moi" sécurisé

### Bonnes pratiques
- Mots de passe minimum 6 caractères
- Validation d'email obligatoire
- Tokens de réinitialisation avec expiration
- Logs de connexion
- Protection contre les attaques par force brute

## Personnalisation

### Modifier les emails
Les templates d'email se trouvent dans :
- `templates/registration/confirmation_email.html.twig`
- `templates/reset_password/email.html.twig`

### Modifier les rôles
Dans `src/Entity/User.php`, modifiez la méthode `getRoles()` pour ajouter des rôles personnalisés.

### Modifier la validation
Dans les formulaires, ajoutez des contraintes de validation Symfony.

## Dépannage

### Problèmes courants

1. **Erreur de base de données**
```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate
```

2. **Emails non reçus**
- Vérifiez la configuration `MAILER_DSN`
- Consultez les logs : `tail -f var/log/dev.log`

3. **Erreur de cache**
```bash
php bin/console cache:clear
```

4. **Permissions sur var/**
```bash
chmod -R 777 var/
```

## Support

Pour toute question ou problème :
1. Consultez les logs dans `var/log/`
2. Vérifiez la documentation Symfony
3. Créez une issue sur le repository

## Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.
