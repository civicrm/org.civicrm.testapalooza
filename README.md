# CiviCRM Testapalooza: codeception-2.x (experimental)

## Requirements

 * See [the master README.md](https://github.com/civicrm/org.civicrm.testapalooza/blob/master/README.md) for general requirements and setup.
 * If you are using a custom CiviCRM installation, be sure to run `cv vars:fill` as discussed in the master [README.md](https://github.com/civicrm/org.civicrm.testapalooza/blob/master/README.md).
 * Additionally, install [`codecept`](http://codeception.com/install) somewhere in the `PATH`.

## Run the example

```bash
cd sites/all/modules/civicrm/tools/extensions
git clone https://github.com/civicrm/org.civicrm.testapalooza -b codeception-2.x
cd org.civicrm.testapalooza
cv api extension.refresh
cv api extension.install key=org.civicrm.testapalooza
codecept run
```

## Create a new suite for your extension

To test a CiviCRM extension with Codeception 2.x:

 * In your extension folder, [initialize the skeletal tests normally](http://codeception.com/quickstart) with `codecept bootstrap`.
 * In `tests/acceptance.suite.yml`, ensure that the `url` points to the default
   value, `http://localhost/myapp`. This placeholder value will be automatically adapted
   to match your CiviCRM installation.
 * Copy the file [`tests/_support/CvBoot.php`](tests/_support/CvBoot.php) to your extension.
 * In `codeception.yml`, enable the `CvBoot` extension

```diff
-- a/codeception.yml
+++ b/codeception.yml
@@ -12,6 +12,7 @@ settings:
 extensions:
     enabled:
         - Codeception\Extension\RunFailed
+        - CvBoot
 modules:
     config:
         Db:
```

Now, you will be able to use Civi's classes and services as part of the test.

## Concerns, limitations, questions

Codeception has three suites (acceptance, functional, unit) which imply
different levels of system functionality.  I *think* we want different default
wiring in each, e.g.

 * Acceptance
   * Bootstrap: maybe use `eval(cv('php:boot --level=full', 'phpcode'))` ?
 * Functional
   * Bootstrap: maybe use `eval(cv('php:boot --level=settings --test', 'phpcode'))` ?
   * Maybe some kind of automatic cleanup/reset (e.g. transaction rollback and/or `Civi::reset()`)
 * Unit
   * Bootstrap: maybe use `eval(cv('php:boot --level=settings --test', 'phpcode'))`
   * Bootstrap: maybe use `eval(cv('php:boot --classloader', 'phpcode'))`
   * Maybe some kind of automatic cleanup/reset (e.g. transaction rollback and/or `Civi::reset()`)

If you try to run all suites in a single PHP process, that should generate an error.

There's also a "DB" plugin.  I'm not sure when it's supposed to be used, but
it should probably get default cxn from `$_CV['TEST_DB_DSN']` or `$_CV['CIVI_DB_DSN']`.

The testers (`$I`?) should probably have logic/helper functions for
 * Login/logout
 * Navigating to Civi routes regardless of CMS (`CRM_Utils_System::url()`)
 * Maybe other common scenarios?

## See also

 * *Codeception*: http://codeception.com/
 * *CiviCRM Testapalooza*: https://github.com/civicrm/org.civicrm.testapalooza/tree/master
