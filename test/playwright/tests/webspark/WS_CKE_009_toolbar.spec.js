import { test, expect } from '@playwright/test';
import { BasicPage } from '../../models/BasicPage.js';
import { Toolbar } from '../../models/CKEPlugins.js';

/** @type {import('@playwright/test').Page} */
let page;
let node, plugin;
const title = 'Toolbar';

test.describe(title, { tag: ['@webspark', '@ckeditor'] }, () => {
  test.describe.configure({ mode: 'serial' });

  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage();
    node = new BasicPage(page, title);
    plugin = new Toolbar(page, title);
  });

  test.afterAll(async () => {
    await page.close();
  });

  // Need updated test for new Animated Blockquote logic
  test.fixme('verify', async () => {
    await node.add();
    await node.edit();
    await plugin.verify();
    await node.save();
  });
});
