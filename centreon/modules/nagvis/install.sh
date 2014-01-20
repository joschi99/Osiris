#!/bin/sh

#
# Author : 	Pierre Villard
# License : 	Creative Commons - Attribution-ShareAlike 3.0 Unported 
#               (http://creativecommons.org/licenses/by-sa/3.0/)
#
# >> Module designed to integrate Nagvis tool in Centreon <<
#
# Tested with Centreon 2.4.1 and Nagvis 1.7.8
# 
# This module modifies Nagvis source code and installs a Centreon 
# module in order to integrate Nagvis in Centreon. It assumes you 
# created a Nagvis user that will be used to automatically log in 
# Centreon users in Nagvis. However it will always be possible to
# log out from Nagvis interface to use a different user : it will 
# remain integrated in Centreon interface.
#

# Configuration
PATH_CENTREON_MODULES='/usr/local/centreon/www/modules'
PATH_NAGVIS='/usr/local/nagvis'
PATH_NAGVIS_CORE='/usr/local/nagvis/share/server/core'
USER_APACHE='apache'
GROUP_APACHE='apache'
USER_NAGVIS_READONLY='nagvis'
URL_NAGVIS_FRONTEND='../nagvis/frontend/nagvis-js/index.php'

###################################################################
# Installation, do not modify if you don't know what you're doing #
###################################################################
echo "Starting installation..."

echo "Preparing files..."
TEMPDIR=`mktemp -d`
cp -rf nagvis.patch $TEMPDIR
cp -rf nagvis/ $TEMPDIR
sed -i 's|{URL_NAGVIS_FRONTEND}|'$URL_NAGVIS_FRONTEND'|g' $TEMPDIR/nagvis/nagvis.php
sed -i 's|{USER_READONLY}|'$USER_NAGVIS_READONLY'|g' $TEMPDIR/nagvis.patch
sed -i 's|{NAGVIS_CORE_PATH}|'$PATH_NAGVIS_CORE'|g' $TEMPDIR/nagvis.patch

echo "Installing Centreon module..."
cp -rf $TEMPDIR/nagvis/ $PATH_CENTREON_MODULES
chown -R $USER_APACHE:$GROUP_APACHE $PATH_CENTREON_MODULES"/nagvis"

echo "Patching Nagvis source code..."
pushd $PATH_NAGVIS
patch -p0 < $TEMPDIR/nagvis.patch
popd

rm -rf $TEMPDIR

echo "Done !"
