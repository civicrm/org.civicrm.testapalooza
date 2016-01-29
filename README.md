# CiviCRM Testapalooza: phpunit

## Requirements

 * See [the master README.md](https://github.com/civicrm/org.civicrm.testapalooza/blob/master/README.md)
   for general requirements and setup.
 * If you are using a custom CiviCRM installation, be sure to run `cv vars:fill`
   as discussed in [the master README.md](https://github.com/civicrm/org.civicrm.testapalooza/blob/master/README.md).
 * Additionally, install [`phpunit`](https://phpunit.de/) somewhere in the `PATH`.
   * (*Note*: This is bundled with the latest buildkit.)

## Run the examples

```bash
cd sites/all/modules/civicrm/tools/extensions
git clone https://github.com/civicrm/org.civicrm.testapalooza -b phpunit
cd org.civicrm.testapalooza
cv api extension.refresh
cv api extension.install key=org.civicrm.testapalooza
phpunit
```

Some of the tests may fail with an error about `CIVICRM_UF`.  These tests
are designed to run on a separate, headless database -- and they fail if you
try to run them on a live database.  To use the headless database, specify:

```bash
export CIVICRM_UF=UnitTests
phpunit
```

To run a specific test, indicate the file:

```bash
phpunit tests/phpunit/CRM/Testapalooza/LightTest.php
```

## Create a new suite for your extension

To test a new CiviCRM extension with PHPUnit:

 * Make the folder `tests/phpunit`
 * Copy the file [`phpunit.xml.dist`](phpunit.xml.dist) to your extension
 * Copy the file [`tests/phpunit/bootstrap.php`](tests/phpunit/bootstrap.php) to your extension

Now, you will be able to use Civi's classes and services as part of the test. There are two
examples included here:

  * [`CRM_Testapalooza_LightTest`](tests/phpunit/CRM/Testapalooza/LightTest.php) extends `PHPUnit_Framework_TestCase`.
    It's fairly thin and lightweight, but you have to handle most setup/cleanup yourself.
  * [`CRM_Testapalooza_StdTest`](tests/phpunit/CRM/Testapalooza/StdTest.php) extends `CiviUnitTestCase`; it
    is heavier and more opinionated, but it automatically provides a clean, normalized environment, and it includes
    lots of convenience functions (like `callAPISuccess()` and `callAPIFailure()`).

## See also

 * *PHPUnit*: https://phpunit.de/
 * *CiviCRM Testapalooza*: https://github.com/civicrm/org.civicrm.testapalooza/tree/master
