#!/usr/bin/env bash

set -e

MAGENTO_VERSION=$ENV_MAGE_VER


ln -s /magento-src/${MAGENTO_VERSION} /var/www/htdocs
if [ -e /var/www/htdocs/.modman ]; then rm -rf /var/www/htdocs/.modman; fi
mkdir /var/www/htdocs/.modman
ln -s /src /var/www/htdocs/.modman/

if [ ! -e /var/www/htdocs/app/etc/local.xml ]; then sleep 20 && n98-magerun install --root-dir="/var/www/htdocs" --dbHost="db" --noDownload --dbUser="root" --dbPass="${DB_ENV_MYSQL_ROOT_PASSWORD}" --dbName="shopymind" --installSampleData=no --useDefaultConfigParams=yes --installationFolder="/var/www/htdocs" --baseUrl="http://shopymind.test/"; fi

cd htdocs
cp -r .modman/src/vendor/ecomdev/ecomdev_phpunit .modman/
cp .modman/src/phpunit.xml.dist .
.modman/src/vendor/bin/modman deploy-all --force
cp .modman/src/vendor/ecomdev/ecomdev_phpunit/app/etc/local.xml.phpunit app/etc/
cd shell; php ecomdev-phpunit.php -a magento-config --db-name shopymind_test --base-url http://shopymind.test/;cd ..

.modman/src/vendor/bin/phpunit --group setup && .modman/src/vendor/bin/phpunit
