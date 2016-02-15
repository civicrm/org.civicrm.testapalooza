# Data management

Civi is a heavily customizable system, so many of the configuration details
(like activity-type-names and membership-type-id) are stored in a mutable
database (rather than relatively stable PHP code). This creates a challenge
in writing tests -- we need to balance competing needs to:

 * Define the test data precisely and reproducibly
 * Easily write the definition of the test data
 * Explore a thorough (or at least representative) range of scenarios
 * Execute tests quickly

It's hard to proscribe a single formula for testing all subsystems and
extensions -- the balance of these concerns weighs differently on, say, an
low-level installation tool (like `civicrm.drush.inc`), a high-level data
view (like CiviReports), and a headline feature (like CiviMail or
CiviVolunteer).

But there are a few tools you should generally consider using, discussed below.

For working examples, see the examples linked in [README.md](README.md).

## 1. System declarations with Civi\Test

The `Civi\Test` class includes a variety of helper functions for declaring a
baseline environment.  Here are a few examples:

```php
// Use the stock schema and stock data in the headless DB.
Civi\Test::headless()->apply();

// Use the stock schema and install this extension (i.e. the
// extension which contains __DIR__).
Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();

// Use the stock schema, as well as some special SQL statements
// and extensions.
Civi\Test::headless()
      ->sqlFile(__DIR__ . '/../example.sql')
      ->install(array('org.civicrm.foo', 'org.civicrm.bar'))
      ->apply();

// Use the existing Civi+CMS stack, and also install this
// extension.
Civi\Test::e2e()
      ->installMe(__DIR__)
      ->apply();
      
// Use the existing Civi+CMS stack, and do a lot of 
// crazy stuff
Civi\Test::e2e()->
      ->uninstall('*')
      ->sqlFile(__DIR__ . '/../example.sql')
      ->installMe(__DIR__)
      ->callback(function(){
        civicrm_api3('Widget', 'frobnicate', array());
      }, 'mycallback')
      ->apply();
```

A few things to note:

 * `Civi\Test::headless()` and `Civi\Test::e2e()` are similar -- both
   allow you to declare a sequence of setup steps. They differ in the defaults:
   * `headless()` only runs on a headless DB, and it can be very aggressive about resetting the system. For example, it may casually reset all
      your option-groups, drop all custom-data, and uninstall all extensions.
   * `e2e()` only runs with a live CMS (Drupal/WordPress/etc), and it has a lighter touch. It tends to leave things in-place unless you specifically instruct otherwise.
 * `Civi\Test` is lazy (in a good way). It keeps track of how the environment
   is configured, and it only makes a change when necessary.
   * Ex: If you call `Civi\Test` as part of `setUp()`, it will be executed several times (for every test). However, it will usually be a null-op. It will only incur a notable performance penalty when you call with *different* configurations.
   * How: Everytime you run `apply()`, it computes a signature for the requested steps. If the signature is already stored (table `civitest_revs`), then it does nothing. If the signature is new/changed, then it runs.
 * `Civi\Test` is stupid. It only knows what you tell it.
   * Ex: If you independently executed `INSERT INTO civicrm_contact` or `TRUNCATE civicrm_option_value`, it won't reset automatically.
   * Tip: If you know that your test cases are particularly dirty, you can force `Civi\Test` to execute by calling `apply(TRUE)` (aka `apply($force === TRUE)`). This may incur a significant performance penalty for the overall suite.
 * PATCHWELCOME: If you need to test with custom-data, consider adding more helper functions to `Civi\Test`. Handling custom-data at this level (rather than the test body) should reduce the amount of work spent on tearing-down/re-creating custom data schema, and it should allow better use of transactions.

## 2. Incremental cleanup with transactions, etal

Within the body of a test, you may do more fine-grained data maniuplation,
such as creating new contacts, events, or memberships. But leaving these
artifacts can impact other, unrelated tests.

(*Example: Suppose `testA()` checks that membership-statuses transition over time, and suppose `testB()` checks that scheduled reminders work for memberships. Old membership records left by `testA()` can impact the behavior of `testB()`, causing unexpected variations in test outcomes.*)

There are a few ways to address this:

 * Execute each test inside a SQL transaction, and rollback at the end of each test. (In `phpunit`, use the `TransactionalInterface`. Otherwise, see `CRM_Core_Transaction`.)
   * Note: This is cool because it's pretty automatic and generic, but it doesn't work if the body of the test calls `CREATE TABLE`, `ALTER TABLE`, or `TRUNCATE`. For custom-data, try managing the custom-data through `Civi\Test` instead.
 * Write the test-suite in a way where these hidden interactions do not occur.
 * Keep track of each change, and clean it up explicitly at the end of each test.
