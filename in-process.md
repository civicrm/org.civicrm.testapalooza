# Bootstrap for fast, in-process testing

The fastest test-suites will load Civi one time. With most PHP test runners, you can edit the main
config file (`phpunit.xml.dist`, `codeception.yml`, or `behat.yml`) and specify a bootstrap
script.

To perform in-process testing, create or edit your bootstrap script and include this:

```php
eval(`cv php:boot`);
```

To get better error reporting, copy the [generic wrapper](wrapper.php) and use:

```php
eval(cv('php:boot --level=settings', 'phpcode'));
```

Evaluating this `php:boot` command will do a few things:

 * Locate the closest instance of CiviCRM
 * Register the class-loader
 * Read the settings (`civicrm.settings.php`)
 * Put some local configuration data in `$GLOBALS['_CV']` (such as `ADMIN_USER` and `ADMIN_PASS`).

Now, you can write tests that use CiviCRM classes and functions.

Tip: If your tests are likely to make a mess in the database, then you may want to run them on a
headless test database.  You can instruct `cv` to use a headless database by either:

 * Setting environment variable `CIVICRM_UF=UnitTests`
 * Calling `cv` with `-t` (e.g. `cv php:boot --level=settings -t`)
