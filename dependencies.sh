#!/bin/sh
EXEC_PATH="$(cd "$(dirname "$0")" >/dev/null 2>&1; pwd -P)";

[ ! "$(docker ps -q -f name=php)" ] && echo "You cannot run this command without the docker containers running." && exit 1;

if [ ! -f "$EXEC_PATH/src/composer.phar" ]; then
    wget https://getcomposer.org/download/2.0.13/composer.phar --output-document="$EXEC_PATH/src/composer.phar"
fi

docker exec php php composer.phar install