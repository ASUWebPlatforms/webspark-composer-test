import { test, expect } from '@playwright/test'
import { BasicPage } from '../../models/BasicPage.js'
import { WebDirectory } from '../../models/WebsparkBlocks.js'

/** @type {import('@playwright/test').Page} */
let page
let node, block
const title = 'Web Directory'
const nodeTitle = 'Web Directory - People'

test.describe(title, { tag: ['@webspark', '@block', '@react'] }, () => {
  test.describe.configure({ mode: 'serial' })

  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage()
    node = new BasicPage(page, nodeTitle)
    block = new WebDirectory(page, title)
  })

  test.afterAll(async () => {
    await page.close()
  })

  test('create', async () => {
    await node.add()
    await node.goToLayout()
    await block.add()
    await block.addDirectoryPeople()
    await block.save()
  })

  test('verify', async () => {
    await block.verifyDirectoryPeople()
  })
})
