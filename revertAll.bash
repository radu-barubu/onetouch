#!/bin/bash

# revert everything
svn revert --depth=infinity .

# show current info
svn info

