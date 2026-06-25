// TODO: This file should be reframed as a helper file, similar to the Drupal helper file
import { expect } from '@playwright/test'
import { faker } from '@faker-js/faker/locale/en'
import drupal from '../helpers/Drupal'

class React {
  /**
   * React model for Playwright tests.
   * @param {import('playwright').Page} page
   */
  constructor (page) {
    this.page = page
  }

  /**
   * Get the props for the component.
   * @param {string} path The object path to the props in drupalSettings
   * @returns {Promise<unknown>}
   */
  async getProps (path) {
    await this.page.waitForLoadState()
    const props = await this.page.evaluate((keyPath) => {
      const keys = keyPath.split('.')
      let result = window.drupalSettings

      for (const key of keys) {
        if (result && typeof result === 'object' && key in result) {
          result = result[key]
        } else {
          return undefined
        }
      }

      return result
    }, path)

    await expect(props).toBeDefined()
    expect(typeof props).toBe('object')
    return props
  }
}

export { React }
