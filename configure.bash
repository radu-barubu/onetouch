#!/bin/bash

# configure.bash
#
# Configure for web service after checkout
#
# must be run in the top-level cakePHP directory,
# e.g. the directory containing app, cake, etc
#
# Command line:
#
# ./configure.bash revision dbPassword regPassword verifyPassword ddlPassword
#

# install_rev.bash branchUrl dbPassword
ROOT_UID=0                          # Only users with $UID 0 have root privileges.

# Run as root
if [ "$UID" -ne "$ROOT_UID" ]
then
  echo "Must be root to run this script."
  exit $E_NOTROOT
fi  

# Test whether command-line arguments are present (non-empty).
if [ -n "$1" ]
then
  REVISION=$1
else  
  echo "Command line is revision dbPassword regPassword verifyPassword ddlPassword"
  exit $E_NOTROOT
fi 
if [ -n "$2" ]
then
  PASSWORD=$2
else  
  echo "Command line is revision dbPassword regPassword verifyPassword ddlPassword"
  exit $E_NOTROOT
fi 
if [ -n "$3" ]
then
  REGISTRATION=$3
else  
  echo "Command line is revision dbPassword regPassword verifyPassword ddlPassword"
  exit $E_NOTROOT
fi 
if [ -n "$4" ]
then
  VERIFY=$4
else  
  echo "Command line is revision dbPassword regPassword verifyPassword ddlPassword"
  exit $E_NOTROOT
fi 
if [ -n "$5" ]
then
  DDL=$5
else  
  echo "Command line is revision dbPassword regPassword verifyPassword ddlPassword"
  exit $E_NOTROOT
fi 

# Configure the database.php file
cd app/config
echo `php configure_database.php ${REVISION} ${PASSWORD} ${REGISTRATION} ${VERIFY} ${DDL}`

# Fix permissions
cd ../..
chown -R dev:dev .
if [ ! -d "app/webroot/CUSTOMER_DATA" ]
then
	mkdir app/webroot/CUSTOMER_DATA
fi
chown -R www-data:www-data \
	app/webroot/ccr \
	app/webroot/CUSTOMER_DATA \
	app/tmp/cache \
	app/tmp/logs \
	app/libs/dompdf_0.6.0-beta3/lib/fonts

# Set perms to allow creation of folders for each client
chmod o+w app/webroot/

