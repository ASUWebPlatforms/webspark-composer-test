import { test, expect } from '@playwright/test'
import { BasicPage } from '../../models/BasicPage.js'
import { Lead } from '../../models/CKEPlugins.js'

/** @type {import('@playwright/test').Page} */
let page
let node, plugin
const title = 'Lead'

test.describe(title, { tag: ['@webspark', '@ckeditor'] }, () => {
  test.describe.configure({ mode: 'serial' })

  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage()
    node = new BasicPage(page, title)
    plugin = new Lead(page, title)
  })

  test.afterAll(async () => {
    await page.close()
  })

  test('create', async () => {
    await node.add()
    await node.edit()
    await plugin.add()
    await plugin.addContent()
    await node.save()
  })

  test('verify', async () => {
    await plugin.verify()
  })
})
