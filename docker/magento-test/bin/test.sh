#!/usr/bin/env bash

set -e

MAGENTO_VERSION=$ENV_MAGE_VER
MAGENTO_PATH=/magento_src/${MAGENTO_VERSION}

if [ ! -e ${MAGENTO_PATH} ]
then
    wget --no-check-certificate https://github.com/bragento/magento-core/archive/${MAGENTO_VERSION}.zip \
        && unzip -q ${MAGENTO_VERSION}.zip && rm -rf ${MAGENTO_VERSION}.zip && mv magento-core-${MAGENTO_VERSION} ${MAGENTO_PATH}
fi

ln -s ${MAGENTO_PATH} /var/www/htdocs
if [ -e /var/www/htdocs/.modman ]; then rm -rf /var/www/htdocs/.modman; fi
mkdir /var/www/htdocs/.modman
ln -s /src /var/www/htdocs/.modman/

if [ ! -e /var/www/htdocs/app/etc/local.xml ]; then sleep 20 && n98-magerun install --root-dir="/var/www/htdocs" --dbHost="db" --noDownload --dbUser="root" --dbPass="${DB_ENV_MYSQL_ROOT_PASSWORD}" --dbName="shopymind_${MAGENTO_VERSION}" --installSampleData=no --useDefaultConfigParams=yes --installationFolder="/var/www/htdocs" --baseUrl="http://shopymind.test/"; fi

cd htdocs
cp -r .modman/src/vendor/ecomdev/ecomdev_phpunit .modman/
cp .modman/src/phpunit.xml.dist .
.modman/src/vendor/bin/modman deploy-all --force
cp .modman/src/vendor/ecomdev/ecomdev_phpunit/app/etc/local.xml.phpunit app/etc/
cd shell; php ecomdev-phpunit.php -a magento-config --same-db 1 --db-name shopymind_test_${MAGENTO_VERSION} --base-url http://shopymind.test/;cd ..

.modman/src/vendor/bin/phpunit --group setup && .modman/src/vendor/bin/phpunit
