import { test, expect } from '@playwright/test'
import { VideoHero } from '../../models/WebsparkBlocks.js'

const MOBILE_URL = process.env.MOBILE_URL
if (!MOBILE_URL) {
  throw new Error('MOBILE_URL must be set in \'.ddev/.env\'.')
}

/** @type {import('@playwright/test').Page} */
let page
let block
const title = 'Video hero'

test.describe(title, { tag: ['@webspark', '@mobile'] }, () => {
  test.describe.configure({ mode: 'serial' })
  test.use({ baseURL: MOBILE_URL })

  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage()
    block = new VideoHero(page, title)
  })

  test.afterAll(async () => {
    await page.close()
  })

  test('verify', async () => {
    await block.verifyMobile()
  })
})
