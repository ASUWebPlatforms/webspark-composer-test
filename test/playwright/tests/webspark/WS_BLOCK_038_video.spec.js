import { test, expect } from '@playwright/test';
import { BasicPage } from '../../models/BasicPage.js';
import { Video } from '../../models/WebsparkBlocks.js';

/** @type {import('@playwright/test').Page} */
let page;
let node, block;
const title = 'Video';

test.describe(title, { tag: ['@webspark', '@block'] }, () => {
  test.describe.configure({ mode: 'serial' });

  test.beforeAll(async ({ browser }) => {
    page = await browser.newPage();
    node = new BasicPage(page, title);
    block = new Video(page, title);
  });

  test.afterAll(async () => {
    await page.close();
  });

  // YouTube updated their embed markup, so now we target the cover photo
  // But need to update the test to locate the video itself
  test.fixme('create', async () => {
    await node.add();
    await node.goToLayout();
    await block.add();
    await block.addContent();
    await block.save();
  });

  test('verify', async () => {
    await block.verify();
  });
});
