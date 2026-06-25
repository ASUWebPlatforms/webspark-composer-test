<?php

namespace Drupal\Tests\wpc_rfi_forms_programs\Kernel;

use Drupal\wpc_rfi_forms_programs\Entity\Program;
use Drupal\KernelTests\KernelTestBase;

/**
 * Test basic CRUD operations for our Program entity type.
 *
 * @group wpc_rfi_forms_programs
 * @group wpc_rfi_forms
 *
 * @ingroup wpc_rfi_forms_programs
 */
class ProgramTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['wpc_rfi_forms_programs', 'wpc_rfi_forms'];

  /**
   * Basic CRUD operations on a Program entity.
   */

  public function testEntity() {
    $this->installEntitySchema('wpc_rfi_forms_programs_program');
    $entity = Program::create([
      'name' => 'Name',
      'ps_acad_plan_descr_key' => 'psacadplandescrkey',
      'ps_acad_plan_key' => 'psacadplankey',
      'campus_key' => 'campuskey',
      'user_id' => 0,
    ]);
    $this->assertNotNull($entity);
    $this->assertEquals(SAVED_NEW, $entity->save());
    $this->assertEquals(SAVED_UPDATED, $entity->save());
    $entity_id = $entity->id();
    $this->assertNotEmpty($entity_id);
    $entity->delete();
    $this->assertNull(Program::load($entity_id));
  }

}
