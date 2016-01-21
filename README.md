# CiviCRM Testapalooza: phpunit

# **FIXME**

*At time of writing, civicrm-packages.git has a copy of PHPUnit, and
it conflicts with any other copy of PHPUnit when running tests.
We should be able to remove this, but it remains TODO*

## Requirements

 * Install a local copy of CiviCRM.
   * (*Strongly suggested*: Use [buildkit](https://github.com/civicrm/civicrm-buildkit/)'s `civibuild` with default file hierarchy)
 * Install [`cv`](https://github.com/civicrm/cv) somewhere in the `PATH`.
   * (*Note*: This is bundled with the latest buildkit.)
 * Install [`phpunit`](https://phpunit.de/) somewhere in the `PATH`.
   * (*Note*: This is bundled with the latest buildkit.)

## Create a new suite for your extension

To test a CiviCRM extension with PHPUnit:

 * Make the folder `tests/phpunit`
 * Copy the file [`phpunit.xml.dist`](phpunit.xml.dist) to your extension
 * Copy the file [`tests/phpunit/bootstrap.php`](tests/phpunit/bootstrap.php) to your extension

Now, you will be able to use Civi's classes and services as part of the test.

Note: When creating tests, please extend `\PHPUnit_Framework_TestCase`
instead of `CiviUnitTestCase`.  Although `CiviUnitTestCase` is pretty
powerful, it's also pretty heavy and opinionated.

## Run this test

```bash
cd sites/all/modules/civicrm/tools/extensions
git clone https://github.com/civicrm/org.civicrm.testapalooza -b phpunit
cd org.civicrm.testapalooza
cv api extension.refresh
cv api extension.install key=org.civicrm.testapalooza
phpunit
```

## See also

 * *PHPUnit*: https://phpunit.de/
 * *CiviCRM Testapalooza*: https://github.com/civicrm/org.civicrm.testapalooza/tree/master
