[![SymfonyInsight](https://insight.symfony.com/projects/7a73ff23-8423-4951-bbfe-8d7312c5a691/mini.svg)](https://insight.symfony.com/projects/7a73ff23-8423-4951-bbfe-8d7312c5a691) [![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)

# P5 OC DAPS - BLOG
___

The project involved developing a blog in native PHP while adhering to OOP (Object-Oriented Programming), MVC (Model-View-Controller) architecture, and PSR (PHP Standards Recommendations).

#### All UML diagrams of the project are available in the [diagrams](https://github.com/MH-DevApp/OC_Projet_5/tree/feature/documentation/diagrams) folder.
_The folder [v1](https://github.com/MH-DevApp/OC_Projet_5/tree/feature/documentation/diagrams/v1) represents to the design before the start of the project, while the folder [v2](https://github.com/MH-DevApp/OC_Projet_5/tree/feature/documentation/diagrams/v2) represents to the design at the end of the project._

## Specs
___

* PHP 8.1
* Bootstrap 5.2.3
* Bundles installed via Composer :
  * Autoload
  * Twig
  * PHP Mailer
  * PHPCodeSniffer
  * PHPStan

### Success criteria
The website must be responsive & secured. Code quality assessments done via [SymfonyInsight](https://insight.symfony.com/projects/7a73ff23-8423-4951-bbfe-8d7312c5a691).

## Install, build and run
___

First clone or download the source code and extract it.

### Local webserver
___

#### Requirements
- You need to have composer on your computer
- Your server needs PHP version 8.1
- MySQL or MariaDB
- Apache or Nginx

The following PHP extensions need to be installed and enabled :
- pdo_mysql
- mysqli
- intl

#### Install
1. To move to the app folder in the terminal:

    ```bash
    > cd app
    ```

2. To install dependencies with Composer:

    ```bash
    > composer install
    ```

3. To run the script for creating the .env and .env_test files:

    ```bash
    > composer run make:env
    ```

4. To modify the values of keys in the .env files:

    example :
    
    ```dotenv
    APP_ENV=DEV
    
    # DATABASE
    DB_DNS=mysql:host=localhost:3306;dbname=dbname
    DB_USER=root
    DB_PWD=root
    
    SECRET_KEY= # GENERATE SECRET_KEY WITH SCRIPT, YOU CAN CHANGE IT IF YOU WANT
    
    # MAILER
    MAILER_HOST=locahost
    MAILER_PORT=1025
    ```

5. To run the script for creating the database and tables, with the condition of deleting the existing database if it already exists:

    ```bash
    > composer run make:database
    ```

6. To load the fixtures for users, posts, and comments:

    ```bash
    > composer run make:load:fixtures
    ```

7. To launch a PHP development server:

   **Note: Please free up port 3000 or modify it in the following command.**

    ```bash
    > php -S localhost:3000 -t public/
    ```

The website is available at the url: http://localhost:3000

### With Docker
___
#### Requirements
To install this project, you will need to have [Docker](https://www.docker.com/) installed on your Computer. 

#### Install

Once your Docker configuration is up and ready, you can follow the instructions below:

1. To create a volume for the database:

    ```bash
    > docker volume create oc_dev
    ```

2. To navigate to the "app" folder in the terminal, you can use the following command:

    ```bash
    > cd app
    ```

3. To install dependencies with Composer:

    ```bash
    > composer install
    ```

4. To run the script for creating the .env and .env_test files:

    ```bash
    > composer run make:env
    ```

5. To modify the values of keys in the .env file as follows:

    ```dotenv
    APP_ENV=DEV

    # DATABASE
    DB_DNS=mysql:host=db;dbname=oc_p5
    DB_USER=root
    DB_PWD=password

    SECRET_KEY= # GENERATE SECRET_KEY WITH SCRIPT, YOU CAN CHANGE IT IF YOU WANT

    # MAILER
    MAILER_HOST=mailer
    MAILER_PORT=1025
    ```

6. To build a Docker image:

   **Note: Please free up port 3000.**

    ```bash
    > docker-compose -f ../docker-compose.dev.yml up -d --build --remove-orphans
    ```

7. To destroy/remove a Docker image, you can use the following command:

    ```bash
    > docker-compose -f ../docker-compose.dev.yml down -v --remove-orphans
    ```
The generated Docker container uses PHP8.2, MySQL 8.0, phpMyAdmin and mailcatcher.

The website is available at the url: http://localhost:3000

You can access the DBMS (phpMyAdmin) to view and configure your database. Please go to the url: http://localhost:8080.

- Username: `root` ;
- Password: `password`.

This assumes that you have set up a Docker container running phpMyAdmin and configured it to run on port 8080. Make sure that the Docker container is running and accessible before attempting to access phpMyAdmin.

