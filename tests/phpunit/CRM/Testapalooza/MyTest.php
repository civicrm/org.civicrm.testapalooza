<?php

/**
 * FIXME
 */
class CRM_Testapalooza_MyTest extends \PHPUnit_Framework_TestCase {
  function setUp() {
    parent::setUp();
  }

  function tearDown() {
    parent::tearDown();
  }

  /**
   * Test that version is returned.
   */
  function testSquareOfOne() {
    $this->assertRegExp('/^([0-9\.]|alpha|beta)*$/', CRM_Utils_System::version());
  }

}