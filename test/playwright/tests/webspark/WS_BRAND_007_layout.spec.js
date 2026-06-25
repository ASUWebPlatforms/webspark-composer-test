import { test, expect } from '@playwright/test'
import { BasicPage } from '../../models/BasicPage.js'
import { Layout } from '../../models/AsuBrand.js'

/** @type {import('@playwright/test').Page} */
let page
let node, component
const title = 'Layout'

test.describe(title, { tag: ['@webspark', '@brand'] }, () => {
  test.describe.configure({ mode: 'serial' })

  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage()
    node = new BasicPage(page, title)
    component = new Layout(page, title)
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
