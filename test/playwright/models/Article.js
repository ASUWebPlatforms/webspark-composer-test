// TODO: Refactor this to be more like the BasicPage.js
import { expect } from '@playwright/test'
import { faker } from '@faker-js/faker/locale/en'
import { Node } from './Node'
import drupal from '../helpers/Drupal'

class Article extends Node {
  /**
   * Article model for Playwright tests.
   * @param {import('playwright').Page} page
   * @param {string} name
   */
  constructor (page, name) {
    super(page, name)
    this.title = `Playwright ${this.name}`;
    this.author = faker.person.fullName();
    this.body = faker.lorem.sentence();
    this.byline = faker.lorem.sentence();

    this.inputHeroSize = page.getByLabel('Hero Size')
    this.inputAuthor = page.getByRole('textbox', { name: 'Article author' })
    this.inputByline = page.getByLabel('Rich Text Editor').getByRole('textbox').nth(0)
    this.inputBody = page.getByLabel('Rich Text Editor').getByRole('textbox').nth(1)

    this.elHero = page.locator(".uds-story-hero");
    this.elImage = page.getByRole("img", { name: "sample image" });
    this.elDate = page.getByRole("time");
    this.elLead = page.getByText(this.byline);
    this.elAuthor = page.getByText(this.author, { exact: true });
    this.elBody = page.getByText(this.body);
    this.elHeading = page.getByRole("heading", { name: this.title });
  }

  /**
   * Add a new article node.
   * @returns {Promise<void>}
   */
  async add () {
    await this.page.goto("/node/add/article");
    await this.inputTitle.fill(this.title);
    await this.inputByline.fill(this.byline);
    await this.inputBody.fill(this.body);
    await this.save();

    await expect(this.status).toHaveClass(/alert-success/);
    await expect(this.elHeading).toBeVisible();
    await expect(this.elBody).toBeVisible();

    await this.setNodeProperties();
  }

  /**
   * Add content to the article node.
   * @returns {Promise<void>}
   */
  async addContent () {
    await drupal.addMediaField(this.page);
    await this.inputHeroSize.selectOption({ label: "Large" });
    await this.inputAuthor.fill(this.author);
  }

  async verify () {
    await expect(this.elHero).toHaveClass(/uds-story-hero-lg/);
    await expect(this.elImage).toBeVisible();
    await expect(this.elLead).toBeVisible();
    await expect(this.elAuthor).toBeVisible();
    await expect(this.elDate).toBeVisible();
  }
}

export { Article }
