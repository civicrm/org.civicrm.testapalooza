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
 * @group headless
 */
class CRM_Testapalooza_MyHeadlessTest extends \PHPUnit_Framework_TestCase
  implements HeadlessInterface, HookInterface {

  public function setUpHeadless() {
    return CiviTester::headless()
      ->extDir(__DIR__)
      ->apply();
  }

  /**
   * @param $content
   * @see \CRM_Utils_Hook::alterContent
   */
  public function hook_civicrm_alterContent(&$content, $context, $tplName, &$object) {
    $content .= 'Testapalooza!!';
  }

  /**
   * Test that a version is returned.
   */
  public function testWellFormedVersion() {
    $this->assertRegExp('/^([0-9\.]|alpha|beta)*$/', CRM_Utils_System::version());
  }

  public function testPageOutput() {
    ob_start();
    $p = new CRM_Testapalooza_Page_FooBar();
    $p->run();
    $content = ob_get_contents();
    ob_end_clean();
    $this->assertRegExp(';This new page is generated by CRM/Testapalooza/Page/FooBar.php;', $content);
    $this->assertRegExp(';Testapalooza!!;', $content);
  }

  public function testExtClassLoaders() {
    $this->assertEquals(CRM_Testapalooza_FooBar::double(3), 6);
    $this->assertEquals(\Civi\Testapalooza\FooBar::square(4), 16);
  }

}