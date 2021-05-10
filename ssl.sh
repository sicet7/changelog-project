#!/bin/sh

SCRIPT_PATH="`dirname \"$0\"`";
SCRIPT_PATH="`( cd \"$SCRIPT_PATH\" && pwd )`";
if [ -z "$SCRIPT_PATH" ]; then
    echo "Failed to acquire path information.";
    exit 1;
fi

ROOT_SSL_KEY="$SCRIPT_PATH/rootSsl.key";
ROOT_SSL_CRT="$SCRIPT_PATH/rootSsl.crt";
ROOT_SSL_SRL="$SCRIPT_PATH/rootSsl.srl";

FRONTEND_SSL_KEY="$SCRIPT_PATH/ssl.key";
FRONTEND_SSL_CRT="$SCRIPT_PATH/ssl.crt";
FRONTEND_SSL_CSR="$SCRIPT_PATH/ssl.csr";
FRONTEND_SSL_PEM="$SCRIPT_PATH/ssl.pem";

if [ -f "$ROOT_SSL_KEY" ]; then
    rm "$ROOT_SSL_KEY"
fi

if [ -f "$ROOT_SSL_CRT" ]; then
    rm "$ROOT_SSL_CRT"
fi

if [ -f "$ROOT_SSL_SRL" ]; then
    rm "$ROOT_SSL_SRL"
fi

openssl genrsa -out "$ROOT_SSL_KEY" 4096 2>/dev/null
openssl req -x509 -new -nodes \
    -key "$ROOT_SSL_KEY" \
    -sha256 \
    -days 1024 \
    -subj "/C=DK/ST=Fyn/L=Odense/O=data/OU=data/CN=root" \
    -out "$ROOT_SSL_CRT"

#Frontend

if [ -f "$FRONTEND_SSL_KEY" ]; then
    rm "$FRONTEND_SSL_KEY"
fi

if [ -f "$FRONTEND_SSL_CRT" ]; then
    rm "$FRONTEND_SSL_CRT"
fi

if [ -f "$FRONTEND_SSL_CSR" ]; then
    rm "$FRONTEND_SSL_CSR"
fi

if [ -f "$FRONTEND_SSL_PEM" ]; then
    rm "$FRONTEND_SSL_PEM"
fi

openssl genrsa -out "$FRONTEND_SSL_KEY" 2048 2>/dev/null

openssl req -new -sha256 -key "$FRONTEND_SSL_KEY" -subj "/C=DK/ST=Fyn/L=Odense/O=data/CN=nginx" -out "$FRONTEND_SSL_CSR"

openssl x509 -req -in "$FRONTEND_SSL_CSR" -CA "$ROOT_SSL_CRT" -CAkey "$ROOT_SSL_KEY" -CAcreateserial -out "$FRONTEND_SSL_CRT" -days 500 -sha256

if [ -f "$FRONTEND_SSL_CSR" ]; then
    rm "$FRONTEND_SSL_CSR"
fi

openssl dhparam -out "$FRONTEND_SSL_PEM" 2048