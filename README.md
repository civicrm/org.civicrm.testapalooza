# CiviCRM Testapalooza: codeception-2.x

## Requirements

 * Install a local copy of CiviCRM.
   * (*Strongly suggested*: Use [buildkit](https://github.com/civicrm/civicrm-buildkit/)'s `civibuild` with default file hierarchy)
 * Install [`cv`](https://github.com/civicrm/cv) somewhere in the `PATH`.
   * (*Note*: This is bundled with the latest buildkit.)
 * Install [`codecept`](http://codeception.com/install) somewhere in the `PATH`.

## Create a new suite for your extension

To test a CiviCRM extension with Codeception 2.x:

 * In your extension folder, [initialize the skeletal tests normally](http://codeception.com/quickstart).
 * In `tests/acceptance.suite.yml`, ensure that the `url` points to the default
   value, "`http://localhost/myapp`". This placeholder value will be automatically adapted
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

## Run this test

```bash
cd sites/all/modules/civicrm/tools/extensions
git clone https://github.com/civicrm/org.civicrm.testapalooza -b codeception-2.x
cd org.civicrm.testapalooza
cv api extension.refresh
cv api extension.install key=org.civicrm.testapalooza
codecept run
```

## See also

 * *Codeception*: http://codeception.com/
 * *CiviCRM Testapalooza*: https://github.com/civicrm/org.civicrm.testapalooza/tree/master
