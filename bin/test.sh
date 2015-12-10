#!/usr/bin/env bash

set -e

MAGENTO_VERSION=$ENV_MAGE_VER

ln -s /magento-src/${MAGENTO_VERSION} /var/www/htdocs
ln -s /src /var/www/htdocs/.modman

n98-magerun install:db
modman deploy
vendor/bin/phpunit --group setup --stderr && vendor/bin/phpunit
