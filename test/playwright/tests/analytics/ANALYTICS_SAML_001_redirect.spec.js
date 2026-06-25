import { test, expect } from '@playwright/test'

/** @type {import('@playwright/test').Page} */
let page

test.describe('Analytics login redirect', { tag: ['@analytics'] }, () => {
  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage()
  })

  test.afterAll(async () => {
    await page.close()
  })

  test('verify', async () => {
    const el = page.getByText('Not a student or faculty/staff?')

    await page.goto('/')
    await expect(el).toBeVisible()
  })
})
