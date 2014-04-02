#!/bin/bash
#########################################################################
# Laraval Travis Build Script
#
# (c) 2014 Brandon Fenning, based on Rio Astamal's Laraeval Build Script
#
# License: MIT
#########################################################################

##############################################################
# Put your package composer namespace here, eg: group/package
##############################################################

NAMESPACE="innoscience/eloquental"
PACKAGIST="innoscience/eloquental:dev-master"

### Optional, blank out if you wish to use composer
ZIP_INSTALLER="https://dl.dropboxusercontent.com/u/2026670/laravel-travis.zip"

### Directory created by composer or in zip file
INSTALL_DIR="laravel-travis"

###################################
# Let it ride from here...
###################################

BASE_DIR=`pwd`
BUILD_DIR=${BASE_DIR}/${INSTALL_DIR}
PACKAGE_DIR=${BUILD_DIR}/vendor/${NAMESPACE}

COMPOSER_PATH=${BUILD_DIR}/composer.json
PHPUNIT_CONFIG_PATH=${BUILD_DIR}/phpunit.xml
PHPUNIT_LOADER_PATH=${BUILD_DIR}/tests/bootstrap.php

# Download the laravel app
pwd

if [ -z "$ZIP_INSTALLER" ]; then
wget ${ZIP_INSTALLER}
unzip $(basename ${ZIP_INSTALLER})
else
composer create-project laravel/laravel ${INSTALL_DIR} --prefer-dist
fi

cd ${INSTALL_DIR}
composer require ${PACKAGIST}

cd vendor/${NAMESPACE}
pwd
composer install