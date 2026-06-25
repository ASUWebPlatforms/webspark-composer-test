import { test, expect } from '@playwright/test'
import { DegreeListing } from '../../models/DegreeListing.js'

/** @type {import('@playwright/test').Page} */
let page
let node
const title = 'Degree Listing'

test.describe(title, { tag: ['@webspark', '@node', '@react'] }, () => {
  test.describe.configure({ mode: 'serial' })

  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage()
    node = new DegreeListing(page, title)
  })

  test.afterAll(async () => {
    await page.close()
  })

  test('create', async () => {
    await node.add()
    await node.edit()
    await node.addContent()
    await node.save()
  })

  test('verify', async () => {
    await node.verify()
  })
})
