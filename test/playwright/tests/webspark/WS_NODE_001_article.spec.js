import { test, expect } from '@playwright/test'
import { Article } from '../../models/Article.js'

/** @type {import('@playwright/test').Page} */
let page
let node
const title = 'Article'

test.describe(title, { tag: ['@webspark', '@node'] }, () => {
  test.describe.configure({ mode: 'serial' })

  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage()
    node = new Article(page, title)
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
