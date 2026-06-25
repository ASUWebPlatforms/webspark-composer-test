import { test, expect } from '@playwright/test'
import { Search } from '../../models/AsuBrand.js'

/** @type {import('@playwright/test').Page} */
let page
let component
const title = 'Search'

test.describe(title, { tag: ['@webspark', '@brand'] }, () => {
  test.describe.configure({ mode: 'serial' })

  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage()
    component = new Search(page, title)
  })

  test.afterAll(async () => {
    await page.close()
  })

  test('create', async () => {
    await component.add()
  })

  test('verify', async () => {
    await component.verify()
  })
})
