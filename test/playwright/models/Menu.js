import { expect } from '@playwright/test'
import drupal from '../helpers/Drupal'

class Menu {
  constructor (page, name) {
    this.page = page
    this.name = name
    this.front = '<front>'
    this.nolink = '<nolink>'
    this.machine = null
    this.node = null

    this.inputTitle = page.getByRole('textbox', { name: 'Title *' })
    this.inputParentLink = page.getByLabel('Parent link')
    this.inputLink = page.getByRole('textbox', { name: 'Link *' })
    this.inputLinkTitle = page.getByRole('textbox', { name: 'Menu link title *' })
    this.inputSave = page.getByRole('button', { name: 'Save' })
    this.inputDelete = page.getByRole('link', { name: 'Delete' })
    this.inputDeleteConfirm = page.getByRole('button', { name: 'Delete' })
  }

  /**
   * Set the path of the node where the menu will be.
   * @returns {Promise<void>}
   */
  async setNodePath (path) {
    this.node = await path
  }

  /**
   * Set the machine name of the menu.
   * @returns {Promise<null|*>}
   */
  async #setMenuMachineName () {
    this.machine = this.name.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '').replace(/-+/g, '-').replace(/^-|-$/g, '')
  }

  /**
   * Add a new menu.
   * @returns {Promise<void>}
   */
  async add () {
    await this.page.goto('/admin/structure/menu/add')
    await this.inputTitle.fill(this.name)
    await this.inputSave.click()
    await this.#setMenuMachineName()
  }

  /**
   * Add a new menu item.
   * @returns {Promise<void>}
   */
  async addItem (title, link) {
    await this.page.goto(`/admin/structure/menu/manage/${this.machine}/add`)
    await this.inputLinkTitle.fill(title)
    await this.inputLink.fill(link)
    await this.inputSave.click()
  }

  /**
   * Add a new menu sub-item.
   * @returns {Promise<void>}
   */
  async addSubItem (parent, title, link) {
    await this.page.goto(`/admin/structure/menu/manage/${this.machine}/add`)
    await this.inputLinkTitle.fill(title)
    await this.inputLink.fill(link)
    await this.inputParentLink.selectOption({ label: `-- ${parent}` })
    await this.inputSave.click()
  }

  /**
   * Add a heading item to the menu.
   * @returns {Promise<void>}
   */
  async addHeadingItem () {
    throw new Error('addHeadingItem() must be implemented in the subclass')
  }

  /**
   * Add a stackable heading item to the menu.
   * @returns {Promise<void>}
   */
  async addStackableHeadingItem () {
    throw new Error('addStackableHeadingItem() must be implemented in the subclass')
  }

  /**
   * Add a column break item to the menu.
   * @returns {Promise<void>}
   */
  async addColumnBreakItem () {
    throw new Error('addColumnBreakItem() must be implemented in the subclass')
  }

  /**
   * Add a button item to the menu.
   * @returns {Promise<void>}
   */
  async addButtonItem () {
    throw new Error('addButtonItem() must be implemented in the subclass')
  }

  /**
   * Add items to the menu.
   * @returns {Promise<void>}
   */
  async addMenuItems () {
    throw new Error('addMenuItems() must be implemented in the subclass')
  }

  /**
   * Save the menu.
   * @returns {Promise<void>}
   */
  async save () {
    await this.inputSave.click()
  }

  /**
   * Delete the menu.
   * @returns {Promise<void>}
   */
  async delete () {
    await this.page.goto(`/admin/structure/menu/manage/${this.machine}`)
    await this.inputDelete.click()
    await this.inputDeleteConfirm.click()
  }
}

export { Menu }
