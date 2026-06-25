// NOTE: Sometimes this test will pick up real login states, causing it to fail
import { test, expect } from '@playwright/test'
import { Cas } from '../../models/AsuBrand.js'

/** @type {import('@playwright/test').Page} */
let page
let component
const title = 'Cas'

// Reset storage state for this file to avoid being authenticated
test.use({ storageState: { cookies: [], origins: [] } })

test.describe(title, { tag: ['@webspark', '@brand'] }, () => {
  test.describe.configure({ mode: 'serial' })

  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage()
    component = new Cas(page, title)
  })

  test.afterAll(async () => {
    await page.close()
  })

  test('verify', async () => {
    await component.verify()
  })
})
