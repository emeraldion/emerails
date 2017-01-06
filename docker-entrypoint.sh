#!/bin/bash

#
#	Project EmeRails - Codename Ocarina
#
#	Copyright (c) 2008, 2017 Claudio Procida
#	http://www.emeraldion.it
#
#

set -euo pipefail

# usage: file_env VAR [DEFAULT]
#    ie: file_env 'XYZ_DB_PASSWORD' 'example'
# (will allow for "$XYZ_DB_PASSWORD_FILE" to fill in the value of
#  "$XYZ_DB_PASSWORD" from a file, especially for Docker's secrets feature)
file_env() {
	local var="$1"
	local fileVar="${var}_FILE"
	local def="${2:-}"
	if [ "${!var:-}" ] && [ "${!fileVar:-}" ]; then
		echo >&2 "error: both $var and $fileVar are set (but are exclusive)"
		exit 1
	fi
	local val="$def"
	if [ "${!var:-}" ]; then
		val="${!var}"
	elif [ "${!fileVar:-}" ]; then
		val="$(< "${!fileVar}")"
	fi
	export "$var"="$val"
	unset "$fileVar"
}

if [[ "$1" == apache2* ]] || [ "$1" == php-fpm ]; then
	file_env 'EMERAILS_DB_HOST' 'mysql'
	# if we're linked to MySQL and thus have credentials already, let's use them
	file_env 'EMERAILS_DB_USER' "${MYSQL_ENV_MYSQL_USER:-root}"
	if [ "$EMERAILS_DB_USER" = 'root' ]; then
		file_env 'EMERAILS_DB_PASSWORD' "${MYSQL_ENV_MYSQL_ROOT_PASSWORD:-}"
	else
		file_env 'EMERAILS_DB_PASSWORD' "${MYSQL_ENV_MYSQL_PASSWORD:-}"
	fi
	file_env 'EMERAILS_DB_NAME' "${MYSQL_ENV_MYSQL_DATABASE:-emerails}"
	if [ -z "$EMERAILS_DB_PASSWORD" ]; then
		echo >&2 'error: missing required EMERAILS_DB_PASSWORD environment variable'
		echo >&2 '  Did you forget to -e EMERAILS_DB_PASSWORD=... ?'
		echo >&2
		echo >&2 '  (Also of interest might be EMERAILS_DB_USER and EMERAILS_DB_NAME.)'
		exit 1
	fi

	if ! [ -e router.php ]; then
		echo >&2 "error: EmeRails not found in $(pwd)"
		exit 1
	fi

	export DB_HOST="$EMERAILS_DB_HOST"
	export DB_USER="$EMERAILS_DB_USER"
	export DB_PASS="$EMERAILS_DB_PASSWORD"
	export DB_NAME="$EMERAILS_DB_NAME"

	file_env 'EMERAILS_DEBUG'
	if [ "$EMERAILS_DEBUG" ]; then
		set_config 'DB_DEBUG' 1 boolean
	fi

	TERM=dumb php -- "$EMERAILS_DB_HOST" "$EMERAILS_DB_USER" "$EMERAILS_DB_PASSWORD" "$EMERAILS_DB_NAME" <<'EOPHP'
<?php
// database might not exist, so let's try creating it (just to be safe)
$stderr = fopen('php://stderr', 'w');
list($host, $socket) = explode(':', $argv[1], 2);
$port = 0;
if (is_numeric($socket)) {
	$port = (int) $socket;
	$socket = null;
}
$maxTries = 10;
do {
	$mysql = new mysqli($host, $argv[2], $argv[3], '', $port, $socket);
	if ($mysql->connect_error) {
		fwrite($stderr, "\n" . 'MySQL Connection Error: (' . $mysql->connect_errno . ') ' . $mysql->connect_error . "\n");
		--$maxTries;
		if ($maxTries <= 0) {
			exit(1);
		}
		sleep(3);
	}
} while ($mysql->connect_error);
if (!$mysql->query('CREATE DATABASE IF NOT EXISTS `' . $mysql->real_escape_string($argv[4]) . '`')) {
	fwrite($stderr, "\n" . 'MySQL "CREATE DATABASE" Error: ' . $mysql->error . "\n");
	$mysql->close();
	exit(1);
}
$mysql->close();
EOPHP
fi

exec "$@"