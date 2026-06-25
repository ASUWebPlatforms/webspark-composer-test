import { test, expect } from '@playwright/test';
import { BasicPage } from '../../models/BasicPage.js';
import { BlockquoteAnimated } from '../../models/CKEPlugins.js';

/** @type {import('@playwright/test').Page} */
let page;
let node, plugin;
const title = 'Blockquote Animated';

test.describe(title, { tag: ['@webspark', '@ckeditor'] }, () => {
  test.describe.configure({ mode: 'serial' });

  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage();
    node = new BasicPage(page, title);
    plugin = new BlockquoteAnimated(page, title);
  });

  test.afterAll(async () => {
    await page.close();
  });

  // Need updated test for new Animated Blockquote logic
  test.fixme('create', async () => {
    await node.add();
    await node.edit();
    await plugin.add();
    await plugin.addContent();
    await node.save();
  });

  test('verify', async () => {
    await plugin.verify();
  });
});
