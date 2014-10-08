#!/bin/bash

# Test whether command-line arguments are present (non-empty).
if [ -n "$1" ]
then
  REVISION=$1
else  
  echo "Command line is ticket number"
  exit $E_NOTROOT
fi 

# switch to the ticket
svn revert --depth=infinity .
svn switch --force https://subversion.assembla.com/svn/onetouchemr/tickets/ticket.$1 .

# merge stable to the ticket
svn merge https://subversion.assembla.com/svn/onetouchemr/trunk/stable .


