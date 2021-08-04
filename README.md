[![Codacy Badge](https://app.codacy.com/project/badge/Grade/1422ab9d9d10496bbe82e45c74297b2e)](https://www.codacy.com/gh/Mamoon97150/BileMo/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Mamoon97150/BileMo&amp;utm_campaign=Badge_Grade)

# BileMo
Projet 7 : API Bilemo

## Documentation de l'API

[Lien vers la documentation](http://localhost:8000/api/doc)

(Attention à changer le port localhost si besoin !)

***

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

Rendez vous sur la page de documentation et exécutez la méthode login avec les paramètres suivants: 

Administrateur :
- name : bilemo 
-  password :admin
   
Client
-  name : company1 
- password : password


