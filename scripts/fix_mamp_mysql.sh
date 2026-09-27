#!/usr/bin/env bash
#                                   _ __
#   ___  ____ ___  ___  _________ _(_) /____
#  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
# /  __/ / / / / /  __/ /  / /_/ / / (__  )
# \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
#
# (c) Claudio Procida 2008-2026
#

# This script creates a symlink to the mysql socket file provided by MAMP to the location that
# the mysqliadapter expects.
# This fixes the ominous error 'mysqli_sql_exception: No such file or directory' occurring in PHPUnit
if [ -h /tmp/mysql.sock ]; then
    echo /tmp/mysql.sock exists already
elif [ -f /tmp/mysql.sock ]; then
    echo /tmp/mysql.sock exists already but it is a regular file
else
    sudo ln -s /Applications/MAMP/tmp/mysql/mysql.sock /tmp/mysql.sock
    echo /tmp/mysql.sock symlink created
fi