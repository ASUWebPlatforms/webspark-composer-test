import { test, expect } from '@playwright/test';
import { Header } from '../../models/AsuBrand.js';

/** @type {import('@playwright/test').Page} */
let page;
let component;
const title = 'Header';

test.describe(title, { tag: ['@webspark', '@brand'] }, () => {
  test.describe.configure({ mode: 'serial' });

  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage();
    component = new Header(page, title);
  });

  test.afterAll(async () => {
    await page.close();
  });

  test('create default', async () => {
    await component.add();
    await component.save();
  });

  test('verify default', async () => {
    await component.verifyDefault();
  });

  test('create partner', async () => {
    await component.addPartner();
    await component.save();
  });

  test('verify partner', async () => {
    await component.verifyPartner();
  });

  test('verify other', async () => {
    await component.verifyParent();
    await component.verifyMenu();
  });
});
