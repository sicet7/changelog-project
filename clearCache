#!/bin/sh
EXEC_PATH="$(cd "$(dirname "$0")" >/dev/null 2>&1; pwd -P)";
rm -rf "$EXEC_PATH/src/cache"
[ ! "$(docker ps -q -f name=php)" ] && exit 0;
sh "$EXEC_PATH/doctrine.sh" "orm:clear-cache:metadata"
sh "$EXEC_PATH/doctrine.sh" "orm:clear-cache:query"