#!/usr/bin/env bash

MAGENTO_VERSION=$1

docker-compose run --rm db mysql -hdb -uroot -proot -e "CREATE DATABASE IF NOT EXISTS \`shopymind_test_${MAGENTO_VERSION}\`"
docker-compose run --rm -e ENV_MAGE_VER=${MAGENTO_VERSION} test
