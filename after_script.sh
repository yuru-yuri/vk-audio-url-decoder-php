#!/usr/bin/env bash

allow_deploy="false"

php_v=$(php -r "echo phpversion();")

if [[ "${php_v:0:-2}" = "7.2" ]]
then
./cc-test-reporter after-build -t clover --exit-code $TRAVIS_TEST_RESULT || true
else
echo "${php_v:0:-2}"
fi
