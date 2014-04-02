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

NAMESPACE=innoscience/eloquental
PACKAGIST=innoscience/eloquental:dev-master

###################################
# Let it ride from here...
###################################

BASE_DIR=`pwd`
BUILD_DIR=${BASE_DIR}/laravel-travis-build
PACKAGE_DIR=${BUILD_DIR}/vendor/${NAMESPACE}

COMPOSER_PATH=${BUILD_DIR}/composer.json
PHPUNIT_CONFIG_PATH=${BUILD_DIR}/phpunit.xml
PHPUNIT_LOADER_PATH=${BUILD_DIR}/tests/bootstrap.php

# download the laravel app
composer create-project laravel/laravel laravel-travis-build --prefer-dist
cd laravel-travis-build
composer require ${PACKAGIST}

cd vendor/${NAMESPACE}