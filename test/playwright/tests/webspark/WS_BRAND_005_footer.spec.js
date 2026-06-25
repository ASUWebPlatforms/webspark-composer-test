import { test, expect } from '@playwright/test'
import { Footer } from '../../models/AsuBrand.js'

/** @type {import('@playwright/test').Page} */
let page
let component
const title = 'Footer'

test.describe(title, { tag: ['@webspark', '@brand'] }, () => {
  test.describe.configure({ mode: 'serial' })

  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage()
    component = new Footer(page, title)
  })

  test.afterAll(async () => {
    await page.close()
  })

  test('create', async () => {
    await component.add()
    await component.save()
  })

  test('verify', async () => {
    await component.verify()
  })
})
