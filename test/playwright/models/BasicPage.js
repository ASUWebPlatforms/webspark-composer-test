import { expect } from '@playwright/test'
import { faker } from '@faker-js/faker/locale/en'
import { Node } from './Node'

class BasicPage extends Node {
  /**
   * BasicPage model for Playwright tests.
   * @param {import('playwright').Page} page
   * @param {string} name
   */
  constructor (page, name) {
    super(page, name)
    this.title = `Playwright ${this.name}`
    this.content = faker.lorem.sentence()

    this.inputBody = page.getByLabel('Rich Text Editor').getByRole('textbox')
    this.inputSaveBlock = page.getByRole('button', { name: 'Add block' })
    this.inputUpdateBlock = page.getByRole('button', { name: 'Update' })
    this.inputSaveLayout = page.getByRole('button', { name: 'Save layout' })
    this.inputAnchorMenu = page.getByRole('link', { name: 'Anchor Menu' })
    this.inputBreadcrumb = page.getByRole('link', { name: 'ASU Breadcrumb' })
    this.inputAddBlockFirstRegion = page.getByRole('link', { name: 'Add block in Top, First region' })
    this.elBreadcrumbColor = page.getByLabel('Select color')
    this.inputSpacingTop = page.getByLabel('Spacing top')
    this.inputSpacingBottom = page.getByLabel('Spacing bottom')

    this.elTitle = page.getByRole('heading', { name: this.title })
    this.elContent = page.getByText(this.content)
  }

  /**
   * Add a new basic page node.
   * @returns {Promise<void>}
   */
  async add () {
    await this.page.goto('/node/add/page')
    await this.inputTitle.fill(this.title)
    await this.save()
    await expect(this.status).toHaveClass(/alert-success/)
    await expect(this.elTitle).toBeVisible()
    await this.setNodeProperties()
  }

  /**
   * Add content to the basic page node.
   * @returns {Promise<void>}
   */
  async addContent () {
    await this.inputBody.fill(this.content)
    // TODO: Extract this out to its own test, like the blocks
    await this.save()
    await expect(this.elContent).toBeVisible()
  }

  /**
   * Add an Anchor Menu.
   * @returns {Promise<void>}
   */
  async addAnchorMenu () {
    await this.inputAddBlockFirstRegion.click();
    await this.inputAnchorMenu.click();
    await this.inputSaveBlock.click();
  }

  /**
   * Add Breadcrumb.
   * @returns {Promise<void>}
   */
  async addBreadcrumb () {
    await this.inputAddBlockFirstRegion.click()
    await this.inputBreadcrumb.click()
    await this.elBreadcrumbColor.selectOption({ label: 'Gray 1' })
    await this.inputSpacingTop.selectOption({ label: '8px' })
    await this.inputSpacingBottom.selectOption({ label: '8px' })
    await this.inputSaveBlock.click()
  }
}

export { BasicPage }
