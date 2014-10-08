#!/bin/bash

# Test whether command-line arguments are present (non-empty).
if [ -n "$1" ]
then
  REVISION=$1
else  
  echo "Command line is revision number"
  exit $E_NOTROOT
fi 

# switch to the ticket
svn merge -c -revnum -$1 .
