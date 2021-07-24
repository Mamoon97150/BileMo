# BileMo
Projet 7 : API Bilemo

## Environnement de développement

### Pré-requis 
-   PHP 8
-   Symfony CLI    
-   Docker
-   Docker-compose

Vous pouvez vérifier les pré-requis de symfony avec la commande suivante (de la CLI de Symfony)

```bash
 symfony check:requirements
```

### Lancer l'environnement de développement

####Avec Docker

Créez un dossier mysql à la racine du projet. Puis entrez les commande suivante :
```
    docker-compose up -d
```

L'environnement de developpement est lancé !!

#### Sans Docker

```
    cd app
    symfony serve
```

### Ajouter les dépendances

Ouvrez un terminal a la racine du projet et exécutez les commandes suivantes :

```
    cd app
    composer install
```

### Installer la base de données
Mettez à jour le fichier .env et lancé la ligne de commande suivante:
```
    composer prepare
```
### Authentification

Dans le terminal exécutez la commande suivante :
```
    php bin/console lexik:jwt:generate-keypair
```

Rendez vous sur la page **/api/doc** et exécutez la méthode login avec les paramètres suivants: 

Administrateur :
- name : bilemo 
-  password :admin
   
Client
-  name : company1 
- password : password


