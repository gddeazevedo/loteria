# Steps to run the project

- ## docker compose build app

  - This command is used to build the image specified by the Dockerfile

- ## docker compose up -d

  - This command is used to start up the containers specified in the docker-compose.yml file

- ## docker compose exec app composer install

  - This command will install all required dependencies from composer.json file

- ## docker compose exec app php artisan migrate

  - This command is used to run the initial migrations of the application

- ## docker compose exec app php artisan queue:work

  - This command is used to start the worker deamon
