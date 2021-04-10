#!/bin/sh
[ ! "$(docker ps -q -f name=php)" ] && echo "You cannot run this command without the docker containers running." && exit 1;
docker exec -it php php vendor/bin/doctrine-migrations "$@"