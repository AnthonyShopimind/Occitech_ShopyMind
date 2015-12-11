#!/usr/bin/env bash

MAGENTO_VERSION=$1
TEST_ARGS=$2

docker-compose run --rm db mysql -hdb -uroot -proot -e "CREATE DATABASE IF NOT EXISTS \`shopymind_test_${MAGENTO_VERSION}\`"
docker-compose run --rm -e ENV_MAGE_VER=${MAGENTO_VERSION} -e TEST_ARGS="${TEST_ARGS}" test
