<?php

/**
 * @file
 * Contains \Drupal\node\Tests\NodeRevisionsUiBypassAccessTest.
 */

namespace Drupal\node\Tests;

use Drupal\node\Entity\NodeType;

/**
 * Tests the revision tab display.
 *
 * This test is similar to NodeRevisionsUITest except that it uses a user with
 * the bypass node access permission to make sure that the revision access
 * check adds correct cacheability metadata.
 *
 * @group node
 */
class NodeRevisionsUiBypassAccessTest extends NodeTestBase {

  /**
   * User with bypass node access permission.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $editor;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['block'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create a user.
    $this->editor = $this->drupalCreateUser([
      'administer nodes',
      'edit any page content',
      'view page revisions',
      'bypass node access',
      'access user profiles',
    ]);
  }

  /**
   * Checks that the Revision tab is displayed correctly.
   */
  function testDisplayRevisionTab() {
    $this->drupalPlaceBlock('local_tasks_block');

    $this->drupalLogin($this->editor);
    $node_storage = $this->container->get('entity.manager')->getStorage('node');

    // Set page revision setting 'create new revision'. This will mean new
    // revisions are created by default when the node is edited.
    $type = NodeType::load('page');
    $type->setNewRevision(TRUE);
    $type->save();

    // Create the node.
    $node = $this->drupalCreateNode();

    // Verify the checkbox is checked on the node edit form.
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertFieldChecked('edit-revision', "'Create new revision' checkbox is checked");

    // Uncheck the create new revision checkbox and save the node.
    $edit = array('revision' => FALSE);
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, 'Save and keep published');

    $this->assertUrl($node->toUrl());
    $this->assertNoLink(t('Revisions'));

    // Verify the checkbox is checked on the node edit form.
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertFieldChecked('edit-revision', "'Create new revision' checkbox is checked");

    // Submit the form without changing the checkbox.
    $edit = array();
    $this->drupalPostForm('node/' . $node->id() . '/edit', $edit, 'Save and keep published');

    $this->assertUrl($node->toUrl());
    $this->assertLink(t('Revisions'));
  }

}
