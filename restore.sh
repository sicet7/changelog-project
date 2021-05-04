#!/bin/sh

[ ! "$(docker ps -q -f name=database)" ] && echo "You cannot run this command without the docker containers running." && exit 1;

EXEC_PATH="$(cd "$(dirname "$0")" >/dev/null 2>&1; pwd -P)";

if [ ! -f "$EXEC_PATH/.pw" ]; then
    echo "Failed to find database root password. Please create \".pw\" file in root with the mysql root password."
    exit 1;
fi

if [ ! -f "$EXEC_PATH/.db" ]; then
    echo "Failed to find database name. Please create \".db\" file in root with the mysql database name."
    exit 1;
fi

if [ ! -f "$EXEC_PATH/backups/$1" ]; then
    echo "Failed to find provided SQL file."
    exit 1;
fi

ROOTPW=`cat "$EXEC_PATH/.pw"`;
DBNAME=`cat "$EXEC_PATH/.db"`;

docker exec database sh -c "mysql --user=root --password=$ROOTPW $DBNAME < /backups/$1"