import { test, expect } from '@playwright/test'
import { BasicPage } from '../../models/BasicPage.js'
import { HorizonalRule } from '../../models/CKEPlugins.js'

/** @type {import('@playwright/test').Page} */
let page
let node, plugin
const title = 'Horizontal line'

test.describe(title, { tag: ['@webspark', '@ckeditor'] }, () => {
  test.describe.configure({ mode: 'serial' })

  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage()
    node = new BasicPage(page, title)
    plugin = new HorizonalRule(page, title)
  })

  test.afterAll(async () => {
    await page.close()
  })

  test('create', async () => {
    await node.add()
    await node.edit()
    await plugin.add()
    await node.save()
  })

  test('verify', async () => {
    await plugin.verify()
  })
})
