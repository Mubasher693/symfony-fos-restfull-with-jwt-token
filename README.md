Getting started
===============
This project is develop to build restfull token based api for CRUD for post and comments based system using below-mentioned technologies.

Prerequisites
-------------

This projects requires 
- PHP7.3
- Symfony 5.3
- Sqlite3
- FosRest & LexikJWTAuthentication Bundle.
- Postman or any other API testing tool.

Installation
------------
- Need to install all the dependencies by running **composer install** OR **php composer.phar install** (locally/globally)
- Once installed run the following command so Your keys will land in config/jwt/private.pem and config/jwt/public.pem for [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle/edit/2.x/Resources/doc/index.md) .

  #### Generate the SSL keys:

``` bash
$ php bin/console lexik:jwt:generate-keypair
```
- Once all the dependencies are installed, now time to create database and run migrations, The database will be created inside `your-project-root/var/data.db`.
``` bash
$ php bin/console doctrine:database:create
$ php bin/console doctrine:migrations:migrate
```

- For API Collection use the following link to view complete documentation.
  - [Postman](https://documenter.getpostman.com/view/12789973/UVRDFR2n)
  - [Swagger](https://app.swaggerhub.com/apis/Mubasher693/clipr-api/1.0.0)