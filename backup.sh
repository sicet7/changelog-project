EXEC_PATH="$(cd "$(dirname "$0")" >/dev/null 2>&1; pwd -P)";

[ ! "$(docker ps -q -f name=database)" ] && echo "You cannot run this command without the docker containers running." && exit 1;

if [ ! -f "$EXEC_PATH/.pw" ]; then
    echo "Failed to find database root password. Please create \".pw\" file in root with the mysql root password."
    exit 1;
fi

if [ ! -f "$EXEC_PATH/.db" ]; then
    echo "Failed to find database name. Please create \".db\" file in root with the mysql database name."
    exit 1;
fi

ROOTPW=`cat "$EXEC_PATH/.pw"`;
DBNAME=`cat "$EXEC_PATH/.db"`;

docker exec database mysqldump --user=root --password="$ROOTPW" $DBNAME > "$EXEC_PATH/backups/db_dump_$(date +%d-%m-%Y_%H-%M).sql"