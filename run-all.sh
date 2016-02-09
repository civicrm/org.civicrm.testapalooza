#!/bin/bash
## There are many ways to launch tests. Ensure that all of them work.
set -ex
phpunit4 tests/phpunit/Civi/Testapalooza/MyHeadlessTest.php
phpunit4 tests/phpunit/CRM/Testapalooza/MyHeadlessTest.php
phpunit4 tests/phpunit/CRM/Testapalooza/MyCoreStyleTest.php
#phpunit4 tests/phpunit/CRM/Testapalooza/MyEndToEndTest.php
phpunit4 tests/phpunit/CRM/Testapalooza/ --group headless
#phpunit4 tests/phpunit/CRM/Testapalooza/ --group e2e
phpunit4 --group headless
#phpunit4 --group e2e
