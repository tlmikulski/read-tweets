#!/bin/bash

docker compose up -d

#project name is defined in docker-compose.yml
docker exec -w /opt/code readtweets-php-1 composer install
docker exec -w /opt/code readtweets-php-1 php bin/console app:get-twitter-posts
