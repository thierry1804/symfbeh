# Configuration d'environnement

## Fichiers d'environnement

### ⚠️ IMPORTANT : Sécurité

Les fichiers d'environnement contiennent des informations sensibles et ne doivent **JAMAIS** être commités dans Git.

### Fichiers exclus de Git

Le fichier `.gitignore` exclut automatiquement :
- `/.env` - Fichier d'environnement principal
- `/.env.local` - Variables locales (priorité la plus haute)
- `/.env.local.php` - Cache des variables d'environnement
- `/.env.*.local` - Autres fichiers locaux

## Configuration requise

### 1. Créer le fichier .env.local

Créez un fichier `.env.local` à la racine du projet avec le contenu suivant :

```env
# Configuration de la base de données
DATABASE_URL="mysql://username:password@127.0.0.1:3306/retail_db?serverVersion=8.0.32&charset=utf8mb4"

# Configuration Symfony
APP_ENV=dev
APP_SECRET=votre-secret-ici-changez-le
```

### 2. Variables d'environnement disponibles

| Variable | Description | Exemple |
|----------|-------------|---------|
| `DATABASE_URL` | URL de connexion à la base de données | `mysql://user:pass@localhost:3306/db` |
| `APP_ENV` | Environnement (dev, prod, test) | `dev` |
| `APP_SECRET` | Clé secrète pour les tokens CSRF | `votre-secret-ici` |

### 3. Exemples de configuration

#### MySQL
```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/retail_db?serverVersion=8.0.32&charset=utf8mb4"
```

#### PostgreSQL
```env
DATABASE_URL="postgresql://username:password@127.0.0.1:5432/retail_db?serverVersion=16&charset=utf8"
```

#### SQLite
```env
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
```

## Ordre de priorité des fichiers

Symfony charge les variables d'environnement dans cet ordre (du moins prioritaire au plus prioritaire) :

1. `.env` (fichier par défaut, peut être commité)
2. `.env.local` (variables locales, jamais commité)
3. `.env.{environment}` (variables spécifiques à l'environnement)
4. `.env.{environment}.local` (variables locales spécifiques à l'environnement)

## Sécurité

### Bonnes pratiques

1. **Ne jamais commiter** `.env.local` ou tout fichier contenant des secrets
2. **Changer APP_SECRET** pour chaque environnement
3. **Utiliser des mots de passe forts** pour la base de données
4. **Limiter les permissions** de la base de données

### Variables sensibles

- `APP_SECRET` : Utilisé pour signer les tokens CSRF et les cookies
- `DATABASE_URL` : Contient les identifiants de base de données
- Toute clé API ou token d'authentification

## Dépannage

### Erreur "DATABASE_URL not found"
- Vérifiez que le fichier `.env.local` existe
- Vérifiez la syntaxe de l'URL de base de données

### Erreur CSRF
- Vérifiez que `APP_SECRET` est défini
- Vérifiez que la valeur n'est pas vide

### Erreur de connexion à la base de données
- Vérifiez les identifiants dans `DATABASE_URL`
- Vérifiez que le serveur de base de données est démarré
- Vérifiez que la base de données existe
