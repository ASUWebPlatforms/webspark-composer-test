import { expect } from '@playwright/test'
import drupal from '../helpers/Drupal'

class CKEditor {
  constructor (page, name) {
    this.page = page
    this.name = name

    this.inputPlugin = page.getByRole('button', { name: this.name, exact: true })
    this.inputDropdown = page.getByLabel('Dropdown toolbar')
    this.inputDropdownSave = this.inputDropdown.getByRole('button', { name: 'Save' })
    this.inputToolbar = page.getByRole('toolbar', { name: 'Editor toolbar' })
    this.inputToolbarSave = this.inputToolbar.getByRole('button', { name: 'Save' })
    this.inputSave = page.locator('#edit-submit')
  }

  /**
   * Add the plugin.
   * @returns {Promise<void>}
   */
  async add () {
   await this.inputPlugin.click();
  }

  /**
   * Add content to the plugin.
   * @returns {Promise<void>}
   */
  async addContent () {
    throw new Error('addContent() must be implemented in the subclass')
  }

  /**
   * Save the plugin.
   * @returns {Promise<void>}
   */
  async save () {
    await this.inputSave.click()
  }

  /**
   * Save the plugin via the dropdown.
   * @returns {Promise<void>}
   */
  async saveDropdown () {
    await this.inputDropdownSave.click()
  }

  /**
   * Save the plugin via the toolbar.
   * @returns {Promise<void>}
   */
  async saveToolbar () {
    await this.inputToolbarSave.click()
  }

  /**
   * Verify the plugin via tests.
   * @returns {Promise<void>}
   */
  async verify () {
    throw new Error('verify() must be implemented in the subclass')
  }
}

export { CKEditor }
