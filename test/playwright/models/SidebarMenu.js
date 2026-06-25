import { Menu } from './Menu'

class SidebarMenu extends Menu {
  /**
   * Sidebar Menu model for Playwright tests.
   * @param {import('playwright').Page} page
   * @param {string} name
   */
  constructor (page, name) {
    super(page, name)
  }

  async addMenuItems () {
    // NOTE: Items are not guaranteed to appear in this exact order
    await this.addItem('Link', this.front)
    await this.addItem(this.name, this.node)
    await this.addItem('Link', this.nolink)
    await this.addSubItem('Link', 'Sublink', this.nolink)
    await this.save()
  }
}

export { SidebarMenu }
