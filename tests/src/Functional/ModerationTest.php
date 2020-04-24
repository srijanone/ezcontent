<?php

namespace Drupal\Tests\ezcontent\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Functional test case for ezcontent.
 *
 * @group ezcontent
 */
class ModerationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'ezcontent';

  /**
   * Remove from ezcontent.info file email_registration and gin_login.
   *
   * Module before running functional tests.
   *
   * @todo: Make this work with gin_login and email_registration module.
   */
  public function setUp() {
    // Ignore schema errors.
    $this->strictConfigSchema = FALSE;
    parent::setUp();
    // Login as admin user.
    $this->drupalLogin($this->rootUser);
  }

  /**
   * It if admin user is able to login.
   */
  public function testAdminLogin() {
    $this->drupalGet('/user/1');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * It test article content type.
   */
  public function testContentTypeArticle() {
    $this->drupalGet('/node/add/article');
    $this->assertSession()->statusCodeEquals(200);
    // Assert that the image thumbnail and add paragraph
    // button is present in the HTML.
    $this->assertRaw('id="edit-field-thumbnail-entity-browser-entity-browser-open-modal"');
    $this->assertRaw('id="edit-field-content-add-more-add-modal-form-area-add-more"');
  }

  /**
   * It test basic page content type.
   */
  public function testContentTypePage() {
    $this->drupalGet('/node/add/page');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * It test author content type.
   */
  public function testContentTypeAuthor() {
    $this->drupalGet('/node/add/author');
    $this->assertSession()->statusCodeEquals(200);
    // Assert that the image thumbnail and add paragraph
    // button is present in the HTML.
    $this->assertRaw('id="edit-field-thumbnail-wrapper"');
    $this->assertFieldByName('body[0][value]');
  }

}
