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
# Download the extension
cd sites/all/modules/civicrm/tools/extensions
git clone https://github.com/civicrm/org.civicrm.testapalooza -b phpunit
cd org.civicrm.testapalooza
cv api extension.refresh
cv api extension.install key=org.civicrm.testapalooza

# Run all the headless tests
phpunit4 --group headless

# Run all the end-to-end tests
phpunit4 --group e2e

# Run a specific test
phpunit4 tests/phpunit/CRM/Testapalooza/MyHeadlessTest.php
```

Note that Testapalooza includes examples of both headless tests
as well as end-to-end tests. These manage the CiviCRM environment very
differently, e.g.

 * *Headless (in-process)*: Boot CiviCRM once with the dummy CMS (`CIVICRM_UF=UnitTests`). Teardown/refill the test DB as needed.
 * *End-to-end (E2E; multi-process)*: Use the live CiviCRM installation, as currently configured.

If you try to run headless and E2E tests at the same time, one test or
another will crash.

## Create a new suite for your extension

To test a new CiviCRM extension with PHPUnit:

 * Make the folder `tests/phpunit`
 * Copy the file [`phpunit.xml.dist`](phpunit.xml.dist) to your extension
 * Copy the file [`tests/phpunit/bootstrap.php`](tests/phpunit/bootstrap.php) to your extension

Now, you will be able to use Civi's classes and services as part of the test. There are a few
examples included here:

  * [`CRM_Testapalooza_MyHeadlessTest`](tests/phpunit/CRM/Testapalooza/MyHeadlessTest.php) extends `PHPUnit_Framework_TestCase`.
    This example runs in a headless environment, where you can freely destroy and recreate the schema.
    The base `TestCase` doesn't provide much help with setup or teardown, but you can mix-in extra support via [`Civi\Test\HeadlessInterface`](https://github.com/civicrm/civicrm-core/blob/master/Civi/Test/HeadlessInterface.php),
    [`Civi\Test\HookInterface`](https://github.com/civicrm/civicrm-core/blob/master/Civi/Test/HookInterface.php), [`Civi\Test\TransactionalInterface`](https://github.com/civicrm/civicrm-core/blob/master/Civi/Test/TransactionalInterface.php).
  * [`CRM_Testapalooza_MyEndToEndTest`](tests/phpunit/CRM/Testapalooza/MyEndToEndTest.php) extends `PHPUnit_Framework_TestCase`.
    This example runs in a live Civi+CMS environment. You should exercise greater caution to ensure that the database
    remains viable and correct, and you may issue calls over HTTP or to the CMS. This implements [`Civi\Test\EndToEndInterface`](https://github.com/civicrm/civicrm-core/blob/master/Civi/Test/EndToEndInterface.php).

## See also

 * *PHPUnit*: https://phpunit.de/
 * *CiviCRM Testapalooza*: https://github.com/civicrm/org.civicrm.testapalooza/tree/master
