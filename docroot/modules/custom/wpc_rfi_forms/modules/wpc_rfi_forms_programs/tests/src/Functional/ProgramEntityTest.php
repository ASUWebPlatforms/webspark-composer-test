<?php

namespace Drupal\Tests\wpc_rfi_forms_programs\Functional;

use Drupal\wpc_rfi_forms_programs\Entity\Program;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\examples\Functional\ExamplesBrowserTestBase;
use Drupal\Core\Url;

/**
 * Tests the basic functions of the WPC RFI Forms Program module.
 *
 * @ingroup wpc_rfi_forms_programs
 *
 * @group wpc_rfi_forms_programs
 * @group wpc_rfi_forms
 */
class ProgramEntityTest extends ExamplesBrowserTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public static $modules = ['wpc_rfi_forms_programs', 'block', 'field_ui'];

  /**
   * Basic tests for Programs Entities.
   */
  public function testPrograms() {
    $assert = $this->assertSession();

    $web_user = $this->drupalCreateUser([
      'add program entity',
      'edit program entity',
      'view program entity',
      'delete program entity',
      'administer program entity',
      'administer wpc_rfi_forms_programs_program display',
      'administer wpc_rfi_forms_programs_program fields',
      'administer wpc_rfi_forms_programs_program form display',
    ]);

    // Anonymous User should not see the link to the listing.
    $assert->pageTextNotContains('Program Entity Example');

    $this->drupalLogin($web_user);

    // Web_user user has the right to view listing.
    $assert->linkExists('Program Entity Example');

    $this->clickLink('Program Entity Example');

    // WebUser can add entity content.
    $assert->linkExists('Add program');

    $this->clickLink($this->t('Add program'));

    $assert->fieldValueEquals('name[0][value]', '');
    $assert->fieldValueEquals('name[0][value]', '');
    $assert->fieldValueEquals('name[0][value]', '');
    $assert->fieldValueEquals('name[0][value]', '');

    $user_ref = $web_user->name->value . ' (' . $web_user->id() . ')';
    $assert->fieldValueEquals('user_id[0][target_id]', $user_ref);

    // Post content, save an instance. Go back to list after saving.

    $edit = [
      'name[0][value]' => 'test name',
      'ps_acad_plan_descr_key[0][value]' => 'test system plan',
      'ps_acad_plan_key[0][value]' => 'test plan key',
      'campus_key[0][value]' => 'test campus key',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    // Entity listed.
    $assert->linkExists('Edit');
    $assert->linkExists('Delete');

    $this->clickLink('test name');

    // Entity shown.
    $assert->pageTextContains('test name');
    $assert->pageTextContains('test system plan');
    $assert->pageTextContains('test plan key');
    $assert->pageTextContains('test campus key');
    $assert->linkExists('Add program');
    $assert->linkExists('Edit');
    $assert->linkExists('Delete');

    // Delete the entity.
    $this->clickLink('Delete');

    // Confirm deletion.
    $assert->linkExists('Cancel');
    $this->drupalPostForm(NULL, [], 'Delete');

    // Back to list, must be empty.
    $assert->pageTextNotContains('test name');

    // Settings page.
    $this->drupalGet('admin/structure/wpc_rfi_forms_programs_program_settings');
    $assert->pageTextContains('Program Settings');

    // Make sure the field manipulation links are available.
    $assert->linkExists('Settings');
    $assert->linkExists('Manage fields');
    $assert->linkExists('Manage form display');
    $assert->linkExists('Manage display');
  }

  /**
   * Test all paths exposed by the module, by permission.
   */
  public function testPaths() {
    $assert = $this->assertSession();

    // Generate a program so that we can test the paths against it.
    $program = Program::create([
      'name' => 'Business Analytics-Online',
      'ps_acad_plan_descr_key' => 'Business Analytics',
      'ps_acad_plan_key' => 'BABUSANMS',
      'campus_key' => 'Online',
 
    ]);
    $program->save();

    // Gather the test data.
    $data = $this->providerTestPaths($program->id());

    // Run the tests.
    foreach ($data as $datum) {
      // drupalCreateUser() doesn't know what to do with an empty permission
      // array, so we help it out.
      if ($datum[2]) {
        $user = $this->drupalCreateUser([$datum[2]]);
        $this->drupalLogin($user);
      }
      else {
        $user = $this->drupalCreateUser();
        $this->drupalLogin($user);
      }
      $this->drupalGet($datum[1]);
      $assert->statusCodeEquals($datum[0]);
    }
  }

  /**
   * Data provider for testPaths.
   *
   * @param int $program_id
   *   The id of an existing Program entity.
   *
   * @return array
   *   Nested array of testing data. Arranged like this:
   *   - Expected response code.
   *   - Path to request.
   *   - Permission for the user.
   */
  protected function providerTestPaths($program_id) {
    return [
      [
        200,
        '/wpc_rfi_forms_programs_program/' . $program_id,
        'view program entity',
      ],
      [
        403,
        '/wpc_rfi_forms_programs_program/' . $program_id,
        '',
      ],
      [
        200,
        '/wpc_rfi_forms_programs_program/list',
        'view program entity',
      ],
      [
        403,
        '/wpc_rfi_forms_programs_program/list',
        '',
      ],
      [
        200,
        '/wpc_rfi_forms_programs_program/add',
        'add program entity',
      ],
      [
        403,
        '/wpc_rfi_forms_programs_program/add',
        '',
      ],
      [
        200,
        '/wpc_rfi_forms_programs_program/' . $program_id . '/edit',
        'edit program entity',
      ],
      [
        403,
        '/wpc_rfi_forms_programs_program/' . $program_id . '/edit',
        '',
      ],
      [
        200,
        '/programs/' . $program_id . '/delete',
        'delete program entity',
      ],
      [
        403,
        '/programs/' . $program_id . '/delete',
        '',
      ],
      [
        200,
        'admin/structure/wpc_rfi_forms_programs_program_settings',
        'administer program entity',
      ],
      [
        403,
        'admin/structure/wpc_rfi_forms_programs_program_settings',
        '',
      ],
    ];
  }

  /**
   * Test add new fields to the program entity.
   */
  public function testAddFields() {
    $web_user = $this->drupalCreateUser([
      'administer program entity',
      'administer wpc_rfi_forms_programs_program display',
      'administer wpc_rfi_forms_programs_program fields',
      'administer wpc_rfi_forms_programs_program form display',
    ]);

    $this->drupalLogin($web_user);
    $entity_name = 'wpc_rfi_forms_programs_program';
    $add_field_url = 'admin/structure/' . $entity_name . '_settings/fields/add-field';
    $this->drupalGet($add_field_url);
    $field_name = 'test_name';
    $edit = [
      'new_storage_type' => 'list_string',
      'label' => 'test name',
      'field_name' => $field_name,
    ];

    $this->drupalPostForm(NULL, $edit, 'Save and continue');
    $expected_path = $this->buildUrl('admin/structure/' . $entity_name . '_settings/fields/' . $entity_name . '.' . $entity_name . '.field_' . $field_name . '/storage');

    // Fetch url without query parameters.
    $current_path = strtok($this->getUrl(), '?');
    $this->assertEquals($expected_path, $current_path);
  }

  /**
   * Ensure admin and permissioned users can create programs.
   */
  public function testCreateAdminPermission() {
    $assert = $this->assertSession();
    $add_url = Url::fromRoute('Program Entity Example.program_add');

    // Create a Program entity object so that we can query it for it's annotated
    // properties. We don't need to save it.
    /* @var $program \Drupal\wpc_rfi_forms_programs\Entity\Program */
    $program = Program::create();

    // Create an admin user and log them in. We use the entity annotation for
    // admin_permission in order to validate it. We also have to add the view
    // list permission because the add form redirects to the list on success.
    $this->drupalLogin($this->drupalCreateUser([
      $program->getEntityType()->getAdminPermission(),
      'view program entity',
    ]));

    // Post a program.
    $edit = [
      'name[0][value]' => 'Test Program Display Name',
      'ps_acad_plan_descr_key[0][value]' => 'Program System Name Example',
      'ps_acad_plan_key[0][value]' => 'Program Key Example',
      'campus_key[0][value]' => 'Program Campus Example',
    ];
    $this->drupalPostForm($add_url, $edit, 'Save');
    $assert->statusCodeEquals(200);
    $assert->pageTextContains('Test Program Display Name');

    // Create a user with 'add program entity' permission. We also have to add
    // the view list permission because the add form redirects to the list on
    // success.
    $this->drupalLogin($this->drupalCreateUser([
      'add program entity',
      'view program entity',
    ]));

    // Post a program.
    $edit = [
      'name[0][value]' => 'Popular Program Name',
      'ps_acad_plan_descr_key[0][value]' => 'Popular Program System Name Example',
      'ps_acad_plan_key[0][value]' => 'ABC123',
      'campus_key[0][value]' => 'Tempe',
    ];
    $this->drupalPostForm($add_url, $edit, 'Save');
    $assert->statusCodeEquals(200);
    $assert->pageTextContains('Popular Program Name');

    // Finally, a user who can only view should not be able to get to the add
    // form.
    $this->drupalLogin($this->drupalCreateUser([
      'view program entity',
    ]));
    $this->drupalGet($add_url);
    $assert->statusCodeEquals(403);
  }

}
