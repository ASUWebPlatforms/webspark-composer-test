import { test, expect } from '@playwright/test'
import { BasicPage } from '../../models/BasicPage.js'
import { DegreeRfi } from '../../models/DegreeBlocks.js'

/** @type {import('@playwright/test').Page} */
let page
let node, block
const title = 'RFI form component'
const nodeTitle = 'Degree RFI'

test.describe(title, { tag: ['@webspark', '@node', '@react'] }, () => {
  test.describe.configure({ mode: 'serial' })

  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage()
    node = new BasicPage(page, nodeTitle)
    block = new DegreeRfi(page, title)
  })

  test.afterAll(async () => {
    await page.close()
  })

  test('create', async () => {
    await node.add()
    await node.goToLayout()
    await block.add()
    await block.addContent()
    await block.save()
  })

  test('verify', async () => {
    await block.verify()
  })
})
