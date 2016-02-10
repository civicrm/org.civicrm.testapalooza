<?php

use \Civi\Test\HookInterface;
use \Civi\Test\HeadlessInterface;

/**
 * This is a lightweight unit-tested based on PHPUnit_Framework_TestCase.
 *
 * PHPUnit_Framework_TestCase is suitable for any of these:
 *  - Running tests which don't require any database.
 *  - Running tests on the main/live database.
 *  - Customizing the setup/teardown processes.
 *
 * @group e2e
 */
class CRM_Testapalooza_MyEndToEndTest extends \PHPUnit_Framework_TestCase
  implements \Civi\Test\EndToEndInterface {

  public static function setUpBeforeClass() {
    // Example: Install this extension. Don't care about anything else.
    Civi\Test::e2e()->installMe(__DIR__)->apply();

    // Example: Uninstall all extensions except this one.
    // Civi\Test::e2e()->uninstall('*')->installMe(__DIR__)->apply();

    // Example: Install only core civicrm extensions.
     //Civi\Test::e2e()->uninstall('*')->install('org.civicrm.*')->apply();
  }

  /**
   * Test that a version is returned.
   */
  public function testWellFormedVersion() {
    $this->assertRegExp('/^([0-9\.]|alpha|beta)*$/', CRM_Utils_System::version());
  }

  /**
   * Test that we're using a real CMS (Drupal, WordPress, etc).
   */
  public function testWellFormedUF() {
    $this->assertRegExp('/^(Drupal|Backdrop|WordPress|Joomla)/', CIVICRM_UF);
  }

  /**
   * Test that classes from the extension are available.
   */
  public function testExtClassLoaders() {
    $this->assertEquals(CRM_Testapalooza_FooBar::double(3), 6);
    $this->assertEquals(\Civi\Testapalooza\FooBar::square(4), 16);
  }

}
