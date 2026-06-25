import { test, expect } from '@playwright/test'
import { BasicPage } from '../../models/BasicPage.js'
import { CardArrangement } from '../../models/WebsparkBlocks.js'

/** @type {import('@playwright/test').Page} */
let page
let node, block
const title = 'Card Arrangement'
const nodeTitle = 'Card Arrangement - Icon'

test.describe(title, { tag: ['@webspark', '@block', '@react'] }, () => {
  test.describe.configure({ mode: 'serial' })

  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage()
    node = new BasicPage(page, nodeTitle)
    block = new CardArrangement(page, title)
  })

  test.afterAll(async () => {
    await page.close()
  })

  test('create', async () => {
    await node.add()
    await node.goToLayout()
    await block.add()
    await block.addContent()
    await block.addCardGroupIcon()
    await block.save()
  })

  test('verify', async () => {
    await block.verifyCardGroupIcon()
  })
})
