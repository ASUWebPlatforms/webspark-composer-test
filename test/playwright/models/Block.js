import { expect } from '@playwright/test'
import { faker } from '@faker-js/faker/locale/en'
import drupal from '../helpers/Drupal'

class Block {
  /**
   * Block model for Playwright tests.
   * @param {import('playwright').Page} page
   * @param {string} name
   */
  constructor (page, name) {
    this.page = page
    this.name = name
    this.defaultUrl = 'https://asu.edu'
    this.defaultIcon = 'Pyramid,ASUAwesome,Shapes,'

    this.inputAddBlockFirstRegion = page.getByRole('link', { name: 'Add block in Top, First region' })
    this.inputAddBlock = page.getByRole('link', { name: 'Add block in Content, First region' })
    this.inputCreateContentBlock = page.getByRole('link', { name: 'Create content block' })
    this.inputAddByName = page.getByRole('link', { name: name, exact: true })
    this.inputBlockAdminTitle = page.getByRole('textbox', { name: 'Block admin title' })
    this.inputDisplayBlockTitle = page.getByRole('checkbox', { name: 'Display title' })
    this.inputSaveBlock = page.getByRole('button', { name: 'Add block' })
    this.inputUpdateBlock = page.getByRole('button', { name: 'Update' })
    this.inputSaveLayout = page.getByRole('button', { name: 'Save layout' })
    this.inputAppearanceSettings = page.getByRole('button', { name: 'Appearance Settings' })
    this.inputAnchorMenuTitle = page.getByRole('textbox', { name: 'Anchor menu title' })
    this.inputSpacingTop = page.getByLabel('Spacing top')
    this.inputSpacingBottom = page.getByLabel('Spacing bottom')
    this.inputFA = page.locator('.fip-icon-down-dir').first()
    this.inputFAIcon = page.getByTitle(this.defaultIcon).first()
    this.inputAnchorMenu = page.getByRole('link', { name: 'Anchor Menu' })
    this.inputBreadcrumb = page.getByRole('link', { name: 'ASU Breadcrumb' })
    this.elBreadcrumbColor = page.getByLabel('Select color')
  }

  /**
   * Add a new block.
   * @returns {Promise<void>}
   */
  async add () {
    await this.inputAddBlock.click()
    await this.inputCreateContentBlock.click()
    await this.inputAddByName.click()
    await this.inputBlockAdminTitle.fill(this.name)
  }

  /**
   * Add a repeater item content to the block.
   * @param {number} i The locator index
   * @returns {Promise<void>}
   */
  async addItem (i = 0) {
    throw new Error('addItem() must be implemented in the subclass')
  }

  /**
   * Add content to the block.
   * @returns {Promise<void>}
   */
  async addContent () {
    throw new Error('addContent() must be implemented in the subclass')
  }

  /**
   * Save the block.
   * @returns {Promise<void>}
   */
  async save () {
    await this.inputSaveBlock.click()
    await this.inputSaveLayout.click()
  }

  /**
   * Update the block.
   * @returns {Promise<void>}
   */
  async update () {
    await this.inputUpdateBlock.click()
    await this.inputSaveLayout.click()
  }

  /**
   * Verify the block via tests.
   * @returns {Promise<void>}
   */
  async verify () {
    throw new Error('verify() must be implemented in the subclass')
  }

  /**
   * Add spacing to the block.
   * @param {string} label
   * @returns {Promise<void>}
   */
  async addSpacingSettings(label = '8px') {
    await this.inputAppearanceSettings.click()
    await this.inputSpacingTop.selectOption({ label: label })
    await this.inputSpacingBottom.selectOption({ label: label })
  }

  /**
   * Add the block to the Anchor Menu.
   * @returns {Promise<void>}
   */
  async addAnchorMenuSettings () {
    await this.inputDisplayBlockTitle.check()
    await this.inputAppearanceSettings.click()
    await this.inputAnchorMenuTitle.fill(this.name)
    await this.inputFA.click()
    await this.inputFAIcon.click()
  }

  /**
   * Verify the Call to Action field.
   * NOTE: Should this really be in the Block class?
   * @param {import('@playwright/test').Locator} locator
   * @returns {Promise<void>}
   */
  async verifyCTA (locator) {
    await expect(locator).toBeVisible()
    await expect(locator).toHaveClass(/btn-maroon/)
    await expect(locator).toHaveAttribute('href', this.defaultUrl)
    await expect(locator).toHaveAttribute('target', '_blank')
  }

  /**
   * General test for carousel functionality.
   *
   * @param {Object} options - Optional parameters for carousel testing
   * @param {int} options.count - The number of slides in the carousel (default: 3)
   * @param {int} options.pages - The number of paginated pages in the carousel (default: 3)
   * @param {string} options.role - The ARIA role for the pagination buttons (default: 'button')
   * @returns {Promise<void>}
   */
  async verifyCarousel(options = {}) {
    // TODO: See about getting rid of the timeouts for efficiency
    const count = options.count ?? 3;
    const pages = options.pages ?? 3;
    const role = options.role ?? 'button';

    const slide = this.page.locator('.glide__slide');
    const pager = this.page.getByRole(role, { name: `Slide view ${pages}` });
    const prev = this.page.getByRole('button', { name: 'Previous slide' });
    const next = this.page.getByRole('button', { name: 'Next slide' });

    await expect(slide).toHaveCount(count);
    await expect(slide.nth(0)).toBeVisible();

    await expect(slide.nth(0)).toHaveClass(/glide__slide--active/);
    await expect(slide.nth(1)).not.toHaveClass(/glide__slide--active/);

    await expect(prev).toBeDisabled();
    await next.click();
    await this.page.waitForTimeout(1000);

    await expect(slide.nth(0)).not.toHaveClass(/glide__slide--active/);
    await expect(slide.nth(1)).toHaveClass(/glide__slide--active/);

    await pager.click();
    await this.page.waitForTimeout(1000);

    await expect(pager).toHaveClass(/glide__bullet--active/);
    await expect(slide.nth(1)).not.toHaveClass(/glide__slide--active/);
    await expect(slide.nth(2)).toHaveClass(/glide__slide--active/);

    await expect(next).toBeDisabled();
    await prev.click();
    await this.page.waitForTimeout(1000);

    await expect(slide.nth(2)).not.toHaveClass(/glide__slide--active/);
  }

  async edit () {}
  async delete () {}
  async verifyIcon () {}
  async verifyMedia () {}
}

export { Block }
