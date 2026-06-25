import { test, expect } from '@playwright/test'
import { BasicPage } from '../../models/BasicPage.js'
import { Spacing } from '../../models/AsuBrand.js'

/** @type {import('@playwright/test').Page} */
let page
let node, component
const title = 'Spacing'

test.describe(title, { tag: ['@webspark', '@brand'] }, () => {
  test.describe.configure({ mode: 'serial' })

  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage()
    node = new BasicPage(page, title)
    component = new Spacing(page, title)
  })

  test.afterAll(async () => {
    await page.close()
  })

  test('create', async () => {
    await node.add()
    await node.goToLayout()
    await component.add()
    await component.save()
  })

  test('verify', async () => {
    await component.verify()
  })
})
