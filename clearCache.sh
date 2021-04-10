#!/bin/sh
EXEC_PATH="$(cd "$(dirname "$0")" >/dev/null 2>&1; pwd -P)";
rm -rf "$EXEC_PATH/src/cache"