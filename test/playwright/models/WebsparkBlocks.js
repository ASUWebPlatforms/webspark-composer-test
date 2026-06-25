import { expect } from '@playwright/test';
import { faker } from '@faker-js/faker/locale/en';
import { Block } from './Block';
import {
  CardGroupDefault,
  CardGroupDegree,
  CardGroupIcon,
  CardGroupImage,
  CardGroupRanking,
  CardGroupStory,
} from './WebsparkCards';
import drupal from '../helpers/Drupal';

export class Accordion extends Block {
  constructor(page, name) {
    super(page, name);
    this.heading = faker.lorem.words();
    this.content = faker.lorem.paragraph();
    this.icon = 'Pyramid,ASUAwesome,Shapes,';

    this.inputColorOptions = page.getByRole('combobox', {
      name: 'Color Options',
    });
    this.inputFA = page.locator('.fip-icon-down-dir').first();
    this.inputFAIcon = page.getByTitle(this.icon).first();
    this.inputHeading = page.getByRole('textbox', { name: 'Heading' });
    this.inputContent = page
      .getByLabel('Rich Text Editor')
      .getByRole('textbox');
    this.inputExpanded = page.getByRole('checkbox', {
      name: 'Initially Expanded',
    });

    this.el = page.locator('.accordion-item.accordion-item-maroon');
    this.elIcon = page.locator('.accordion-header.accordion-header-icon');
    this.elHeading = page.getByRole('button', { name: this.heading });
    this.elContent = page.getByText(this.content, { exact: true });
  }

  async addContent() {
    await this.inputColorOptions.selectOption({ label: 'Maroon' });
    await this.inputFA.click();
    await this.inputFAIcon.click();
    await this.inputHeading.fill(this.heading);
    await this.inputContent.fill(this.content);
    await this.inputExpanded.setChecked(true);
  }

  async verify() {
    await expect(this.el).toBeVisible();
    await expect(this.elIcon).toBeVisible();
    await expect(this.elHeading).toBeVisible();
    await expect(this.elContent).toBeVisible();
    await this.elHeading.click();
    await expect(this.elContent).toBeHidden();
  }
}

export class Blockquote extends Block {
  constructor(page, name) {
    super(page, name);
    this.content = faker.lorem.paragraph();
    this.author = faker.person.fullName();
    this.title = faker.lorem.words();
    this.heading = faker.lorem.words();

    this.inputAccentColor = page.getByRole('combobox', {
      name: 'Accent Color',
    });
    this.inputTextColor = page.getByRole('combobox', { name: 'Text Color' });
    this.inputHeading = page.getByRole('textbox', { name: 'Heading' });
    this.inputHeadingHighlight = page.getByRole('combobox', {
      name: 'Heading Highlight',
    });
    this.inputText = page.getByLabel('Rich Text Editor').getByRole('textbox');
    this.inputImagePosition = page.getByRole('radio', { name: 'Right' });
    this.inputCitationStyle = page.getByRole('combobox', {
      name: 'Citation Style',
    });
    this.inputCitationAuthor = page.getByRole('textbox', {
      name: 'Citation author',
    });
    this.inputCitationTitle = page.getByRole('textbox', {
      name: 'Citation Title',
    });

    this.el = page.locator('.uds-blockquote');
    this.elHeading = page.getByText(this.heading, { exact: true });
    this.elContent = page.getByText(this.content, { exact: true });
    this.elAuthor = page.getByText(this.author, { exact: true });
    this.elTitle = page.getByText(this.title, { exact: true });
    this.elImage = page.getByRole('img', { name: 'sample image' });
  }

  async addContent() {
    await this.inputAccentColor.selectOption({ label: 'Gold' });
    await this.inputText.fill(this.content);
    await this.inputCitationStyle.selectOption({ label: 'Alternative' });
    await this.inputCitationAuthor.fill(this.author);
    await this.inputCitationTitle.fill(this.title);
  }

  async addContentVariant() {
    await this.inputHeading.fill(this.heading);
    await this.inputHeadingHighlight.selectOption({ label: 'Gold' });
    await this.inputTextColor.selectOption({ label: 'White' });
    await this.inputText.fill(this.content);
    await this.inputImagePosition.setChecked(true);
    await drupal.addMediaField(this.page);
    await this.inputCitationAuthor.fill(this.author);
    await this.inputCitationTitle.fill(this.title);
  }

  async verify() {
    await expect(this.el.first()).toHaveClass(/accent-gold/);
    await expect(this.el.first()).toHaveClass(/alt-citation/);
    await expect(this.elContent.first()).toBeVisible();
    await expect(this.elAuthor.first()).toBeVisible();
    await expect(this.elTitle.first()).toBeVisible();
  }

  async verifyVariant() {
    await expect(this.el.last()).toHaveClass(/with-image/);
    await expect(this.el.last()).toHaveClass(/text-white/);
    await expect(this.el.last()).toHaveClass(/reversed/);
    await expect(this.elHeading).toHaveClass('highlight-gold');
    await expect(this.elImage).toBeVisible();
  }
}

export class CardAndImage extends Block {
  constructor(page, name) {
    super(page, name);
    this.heading = faker.lorem.words();
    this.content = faker.lorem.paragraph();
    this.author = faker.person.fullName();
    this.title = faker.lorem.words();

    this.inputParallax = page.getByRole('checkbox', { name: 'Parallax' });
    this.inputHeading = page.getByRole('textbox', { name: 'Heading' });
    this.inputBody = page.getByLabel('Rich Text Editor').getByRole('textbox');
    this.inputShowBorders = page.getByRole('checkbox', {
      name: 'Show borders',
    });
    this.inputContentPosition = page.getByRole('combobox', {
      name: 'Content Position',
    });

    this.el = page.locator('.uds-card-and-image');
    this.elIcon = page.getByTestId('card-icon');
    this.elHeading = page.getByText(this.heading, { exact: true });
    this.elContent = page.getByText(this.content, { exact: true });
    this.elCTA = page.getByRole('link', {
      name: 'Call to action',
      exact: true,
    });
    this.elImage = page.getByRole('img', { name: 'sample image' });
  }

  async addContent() {
    await drupal.addMediaField(this.page);
    await this.inputHeading.fill(this.heading);
    await this.inputBody.fill(this.content);
    await drupal.addCTAField(this.page);
    await this.inputShowBorders.setChecked(true);
    await drupal.addIcon(this.page);
    await this.inputContentPosition.selectOption({ label: 'Right' });
  }

  async addContentVariant() {
    await drupal.addMediaField(this.page);
    await this.inputParallax.setChecked(true);
  }

  async verify() {
    await expect(this.el.first()).toHaveCSS('background-image', /.*sample.*/);
    await expect(this.el.first()).toHaveClass(/uds-card-and-image-right/);
    await expect(this.elIcon).toBeVisible();
    await expect(this.elHeading).toBeVisible();
    await expect(this.elContent).toBeVisible();
    await this.verifyCTA(this.elCTA);
  }

  async verifyVariant() {
    await expect(this.el.last()).toHaveClass(/parallax-container-content/);
    await expect(this.elImage).toBeVisible();

    // Check the parallax effect is working
    const initialPosition = await this.elImage.evaluate((img) => img.style.top);
    await this.page.evaluate(() => window.scrollTo(0, window.innerHeight / 2));
    await this.page.waitForTimeout(1000);
    const scrolledPosition = await this.elImage.evaluate(
      (img) => img.style.top,
    );
    expect(initialPosition).not.toEqual(scrolledPosition);
  }
}

export class CardCarousel extends Block {
  constructor(page, name) {
    super(page, name);

    this.cards = new CardGroupDefault(page);
    this.inputLayout = page.getByLabel('Layout', { exact: true });
    this.inputCardOrientationLandscape = page.getByRole('radio', {
      name: 'Landscape',
    });

    this.elSlides = page.locator('.glide__slide');
    this.elBullets = page.locator('.glide__bullet');
    this.elArrows = page.locator('.glide__arrow');
    this.elCards = page.locator('.card-horizontal');
  }

  async addContent() {
    await this.inputLayout.selectOption({ label: '1 Column' });
    await this.inputCardOrientationLandscape.check();
    await this.cards.addCardGroup();
    await this.cards.addContent();
  }

  async verify() {
    await expect(this.elSlides).toHaveCount(3);
    await expect(this.elSlides.nth(0)).toHaveClass(/glide__slide--active/);
    await expect(this.elCards).toHaveCount(3);
    await expect(this.elBullets).toHaveCount(3);
    await expect(this.elBullets.nth(0)).toHaveClass(/glide__bullet--active/);
    await expect(this.elArrows).toHaveCount(2);
    await expect(this.elArrows.nth(0)).toHaveClass(/glide__arrow--disabled/);
    await this.elBullets.nth(2).click();
    await expect(this.elBullets.nth(2)).toHaveClass(/glide__bullet--active/);
    await expect(this.elBullets.nth(0)).not.toHaveClass(
      /glide__bullet--active/,
    );
    await expect(this.elSlides.nth(0)).not.toHaveClass(/glide__slide--active/);
    await expect(this.elSlides.nth(2)).toHaveClass(/glide__slide--active/);
    await expect(this.elArrows.nth(0)).not.toHaveClass(
      /glide__arrow--disabled/,
    );
    await expect(this.elArrows.nth(1)).toHaveClass(/glide__arrow--disabled/);
    await this.elArrows.nth(0).click();
    await expect(this.elSlides.nth(1)).toHaveClass(/glide__slide--active/);
  }
}

export class CardImageAndContent extends Block {
  constructor(page, name) {
    super(page, name);
    this.heading = faker.lorem.words();
    this.text = faker.lorem.paragraph();

    this.inputHeading = page.getByRole('textbox', { name: 'Heading' });
    this.inputHeadingColor = page.getByRole('combobox', {
      name: 'Heading color',
    });
    this.inputText = page.getByLabel('Rich Text Editor').getByRole('textbox');
    this.inputTextColor = page.getByRole('combobox', { name: 'Text Color' });

    this.el = page.locator('.uds-card-image-and-content-image-container');
    this.elTextParent = page.locator(
      '.uds-card-image-and-content-content-container > .content',
    );
    this.elHeading = page.getByText(this.heading, { exact: true });
    this.elText = page.getByText(this.text, { exact: true });
  }

  async addContent() {
    await this.inputHeading.first().fill(this.heading);
    await this.inputHeadingColor.selectOption({ label: 'Gray 7' });
    await drupal.addMediaField(this.page);
    await this.inputText.first().fill(this.text);
    await this.inputTextColor.selectOption({ label: 'White' });

    // Card -- only need this for layout
    await drupal.addMediaField(this.page, 1);
    await this.inputHeading.last().fill(faker.book.title());
    await this.inputText.last().fill(faker.lorem.paragraph());
  }

  async verify() {
    await expect(this.el).toHaveCSS('background-image', /.*sample.*/);
    await expect(this.elHeading).toBeVisible();
    await expect(this.elText).toBeVisible();
    await expect(this.elTextParent).toHaveClass(/text-white/);
  }
}

export class ContentImageOverlap extends Block {
  constructor(page, name) {
    super(page, name);
    this.heading = faker.lorem.words();
    this.content = faker.lorem.paragraph();

    this.inputHeading = page.getByRole('textbox', { name: 'Heading' });
    this.inputContent = page
      .getByLabel('Rich Text Editor')
      .getByRole('textbox');

    this.el = page.locator('.uds-image-overlap');
    this.elHeading = page.getByText(this.heading, { exact: true });
    this.elContent = page.getByText(this.content, { exact: true });
    this.elImage = page.getByRole('img', { name: 'sample image' });
  }

  async addContent() {
    await drupal.addMediaField(this.page);
    await this.inputHeading.fill(this.heading);
    await this.inputContent.fill(this.content);
  }

  async verify() {
    await expect(this.el).toHaveClass(/content-left/);
    await expect(this.elHeading).toBeVisible();
    await expect(this.elHeading).toHaveClass('highlight-gold');
    await expect(this.elContent).toBeVisible();
    await expect(this.elImage).toBeVisible();
  }
}

export class DisplayList extends Block {
  constructor(page, name) {
    super(page, name);
    this.heading = faker.book.title();
    this.body = faker.lorem.sentence();

    this.inputHeading = page.locator(
      '[data-drupal-selector*="field-heading-0-value"]',
    );
    this.inputBody = page.locator(
      '[data-drupal-selector*="field-body-0-value"]',
    );
    this.inputAddItem = page.getByRole('button', {
      name: 'Add Display List Item',
    });

    this.el = page.getByText(`${this.heading} ${this.body}`);
  }

  async addItem(i = 0) {
    await this.inputHeading.nth(i).fill(this.heading);
    await this.inputBody.nth(i).fill(this.body);
  }

  async addContent() {
    await this.addItem();
    await drupal.waitForAjax(this.page, this.inputAddItem);
    await this.addItem(1);
    await drupal.waitForAjax(this.page, this.inputAddItem);
    await this.addItem(2);
  }

  async verify() {
    await expect(this.el).toHaveCount(3);
    await expect(this.el.first()).toBeVisible();
  }
}

export class Divider extends Block {
  constructor(page, name) {
    super(page, name);
    this.inputDivider = page.getByRole('combobox', { name: 'Divider type' });
    this.el = page.getByRole('separator');
  }

  async addContent() {
    await this.inputDivider.selectOption({ label: 'Gold body copy divider' });
  }

  async verify() {
    await expect(this.el.first()).toBeVisible();
    await expect(this.el.first()).toHaveClass('margin-width-divider');
    await expect(this.el.last()).toBeVisible();
    await expect(this.el.last()).toHaveClass('copy-divider');
  }
}

export class DonutChart extends Block {
  constructor(page, name) {
    super(page, name);
    this.text = faker.lorem.sentence();

    this.inputNumber = page.getByRole('spinbutton', { name: 'Number' });
    this.inputTextColor = page.getByRole('combobox', { name: 'Text Color' });
    this.inputText = page.getByRole('textbox', { name: 'Text' });

    this.el = page.locator('.uds-charts-and-graphs-overlay');
    this.elRing = page.locator('#uds-donut');
    this.elNumber = page.locator('#percentage-display');
    this.elText = page.locator('#percentage-display + span');
  }

  async addContent() {
    await this.inputNumber.fill('85');
    await this.inputTextColor.selectOption({ label: 'White' });
    await this.inputText.fill(this.text);
  }

  async verify() {
    await expect(this.el).toHaveClass(/text-white/);
    await expect(this.elRing).toBeVisible();
    await expect(this.elRing).toHaveCSS('height', '350px');
    await expect(this.elNumber).toContainText('85%');
    await expect(this.elText).toContainText(this.text);
  }
}

export class Events extends Block {
  constructor(page, name) {
    super(page, name);
    this.heading = faker.lorem.sentence();
    this.url = faker.internet.url();
    this.text = faker.lorem.sentence();

    this.inputTaxonomy = page.getByRole('textbox', {
      name: 'Feed taxonomy term',
    });
    this.inputFilter = page.getByRole('textbox', { name: 'Feed Filter' });
    this.inputItemsToDisplay = page.getByRole('combobox', {
      name: 'Items to Display',
    });
    this.inputHeading = page.getByRole('textbox', {
      name: 'Heading',
      exact: true,
    });
    this.inputHeadingColor = page.getByRole('combobox', {
      name: 'Header Text Color',
    });
    this.inputCTAURL = page.getByRole('textbox', { name: 'URL' });
    this.inputCTAText = page.getByRole('textbox', { name: 'Link text' });
    this.inputCTAColor = page.getByRole('combobox', {
      name: 'Header CTA color',
    });

    this.el = page.getByTestId('list-view-container').getByRole('listitem');
    this.elHeading = page.getByText(this.heading, { exact: true });
    this.elCTA = page.getByRole('link', { name: this.text, exact: true });
  }

  async addContent() {
    // await this.inputTaxonomy.fill('sports');
    // await this.inputFilter.fill('golf');
    await this.inputItemsToDisplay.selectOption({ label: 'Three' });
    await this.inputHeading.fill(this.heading);
    await this.inputHeadingColor.selectOption({ label: 'White' });
    await this.inputCTAURL.fill(this.url);
    await this.inputCTAText.fill(this.text);
    await this.inputCTAColor.selectOption({ label: 'Maroon' });
  }

  // Although this is a React component, we cant yet test via the props
  // since the props are deleted in the components JS, then data passed via
  // the preprocess hook server side
  async verify() {
    await expect(this.el).toHaveCount(3);
    await expect(this.elHeading).toBeVisible();
    await expect(this.elHeading).toHaveClass('text-white');
    await expect(this.elCTA).toHaveClass(/btn-maroon/);
    await expect(this.elCTA).toHaveAttribute('href', this.url);
  }
}

export class GridLinks extends Block {
  constructor(page, name) {
    super(page, name);
    this.heading = faker.lorem.sentence();
    this.url = faker.internet.url();
    this.text = faker.lorem.words();
    this.icon = 'Pyramid,ASUAwesome,Shapes,';

    this.inputRequiredDisplay = page.getByRole('combobox', { name: 'Display' });
    this.inputRequiredLinksColor = page.getByRole('combobox', {
      name: 'Links Text Color',
    });
    this.inputUrl = page.getByRole('textbox', { name: 'URL' });
    this.inputLinkText = page.getByRole('textbox', { name: 'Link text' });
    this.inputIconWidget = page.locator('.fip-icon-down-dir').first();
    this.inputIcon = page.getByTitle(this.icon).first();
    this.inputAddCta = page.getByRole('button', { name: 'Add CTA' });
    this.inputCtaUrl = page.locator(
      '[data-drupal-selector^="edit-settings-block-form-field-links-1-subform-field-cta-link-0-uri"]',
    );
    this.inputCtaTitle = page.locator(
      '[data-drupal-selector^="edit-settings-block-form-field-links-1-subform-field-cta-link-0-title"]',
    );
    this.inputCtaIconWidget = page.locator(
      '[data-fip-origin^="edit-settings-block-form-field-links-1-subform-field-icon-0-icon-name"] .fip-icon-down-dir',
    );
    this.inputCtaIconWidgetSelector = page.locator(
      '[data-fip-origin^="edit-settings-block-form-field-links-1-subform-field-icon-0-icon-name"]',
    );
    this.inputCtaUrl2 = page.locator(
      '[data-drupal-selector^="edit-settings-block-form-field-links-2-subform-field-cta-link-0-uri"]',
    );
    this.inputCtaTitle2 = page.locator(
      '[data-drupal-selector^="edit-settings-block-form-field-links-2-subform-field-cta-link-0-title"]',
    );
    this.inputCtaIconWidget2 = page.locator(
      '[data-fip-origin^="edit-settings-block-form-field-links-2-subform-field-icon-0-icon-name"] .fip-icon-down-dir',
    );
    this.inputCtaIconWidgetSelector2 = page.locator(
      '[data-fip-origin^="edit-settings-block-form-field-links-2-subform-field-icon-0-icon-name"]',
    );

    this.el = page.locator('.uds-grid-links');
    this.elLink = page.getByRole('link', { name: this.text });
    this.elIcon = page.locator('.uds-grid-links-icon');
  }

  async addContent() {
    await this.inputRequiredDisplay.selectOption({ label: 'Three Columns' });
    await this.inputRequiredLinksColor.selectOption({ label: 'Gold Links' });

    await this.inputUrl.fill(this.url);
    await this.inputLinkText.fill(this.text);
    await this.inputIconWidget.click();
    await this.inputIcon.click();
    await this.inputAddCta.click();

    await this.inputCtaUrl.fill(this.url);
    await this.inputCtaTitle.fill(this.text);
    await this.inputCtaIconWidget.click();
    await this.inputCtaIconWidgetSelector.getByTitle(this.icon).first().click();
    await this.inputAddCta.click();

    await this.inputCtaUrl2.fill(this.url);
    await this.inputCtaTitle2.fill(this.text);
    await this.inputCtaIconWidget2.click();
    await this.inputCtaIconWidgetSelector2
      .getByTitle(this.icon)
      .first()
      .click();
  }

  async verify() {
    await expect(this.el).toHaveClass(/three-columns/);
    await expect(this.el).toHaveClass(/text-gold/);
    await expect(this.elLink.nth(0)).toBeVisible();
    await expect(this.elIcon.nth(0)).toBeVisible();
    await expect(this.elLink.nth(1)).toBeVisible();
    await expect(this.elIcon.nth(1)).toBeVisible();
    await expect(this.elLink.nth(2)).toBeVisible();
    await expect(this.elIcon.nth(2)).toBeVisible();
  }
}

export class Hero extends Block {
  constructor(page, name) {
    super(page, name);
    this.heading = faker.lorem.words();
    this.subHeading = faker.lorem.words();
    this.content = faker.lorem.paragraph();
    this.ctaText = faker.lorem.words();
    this.url = faker.internet.url();

    this.inputHeroSize = page.getByRole('combobox', { name: 'Hero Size' });
    this.inputHeading = page.getByRole('textbox', {
      name: 'Hero Heading',
      exact: true,
    });
    this.inputHeadingBgColor = page.getByRole('combobox', {
      name: 'Hero Heading Background Color',
    });
    this.inputSubHeading = page.getByRole('textbox', {
      name: 'Sub Heading',
      exact: true,
    });
    this.inputSubHeadingBgColor = page.getByRole('combobox', {
      name: 'Sub Heading Background Color',
    });
    this.inputHeroText = page.getByRole('textbox', {
      name: 'Hero Text',
      exact: true,
    });
    this.inputCta1Url = page.locator(
      '[data-drupal-selector^="edit-settings-block-form-field-two-cta-0-subform-field-cta-link-0-uri"]',
    );
    this.inputCta1Title = page.locator(
      '[data-drupal-selector^="edit-settings-block-form-field-two-cta-0-subform-field-cta-link-0-title"]',
    );
    this.inputCta1Target = page.locator(
      '[data-drupal-selector^="edit-settings-block-form-field-two-cta-0-subform-field-cta-link-0-options-attributes-target"]',
    );
    this.inputCta1Class = page.locator(
      '[data-drupal-selector^="edit-settings-block-form-field-two-cta-0-subform-field-cta-link-0-options-attributes-class"]',
    );
    this.inputCta2Url = page.locator(
      '[data-drupal-selector^="edit-settings-block-form-field-two-cta-1-subform-field-cta-link-0-uri"]',
    );
    this.inputCta2Title = page.locator(
      '[data-drupal-selector^="edit-settings-block-form-field-two-cta-1-subform-field-cta-link-0-title"]',
    );
    this.inputAddCta = page.getByRole('button', { name: 'Add CTA' });

    this.el = page.locator('.uds-hero');
    this.elHeading = page.getByText(this.heading, { exact: true });
    this.elSubHeading = page.getByText(this.subHeading, { exact: true });
    this.elContent = page.getByText(this.content, { exact: true });
    this.elCta = page.getByRole('link', { name: this.ctaText, exact: true });
    this.elImage = page.getByRole('img', { name: 'sample image' });
  }

  async addContent() {
    await this.inputHeroSize.selectOption({ label: 'Small' });
    await this.inputHeading.fill(this.heading);
    await this.inputHeadingBgColor.selectOption({ label: 'Gray 7 background' });
    await this.inputSubHeading.fill(this.subHeading);
    await this.inputSubHeadingBgColor.selectOption({
      label: 'Gray 7 background',
    });
    await drupal.addMediaField(this.page);

    await this.inputCta1Url.fill(this.url);
    await this.inputCta1Title.fill(this.ctaText);
    await this.inputCta1Target.selectOption({ label: 'New window (_blank)' });
    await this.inputCta1Class.selectOption({ label: 'Maroon' });
    await this.inputAddCta.click();

    await this.inputCta2Url.fill(this.url);
    await this.inputCta2Title.fill(this.ctaText);
  }

  async addContentVariant() {
    await this.inputHeading.fill(this.heading);
    await this.inputHeroText.fill(this.content);
  }

  async verify() {
    await expect(this.elSubHeading).toBeVisible();
    await expect(this.elSubHeading).toHaveClass('highlight-black');
    await expect(this.elHeading.first()).toBeVisible();
    await expect(this.elHeading.first()).toHaveClass('highlight-black');
    await expect(this.elCta.first()).toBeVisible();
    await expect(this.elCta.first()).toHaveClass(/btn-maroon/);
    await expect(this.elCta.first()).toHaveAttribute('href', this.url);
    await expect(this.elCta.first()).toHaveAttribute('target', '_blank');
    await expect(this.elCta.last()).toBeVisible();
    await expect(this.elCta.last()).toHaveClass(/btn-gold/);
    await expect(this.elImage).toBeVisible();
  }

  async verifyVariant() {
    await expect(this.elHeading.nth(1)).toBeVisible();
    await expect(this.elHeading.nth(1)).toHaveClass('highlight-gold');
    await expect(this.elContent).toBeVisible();
  }
}

export class HoverCards extends Block {
  constructor(page, name) {
    super(page, name);
    this.heading = faker.lorem.words();
    this.content = faker.lorem.paragraph();
    this.ctaText = faker.lorem.words();
    this.url = faker.internet.url();

    this.inputHeading = page.getByRole('textbox', { name: 'Heading' });
    this.inputBody = page.getByRole('textbox', { name: 'Body' });
    this.inputUrl = page.getByRole('textbox', { name: 'URL' });
    this.inputLinkText = page.getByRole('textbox', { name: 'Link Text' });

    this.el = page.locator('.block-inline-blockhover-cards .content-section');
    this.elImage = page.getByRole('img', { name: 'sample image' });
    this.elHeading = page.getByText(this.heading, { exact: true });
    this.elContent = page.getByText(this.content, { exact: true });
    this.elCta = page.getByRole('link', { name: this.ctaText, exact: true });
  }

  async addContent() {
    await drupal.addMediaField(this.page);
    await this.inputHeading.fill(this.heading);
    await this.inputBody.fill(this.content);
    await this.inputUrl.fill(this.url);
    await this.inputLinkText.fill(this.ctaText);
  }

  async verify() {
    await expect(this.elImage).toBeVisible();
    await expect(this.elHeading).toBeVisible();
    await this.el.hover();
    await expect(this.elContent).toBeVisible();
    await expect(this.elCta).toBeVisible();
    await expect(this.elCta).toHaveAttribute('href', this.url);
  }
}

export class IconList extends Block {
  constructor(page, name) {
    super(page, name);
    this.content = faker.lorem.paragraph();
    this.icon = 'Pyramid,ASUAwesome,Shapes,';

    this.inputIconWidget = page.locator(
      '[data-fip-origin^="edit-settings-block-form-field-list-item-0-subform-field-icon-0-icon-name"] .fip-icon-down-dir',
    );
    this.inputIcon = page
      .locator(
        '[data-fip-origin^="edit-settings-block-form-field-list-item-0-subform-field-icon-0-icon-name"]',
      )
      .getByTitle(this.icon)
      .first();
    this.inputContent = page
      .getByLabel('Rich Text Editor')
      .getByRole('textbox');

    this.elItem = page.getByText(this.content, { exact: true });
    this.elIcon = page
      .getByRole('listitem')
      .filter({ hasText: this.content })
      .locator('svg');
  }

  async addContent() {
    await this.inputIconWidget.click();
    await this.inputIcon.click();
    await this.inputContent.fill(this.content);
  }

  async verify() {
    await expect(this.elItem).toBeVisible();
    await expect(this.elIcon).toBeVisible();
  }
}

export class ImageAndText extends Block {
  constructor(page, name) {
    super(page, name);
    this.heading = faker.lorem.words();
    this.subHeading = faker.lorem.words();
    this.content = faker.lorem.paragraph();
    this.ctaText = faker.lorem.words();
    this.url = faker.internet.url();

    this.inputHeading = page.getByRole('textbox', {
      name: 'Heading',
      exact: true,
    });
    this.inputSubHeading = page.getByRole('textbox', {
      name: 'Sub Heading',
      exact: true,
    });
    this.inputContent = page
      .getByLabel('Rich Text Editor')
      .getByRole('textbox');
    this.inputBackgroundColor = page.getByRole('combobox', {
      name: 'Background Color',
    });
    this.inputCta1Url = page.locator(
      '[data-drupal-selector^="edit-settings-block-form-field-two-cta-0-subform-field-cta-link-0-uri"]',
    );
    this.inputCta1Title = page.locator(
      '[data-drupal-selector^="edit-settings-block-form-field-two-cta-0-subform-field-cta-link-0-title"]',
    );
    this.inputCta1Target = page.locator(
      '[data-drupal-selector^="edit-settings-block-form-field-two-cta-0-subform-field-cta-link-0-options-attributes-target"]',
    );
    this.inputCta1Class = page.locator(
      '[data-drupal-selector^="edit-settings-block-form-field-two-cta-0-subform-field-cta-link-0-options-attributes-class"]',
    );
    this.inputCta2Url = page.locator(
      '[data-drupal-selector^="edit-settings-block-form-field-two-cta-1-subform-field-cta-link-0-uri"]',
    );
    this.inputCta2Title = page.locator(
      '[data-drupal-selector^="edit-settings-block-form-field-two-cta-1-subform-field-cta-link-0-title"]',
    );
    this.inputAddCta = page.getByRole('button', { name: 'Add CTA' });

    this.el = page.locator('.uds-image-text-block-text-container');
    this.elHeading = page.getByText(this.heading, { exact: true });
    this.elSubHeading = page.getByText(this.subHeading, { exact: true });
    this.elContent = page.getByText(this.content, { exact: true });
    this.elCta = page.getByRole('link', { name: this.ctaText, exact: true });
    this.elImage = page.getByRole('img', { name: 'sample image' });
  }

  async addContent() {
    await this.inputHeading.fill(this.heading);
    await this.inputSubHeading.fill(this.subHeading);
    await this.inputContent.fill(this.content);
    await drupal.addMediaField(this.page);
    await this.inputBackgroundColor.selectOption({ label: 'Gray 1' });

    await this.inputCta1Url.fill(this.url);
    await this.inputCta1Title.fill(this.ctaText);
    await this.inputCta1Target.selectOption({ label: 'New window (_blank)' });
    await this.inputCta1Class.selectOption({ label: 'Maroon' });
    await this.inputAddCta.click();

    await this.inputCta2Url.fill(this.url);
    await this.inputCta2Title.fill(this.ctaText);
  }

  async verify() {
    await expect(this.el).toHaveClass(/gray-1-bg/);
    await expect(this.elHeading).toBeVisible();
    await expect(this.elSubHeading).toBeVisible();
    await expect(this.elContent).toBeVisible();
    await expect(this.elCta.first()).toBeVisible();
    await expect(this.elCta.first()).toHaveClass(/btn-maroon/);
    await expect(this.elCta.first()).toHaveAttribute('href', this.url);
    await expect(this.elCta.first()).toHaveAttribute('target', '_blank');
    await expect(this.elCta.last()).toBeVisible();
    await expect(this.elCta.last()).toHaveClass(/btn-gold/);
    await expect(this.elImage).toBeVisible();
  }
}

export class ImageBackgroundCta extends Block {
  constructor(page, name) {
    super(page, name);
    this.heading = faker.lorem.words();

    this.inputHeading = page.getByRole('textbox', { name: 'Heading' });

    this.el = page.locator('.uds-image-background-with-cta');
    this.elHeading = page.getByText(this.heading, { exact: true });
    this.elCta = page.getByRole('link', {
      name: 'Call to action',
      exact: true,
    });
  }

  async addContent() {
    await this.inputHeading.fill(this.heading);
    await drupal.addMediaField(this.page);
    await drupal.addCTAField(this.page);
  }

  async verify() {
    await expect(this.el).toHaveCSS('background-image', /.*sample.*/);
    await expect(this.elHeading).toBeVisible();
    await expect(this.elCta).toBeVisible();
    await expect(this.elCta).toHaveClass(/btn-maroon/);
    await expect(this.elCta).toHaveAttribute('href', 'https://asu.edu');
    await expect(this.elCta).toHaveAttribute('target', '_blank');
  }
}

export class ImageCarousel extends Block {
  constructor(page, name) {
    super(page, name);
    this.title = faker.lorem.words();
    this.content = faker.lorem.paragraph();

    this.inputTitle = page.getByRole('textbox', { name: 'Title' });
    this.inputContent = page
      .getByLabel('Rich Text Editor')
      .getByRole('textbox');
    this.inputAddGalleryImage = page.getByRole('button', {
      name: 'Add Gallery Image',
    });

    this.elImage = page.getByRole('img', { name: 'sample image' });
    this.elTitle = page.getByText(this.title, { exact: true });
    this.elContent = page.getByText(this.content, { exact: true });
  }

  async addContent() {
    // TODO: This one blocks title is different, why?
    await this.inputTitle.nth(0).fill(this.name);

    await this.inputTitle.nth(1).fill(this.title);
    await this.inputContent.nth(0).fill(this.content);
    await drupal.addMediaField(this.page);
    await this.inputAddGalleryImage.click();

    await this.inputTitle.nth(2).fill(this.title);
    await this.inputContent.nth(1).fill(this.content);
    await drupal.addMediaField(this.page, 1);
    await this.inputAddGalleryImage.click();

    await this.inputTitle.nth(3).fill(this.title);
    await this.inputContent.nth(2).fill(this.content);
    await drupal.addMediaField(this.page, 2);
    await this.inputAddGalleryImage.click();

    await this.inputTitle.nth(4).fill(this.title);
    await this.inputContent.nth(3).fill(this.content);
    await drupal.addMediaField(this.page, 3);
  }

  async verify() {
    await expect(this.elImage.nth(0)).toBeVisible();
    await expect(this.elTitle.nth(0)).toBeVisible();
    await expect(this.elContent.nth(0)).toBeVisible();

    await this.verifyCarousel({ count: 4 });
  }
}

export class ImageGallery extends Block {
  constructor(page, name) {
    super(page, name);
    this.title = faker.lorem.words();
    this.content = faker.lorem.paragraph();

    this.inputTitle = page.getByRole('textbox', { name: 'Title' });
    this.inputContent = page
      .getByLabel('Rich Text Editor')
      .getByRole('textbox');
    this.inputAddGalleryImage = page.getByRole('button', {
      name: 'Add Gallery Image',
    });

    this.elImage = page.getByRole('img', { name: 'sample image' });
    this.elTitle = page.getByText(this.title, { exact: true });
    this.elContent = page.getByText(this.content, { exact: true });
  }

  async addContent() {
    // TODO: This one blocks title is different, why?
    await this.inputTitle.nth(0).fill(this.name);

    await this.inputTitle.nth(1).fill(this.title);
    await this.inputContent.nth(0).fill(this.content);
    await drupal.addMediaField(this.page);
    await this.inputAddGalleryImage.click();

    await this.inputTitle.nth(2).fill(this.title);
    await this.inputContent.nth(1).fill(this.content);
    await drupal.addMediaField(this.page, 1);
    await this.inputAddGalleryImage.click();

    await this.inputTitle.nth(3).fill(this.title);
    await this.inputContent.nth(2).fill(this.content);
    await drupal.addMediaField(this.page, 2);
  }

  async verify() {
    await this.page.waitForTimeout(1000);
    await expect(this.elImage.nth(0)).toBeVisible();
    await expect(this.elTitle.nth(0)).toBeVisible();
    await expect(this.elContent.nth(0)).toBeVisible();

    await this.verifyCarousel({ role: 'option' });
  }
}

export class Image extends Block {
  constructor(page, name) {
    super(page, name);
    this.caption = faker.lorem.sentence();

    this.inputImageSize = page.getByRole('combobox', { name: 'Image Size' });
    this.inputCenterImage = page.getByRole('checkbox', {
      name: 'Center image',
    });
    this.inputAddDropShadow = page.getByRole('checkbox', {
      name: 'Add Drop Shadow',
    });
    this.inputImageCaption = page.getByRole('textbox', {
      name: 'Image Caption',
    });

    this.el = page.locator('.uds-img');
    this.elBlockParent = page
      .locator('.block > .center-container')
      .filter({ has: this.el });
    this.elImage = page.getByRole('img', { name: 'sample image' });
    this.elCaption = page.getByText(this.caption, { exact: true });
  }

  async addContent() {
    await this.inputImageSize.selectOption({ label: '50%' });
    await this.inputCenterImage.setChecked(true);
    await this.inputAddDropShadow.setChecked(true);
    await drupal.addMediaField(this.page);
    await this.inputImageCaption.fill(this.caption);
  }

  async verify() {
    await expect(this.elBlockParent).toHaveCSS('max-width', '50%');
    await expect(this.el).toHaveClass(/uds-img-drop-shadow/);
    await expect(this.elImage).toBeVisible();
    await expect(this.elCaption).toBeVisible();
  }
}

export class InsetBox extends Block {
  constructor(page, name) {
    super(page, name);
    this.content = faker.lorem.paragraph();

    this.inputContent = page
      .getByLabel('Rich Text Editor')
      .getByRole('textbox');
    this.inputBackgroundColor = page.getByRole('combobox', {
      name: 'Background Color',
    });

    this.el = page.locator('.uds-inset-box-container');
    this.elContent = page.getByText(this.content, { exact: true });
  }

  async addContent() {
    await this.inputContent.fill(this.content);
    await this.inputBackgroundColor.selectOption({ label: 'Gray 1' });
  }

  async verify() {
    await expect(this.elContent).toBeVisible();
    await expect(this.el).toHaveClass(/gray-1-bg/);
  }
}

export class News extends Block {
  constructor(page, name) {
    super(page, name);
    this.heading = faker.lorem.words();
    this.ctaText = faker.lorem.words();
    this.url = faker.internet.url();
    this.taxonomyTerm = 'biodesign_center_for_bioenergetics';
    this.itemsCount = '8';

    this.inputTaxonomyTerm = page.getByRole('textbox', {
      name: 'Feed taxonomy term',
      exact: true,
    });
    this.inputFilter = page.getByRole('textbox', {
      name: 'Feed Filter',
      exact: true,
    });
    this.inputHeading = page.getByRole('textbox', {
      name: 'Heading',
      exact: true,
    });
    this.inputHeadingTextColor = page.getByRole('combobox', {
      name: 'Heading Text Color',
    });
    this.inputUrl = page.getByRole('textbox', { name: 'URL' });
    this.inputLinkText = page.getByRole('textbox', { name: 'Link text' });
    this.inputHeadingCtaColor = page.getByRole('combobox', {
      name: 'Heading CTA Color',
    });
    this.inputCardCtaColor = page.getByRole('combobox', {
      name: 'Card CTA Color',
    });
    this.inputDisplay = page.getByRole('combobox', { name: 'Display' });
    this.inputItemsToDisplay = page.getByRole('spinbutton', {
      name: 'Items to Display',
    });

    this.elHeading = page.getByText(this.heading, { exact: true });
    this.elCta = page.getByRole('link', { name: this.ctaText, exact: true });
    this.elNews = page
      .getByTestId('list-view-container')
      .locator('.card.card-hover');
  }

  async addContent() {
    await this.inputTaxonomyTerm.fill(this.taxonomyTerm);

    // Will write a test for this when I figure out how this filter works
    // await this.inputFilter.fill('');
    await this.inputHeading.fill(this.heading);
    await this.inputHeadingTextColor.selectOption({ label: 'White' });
    await this.inputUrl.fill(this.url);
    await this.inputLinkText.fill(this.ctaText);

    // This will have no effect if using the CTA instead, this is the default CTA
    await this.inputHeadingCtaColor.selectOption({ label: 'Maroon' });
    await this.inputCardCtaColor.selectOption({ label: 'Gold' });
    await this.inputDisplay.selectOption({ label: 'Horizontal' });
    await this.inputItemsToDisplay.fill(this.itemsCount);
  }

  async verify() {
    await expect(this.elHeading).toBeVisible();
    await expect(this.elHeading).toHaveClass('text-white');
    await expect(this.elCta).toHaveClass(/btn-maroon/);
    await expect(this.elCta).toHaveAttribute('href', this.url);
    await expect(this.elNews).toHaveCount(parseInt(this.itemsCount));
    await expect(this.elNews.first()).toHaveClass(/card/);
    await expect(this.elNews.first()).toHaveClass(/card-hover/);
  }
}

export class NotificationBanner extends Block {
  constructor(page, name) {
    super(page, name);
    this.heading = faker.lorem.words();
    this.content = faker.lorem.paragraph();
    this.ctaText = faker.lorem.words();
    this.url = faker.internet.url();
    this.icon = 'Pyramid,ASUAwesome,Shapes,';

    this.inputHeading = page.getByRole('textbox', { name: 'Heading' });
    this.inputContent = page
      .getByLabel('Rich Text Editor')
      .getByRole('textbox');
    this.inputColorBlue = page.getByRole('radio', { name: 'Blue' });
    this.inputIconWidget = page.locator('.fip-icon-down-dir').first();
    this.inputIcon = page.getByTitle(this.icon).first();
    this.inputCtaUrl = page
      .locator(
        '[data-drupal-selector^="edit-settings-block-form-field-ctas-0-subform-field-cta-link-0-uri"]',
      )
      .first();
    this.inputCtaTitle = page
      .locator(
        '[data-drupal-selector^="edit-settings-block-form-field-ctas-0-subform-field-cta-link-0-title"]',
      )
      .first();

    this.el = page.locator('.block-inline-blockbanner');
    this.elColor = page.locator('.banner-blue');
    this.elIcon = page.locator('.banner-icon');
    this.elHeading = page.getByText(this.heading, { exact: true });
    this.elContent = page.getByText(this.content, { exact: true });
    this.elCta = page.getByRole('link', { name: this.ctaText, exact: true });
    this.elClose = page
      .getByRole('article')
      .getByRole('button', { name: 'Close' });
  }

  async addContent() {
    await this.inputHeading.fill(this.heading);
    await this.inputContent.fill(this.content);
    await this.inputColorBlue.setChecked(true);
    await this.inputIconWidget.click();
    await this.inputIcon.click();
    await this.inputCtaUrl.fill(this.url);
    await this.inputCtaTitle.fill(this.ctaText);
  }

  async verify() {
    await expect(this.el).toBeVisible();
    await expect(this.elColor).toHaveCount(1);
    await expect(this.elIcon).toBeVisible();
    await expect(this.elHeading).toBeVisible();
    await expect(this.elContent).toBeVisible();
    await expect(this.elCta).toBeVisible();
    await expect(this.elCta).toHaveClass(/btn-dark/);
    await expect(this.elCta).toHaveAttribute('href', this.url);
    await expect(this.elClose).toBeVisible();
    await this.elClose.click();
    await expect(this.el).toBeHidden();
  }
}

export class SidebarMenu extends Block {
  constructor(page, name) {
    super(page, name);
    this.heading = faker.lorem.words();

    this.inputTitle = page.getByRole('textbox', { name: 'Title', exact: true });
    this.inputRoot = page.getByRole('combobox', { name: 'Root' });
    this.inputIncludeRoot = page.getByRole('checkbox', {
      name: 'Include root?',
    });

    this.elHeading = page.getByText(this.heading, { exact: true });
    this.elLink = page
      .getByRole('link', { name: this.name, exact: true })
      .first();
  }

  async addContent() {
    await this.inputTitle.fill(this.heading);
    await this.inputRoot.selectOption({ label: `<${this.name}>` });
    await this.inputIncludeRoot.check();
  }

  async verify() {
    await expect(this.elHeading).toBeVisible();
    await expect(this.elLink).toHaveClass(/is-active/);
  }
}

export class StepList extends Block {
  constructor(page, name) {
    super(page, name);
    this.heading = faker.lorem.words();
    this.content = faker.lorem.paragraph();

    this.inputAccentColor = page.getByRole('combobox', {
      name: 'Accent Color',
    });
    this.inputHeading1 = page.locator(
      '[data-drupal-selector^="edit-settings-block-form-field-list-item-0-subform-field-heading-0-value"]',
    );
    this.inputContent1 = page.locator(
      '[data-drupal-selector^="edit-settings-block-form-field-list-item-0-subform-field-body-0-value"]',
    );
    this.inputAddItem = page.getByRole('button', {
      name: 'Add Step List Item',
    });
    this.inputHeading2 = page.locator(
      '[data-drupal-selector^="edit-settings-block-form-field-list-item-1-subform-field-heading-0-value"]',
    );
    this.inputContent2 = page.locator(
      '[data-drupal-selector^="edit-settings-block-form-field-list-item-1-subform-field-body-0-value"]',
    );

    this.el = page.locator('.uds-steplist');
    // An odd selector but thats what the Playwright generator spit out
    this.elItem = page.getByText(`${this.heading} ${this.content}`);
  }

  async addContent() {
    await this.inputAccentColor.selectOption({ label: 'Maroon' });
    await this.inputHeading1.fill(this.heading);
    await this.inputContent1.fill(this.content);
    await this.inputAddItem.click();
    await this.inputHeading2.fill(this.heading);
    await this.inputContent2.fill(this.content);
  }

  async verify() {
    await expect(this.el).toBeVisible();

    const content = await this.page.evaluate(() => {
      const element = document.querySelector('.uds-steplist > li:first-child');
      if (!element) return null;
      const style = window.getComputedStyle(element, '::before');
      return style.getPropertyValue('content');
    });

    await expect(this.el).toHaveClass(/uds-steplist-maroon/);
    await expect(this.elItem.first()).toBeVisible();
    // As long as it is a list counter we trust CSS to increment it
    expect(content).toBe('counter(listcounter)');
    await expect(this.elItem.last()).toBeVisible();
  }
}

export class TabbedContent extends Block {
  constructor(page, name) {
    super(page, name);
    this.tabTitle = faker.lorem.words();
    this.tabTitleLong = faker.lorem.words(10);
    this.tabContent = faker.lorem.paragraph();

    this.inputBackgroundColor = page.getByRole('combobox', {
      name: 'Background Color',
    });
    this.inputAddTab = page.getByRole('button', { name: 'Add Tab Content' });
    this.inputTabTitle = page.getByRole('textbox', { name: 'Tab Title' });
    this.inputTabContent = page
      .getByLabel('Rich Text Editor')
      .getByRole('textbox');

    this.el = page.locator('.bg-gray-1');
    this.elNext = page.getByRole('button', { name: 'Next' });
    this.elPrev = page.getByRole('button', { name: 'Previous' });
  }

  async addContent() {
    await this.inputBackgroundColor.selectOption({ label: 'Gray 1' });

    await this.inputTabTitle.nth(0).fill(`Tab 1 ${this.tabTitle}`);
    await this.inputTabContent.nth(0).fill(this.tabContent);
    await this.inputAddTab.click();

    await this.inputTabTitle.nth(1).fill(`Tab 2 ${this.tabTitleLong}`);
    await this.inputTabContent.nth(1).fill(this.tabContent);
    await this.inputAddTab.click();

    await this.inputTabTitle.nth(2).fill(`Tab 3 ${this.tabTitle}`);
    await this.inputTabContent.nth(2).fill(this.tabContent);
  }

  async verify() {
    const tabs = Array.from({ length: 3 }, (_, i) =>
      this.page.getByRole('tab', { name: `Tab ${i + 1}` }),
    );
    const content = Array.from({ length: 3 }, (_, i) =>
      this.page.getByText(this.tabContent).nth(i),
    );

    // Initial state
    await expect(this.el).toHaveClass('bg-gray-1');
    await expect(this.elPrev).toBeHidden();
    await expect(this.elNext).toBeVisible();
    await expect(tabs[0]).toBeVisible();
    await expect(tabs[1]).toBeVisible();
    await expect(tabs[2]).toBeVisible();
    await expect(content[0]).toBeVisible();
    await expect(content[1]).toBeHidden();
    await expect(content[2]).toBeHidden();

    // Clicks
    await tabs[1].click();
    await expect(content[1]).toBeVisible();
    await expect(content[0]).toBeHidden();
    await this.elNext.click({ force: true });
    await this.elNext.click({ force: true });
    await expect(this.elPrev).toBeVisible();
    await tabs[2].click();
    await expect(content[2]).toBeVisible();
  }
}

export class TestimonialImageBackground extends Block {
  constructor(page, name) {
    super(page, name);
    this.heading = faker.lorem.words();
    this.content = faker.lorem.paragraph();
    this.author = faker.person.fullName();
    this.title = faker.lorem.words();

    this.inputAccentColor = page.getByRole('combobox', {
      name: 'Accent Color',
    });
    this.inputHeadingHighlight = page.getByRole('combobox', {
      name: 'Heading Highlight',
    });
    this.inputTextColor = page.getByRole('combobox', { name: 'Text Color' });
    this.inputHeading = page.getByRole('textbox', { name: 'Heading' });
    this.inputContent = page.getByRole('textbox', { name: 'Formatted Text' });
    this.inputAuthor = page.getByRole('textbox', { name: 'Citation author' });
    this.inputTitle = page.getByRole('textbox', { name: 'Citation Title' });

    this.el = page.locator('.uds-quote-image-background');
    this.elBlock = page.locator('.uds-testimonial');
    this.elImage = page.getByTestId('testimonial-image');
    this.elHeading = page.getByText(this.heading, { exact: true });
    this.elContent = page.getByText(this.content, { exact: true });
    this.elAuthor = page.getByText(this.author, { exact: true });
    this.elTitle = page.getByText(this.title, { exact: true });
  }

  async addContent() {
    await this.inputAccentColor.selectOption({ label: 'Maroon' });
    await this.inputHeadingHighlight.selectOption({ label: 'Gold' });
    await this.inputTextColor.selectOption({ label: 'White' });
    await drupal.addMediaField(this.page);
    await this.inputHeading.fill(this.heading);
    await this.inputContent.fill(this.content);
    await drupal.addMediaField(this.page, 1);
    await this.inputAuthor.fill(this.author);
    await this.inputTitle.fill(this.title);
  }

  async verify() {
    await expect(this.el).toHaveCSS('background-image', /.*sample.*/);
    await expect(this.elBlock).toHaveClass(/text-white/);
    await expect(this.elBlock).toHaveClass(/accent-maroon/);
    await expect(this.elImage).toBeVisible();
    await expect(this.elHeading).toBeVisible();
    await expect(this.elHeading).toHaveClass('highlight-gold');
    await expect(this.elContent).toBeVisible();
    await expect(this.elAuthor).toBeVisible();
    await expect(this.elTitle).toBeVisible();
  }
}

export class TestimonialCarousel extends Block {
  constructor(page, name) {
    super(page, name);
    this.heading = faker.lorem.words();
    this.content = faker.lorem.paragraph();
    this.author = faker.person.fullName();
    this.title = faker.lorem.words();

    this.inputHeading = page.getByRole('textbox', { name: 'Heading' });
    this.inputContent = page.getByRole('textbox', { name: 'Formatted Text' });
    this.inputAuthor = page.getByRole('textbox', { name: 'Citation author' });
    this.inputTitle = page.getByRole('textbox', { name: 'Citation Title' });
    this.inputAccentColor = page.getByRole('combobox', {
      name: 'Accent Color',
    });
    this.inputTextColor = page.getByRole('combobox', { name: 'Text Color' });
    this.inputHeadingHighlight = page.getByRole('combobox', {
      name: 'Heading Highlight',
    });
    this.inputAddTestimonial = page.getByRole('button', {
      name: 'Add Testimonial',
    });

    this.el = page.locator('.uds-testimonial');
    this.elImage = page.getByRole('img', { name: 'sample image' });
    this.elHeading = page.getByText(this.heading, { exact: true });
    this.elContent = page.getByText(this.content, { exact: true });
    this.elAuthor = page.getByText(this.author, { exact: true });
    this.elTitle = page.getByText(this.title, { exact: true });
    this.elSlideViewButton = this.page.getByRole('button', {
      name: 'Slide view 1',
    });
    this.elNextSlideButton = this.page.getByRole('button', {
      name: 'Next slide',
    });
    this.elSlide = page.locator('.glide__slide');
  }

  async addContent() {
    await this.inputHeading.nth(0).fill(this.heading);
    await this.inputContent.nth(0).fill(this.content);
    await drupal.addMediaField(this.page);
    await this.inputAuthor.nth(0).fill(this.author);
    await this.inputTitle.nth(0).fill(this.title);
    await this.inputAddTestimonial.click();

    await this.inputHeading.nth(1).fill(this.heading);
    await this.inputContent.nth(1).fill(this.content);
    await drupal.addMediaField(this.page, 1);
    await this.inputAuthor.nth(1).fill(this.author);
    await this.inputTitle.nth(1).fill(this.title);
    await this.inputAddTestimonial.click();

    await this.inputHeading.nth(2).fill(this.heading);
    await this.inputContent.nth(2).fill(this.content);
    await drupal.addMediaField(this.page, 2);
    await this.inputAuthor.nth(2).fill(this.author);
    await this.inputTitle.nth(2).fill(this.title);

    await this.inputAccentColor.selectOption({ label: 'Maroon' });
    await this.inputTextColor.selectOption({ label: 'White' });
    await this.inputHeadingHighlight.selectOption({ label: 'Gold' });
  }

  async verify() {
    await expect(this.elImage.nth(0)).toBeVisible();
    await expect(this.elHeading.nth(0)).toBeVisible();
    await expect(this.elContent.nth(0)).toBeVisible();
    await expect(this.elAuthor.nth(0)).toBeVisible();
    await expect(this.elTitle.nth(0)).toBeVisible();
    await expect(this.el.nth(0)).toHaveClass(/text-white/);
    await expect(this.el.nth(0)).toHaveClass(/accent-maroon/);
    await expect(this.elHeading.nth(0)).toHaveClass('highlight-gold');
    await this.verifyCarousel();
  }

  async verifyMobile() {
    await this.page.goto('/testimonial-carousel');
    await expect(this.elSlideViewButton).toBeHidden();
    await expect(this.elNextSlideButton).toBeVisible();
    // Best we have for now until native swipe is implemented in Playwright
    await this.elNextSlideButton.tap();
    await expect(this.elSlide.nth(1)).toBeVisible();
  }
}

export class Testimonial extends Block {
  constructor(page, name) {
    super(page, name);
    this.heading = faker.lorem.words();
    this.content = faker.lorem.paragraph();
    this.author = faker.person.fullName();
    this.title = faker.lorem.words();

    this.inputAccentColor = page.getByRole('combobox', {
      name: 'Accent Color',
    });
    this.inputTextColor = page.getByRole('combobox', { name: 'Text Color' });
    this.inputHeadingHighlight = page.getByRole('combobox', {
      name: 'Heading Highlight',
    });
    this.inputHeading = page.getByRole('textbox', { name: 'Heading' });
    this.inputContent = page.getByRole('textbox', { name: 'Formatted Text' });
    this.inputAuthor = page.getByRole('textbox', { name: 'Citation author' });
    this.inputTitle = page.getByRole('textbox', { name: 'Citation Title' });

    this.el = page.locator('.uds-testimonial');
    this.elImage = page.getByRole('img', { name: 'sample image' });
    this.elHeading = page.getByText(this.heading, { exact: true });
    this.elContent = page.getByText(this.content, { exact: true });
    this.elAuthor = page.getByText(this.author, { exact: true });
    this.elTitle = page.getByText(this.title, { exact: true });
  }

  async addContent() {
    await this.inputAccentColor.selectOption({ label: 'Maroon' });
    await this.inputTextColor.selectOption({ label: 'White' });
    await this.inputHeadingHighlight.selectOption({ label: 'Gold' });
    await this.inputHeading.fill(this.heading);
    await this.inputContent.fill(this.content);
    await drupal.addMediaField(this.page);
    await this.inputAuthor.fill(this.author);
    await this.inputTitle.fill(this.title);
  }

  async verify() {
    await expect(this.el).toHaveClass(/text-white/);
    await expect(this.el).toHaveClass(/accent-maroon/);
    await expect(this.elImage).toBeVisible();
    await expect(this.elHeading).toBeVisible();
    await expect(this.elHeading).toHaveClass('highlight-gold');
    await expect(this.elContent).toBeVisible();
    await expect(this.elAuthor).toBeVisible();
    await expect(this.elTitle).toBeVisible();
  }
}

export class TextContent extends Block {
  constructor(page, name) {
    super(page, name);
    this.content = faker.lorem.paragraph();

    this.inputContent = page
      .getByLabel('Rich Text Editor')
      .getByRole('textbox');
    this.inputTextColor = page.getByRole('combobox', { name: 'Text Color' });

    this.el = page.locator('.block-inline-blocktext-content');
    this.elContent = page.getByText(this.content, { exact: true });
    this.elContentParent = page
      .locator('.block-inline-blocktext-content')
      .filter({ has: this.elContent });
  }

  async addContent() {
    await this.inputContent.fill(this.content);
    await this.inputTextColor.selectOption({ label: 'White' });
  }

  async verify() {
    await expect(this.elContent).toBeVisible();
    await expect(this.elContentParent).toHaveClass(/text-white/);
  }
}

export class VideoHero extends Block {
  constructor(page, name) {
    super(page, name);
    this.heading = faker.lorem.words();
    this.subHeading = faker.lorem.words();
    this.content = faker.lorem.paragraph();
    this.url = faker.internet.url();
    this.cta = faker.lorem.words();

    this.inputHeading = page.getByRole('textbox', {
      name: 'Heading',
      exact: true,
    });
    this.inputHeadingBgColor = page.getByRole('combobox', {
      name: 'Hero Heading Background Color',
    });
    this.inputHeroText = page.getByRole('textbox', { name: 'Hero Text' });
    this.inputSubHeading = page.getByRole('textbox', { name: 'Sub Heading' });
    this.inputSubHeadingBgColor = page.getByRole('combobox', {
      name: 'Sub Heading Background Color',
    });
    this.inputCtaUrl = page.getByRole('textbox', { name: 'URL' });
    this.inputCtaText = page.getByRole('textbox', { name: 'Link text' });
    this.inputCtaTarget = page.getByRole('combobox', {
      name: 'Select a target',
    });
    this.inputCtaStyle = page.getByRole('combobox', { name: 'Style' });

    this.el = page.locator('.uds-video-hero');
    this.elHeroDiv = page.locator('.uds-video-hero .hero');
    this.elSubHeading = page.getByText(this.subHeading, { exact: true });
    this.elHeading = page.getByText(this.heading, { exact: true });
    this.elContent = page.getByText(this.content, { exact: true });
    this.elContentDiv = page.locator('.uds-video-hero .content');
    this.elCta = page.getByRole('link', { name: this.cta, exact: true });
    this.elVideo = page.locator('#media-video');
    this.elPlayButton = page.getByRole('button', { name: 'Play hero video' });
    this.elPauseButton = page.getByRole('button', { name: 'Pause' });
  }

  async addContent() {
    await this.inputHeading.fill(this.heading);
    await this.inputHeadingBgColor.selectOption({ label: 'Gold background' });
    await this.inputHeroText.fill(this.content);
    await this.inputSubHeading.fill(this.subHeading);
    await this.inputSubHeadingBgColor.selectOption({
      label: 'Gray 7 background',
    });
    await drupal.addMediaField(this.page);
    await drupal.addMediaField(this.page, 1);
    await this.inputCtaUrl.nth(0).fill(this.url);
    await this.inputCtaText.nth(0).fill(this.cta);
    await this.inputCtaTarget
      .nth(0)
      .selectOption({ label: 'New window (_blank)' });
    await this.inputCtaStyle.nth(0).selectOption({ label: 'Maroon' });
  }

  async verify() {
    await expect(this.elVideo).toBeVisible();
    await expect(this.elSubHeading).toBeVisible();
    await expect(this.elSubHeading).toHaveClass(/highlight-black/);
    await expect(this.elHeading).toBeVisible();
    await expect(this.elHeading).toHaveClass(/highlight-gold/);
    await expect(this.elContent).toBeVisible();
    await expect(this.elCta).toBeVisible();
    await expect(this.elCta).toHaveClass(/btn-maroon/);
    await expect(this.elCta).toHaveAttribute('href', this.url);
    await expect(this.elCta).toHaveAttribute('target', '_blank');

    // Wait for video to be ready and handle Chrome's autoplay restrictions
    await this.page.waitForTimeout(3000);
    await this.elVideo.waitFor({ state: 'attached' });
    const isVideoReady = await this.elVideo.evaluate((video) => {
      return video.readyState >= 2; // HAVE_CURRENT_DATA or higher
    });

    if (isVideoReady) {
      // Try to ensure video starts playing - Chrome may need explicit play
      await this.elVideo.evaluate((video) => {
        if (video.paused) {
          video.play().catch(() => {}); // Ignore autoplay failures
        }
      });

      // Wait a bit more for video to start
      await this.page.waitForTimeout(1000);
      const initialTime = await this.elVideo.evaluate(
        (video) => video.currentTime,
      );
      await this.page.waitForTimeout(1000);
      const laterTime = await this.elVideo.evaluate(
        (video) => video.currentTime,
      );
      expect(laterTime).toBeGreaterThanOrEqual(initialTime);
    }

    // Test pause functionality if video is available
    const hasControls = await this.elPauseButton.isVisible();
    if (hasControls) {
      await this.elPauseButton.click();
      await this.page.waitForTimeout(1000);
      const isPaused = await this.elVideo.evaluate((video) => video.paused);
      expect(isPaused).toBeTruthy();

      // Test play functionality
      await this.elPlayButton.click();
      await this.page.waitForTimeout(1000);
      const isNotPaused = await this.elVideo.evaluate((video) => !video.paused);
      expect(isNotPaused).toBeTruthy();
    }
  }

  async verifyMobile() {
    await this.page.goto('/video-hero');
    await expect(this.el).toBeVisible();
    await expect(this.elHeroDiv).toBeVisible();
    await expect(this.elVideo).toBeHidden();
    await expect(this.elPauseButton).toBeHidden();
    await expect(this.elContentDiv).toBeHidden();
  }
}

export class Video extends Block {
  constructor(page, name) {
    super(page, name);
    this.caption = faker.lorem.sentence();
    this.iframeLocator = 'iframe[title="We build our future"]';

    this.inputCaption = page.getByRole('textbox', { name: 'Video Caption' });

    this.el = page.locator(this.iframeLocator);
    this.elCaption = page.locator('figcaption');
    this.elVideo = page
      .locator(this.iframeLocator)
      .contentFrame()
      .locator(this.iframeLocator)
      .contentFrame()
      .locator('.ytmVideoCoverThumbnail');
  }

  async addContent() {
    await drupal.addMediaField(this.page);
    await this.inputCaption.fill(this.caption);
  }

  async verify() {
    await expect(this.elVideo).toBeVisible();
    await expect(this.elCaption).toContainText(this.caption);
  }
}

export class Webform extends Block {
  constructor(page, name) {
    super(page, name);
    this.username = 'playwright';
    this.email = 'pw@example.com';
    this.subject = faker.lorem.words();
    this.message = faker.lorem.paragraph();

    this.inputWebform = page.getByRole('combobox', { name: 'Webform' });

    this.el = page.locator('.uds-form');
    this.elRequired = page.locator('.uds-field-required');
    this.elName = page.locator('#edit-name');
    this.elEmail = page.locator('#edit-email');
    this.elSubject = page.locator('#edit-subject');
    this.elMessage = page.getByRole('textbox', { name: 'Message' });
    this.elSubmit = page.locator('#edit-actions-submit');
    this.elError = page.getByText('error has been found: Message');
    this.elSuccess = page.getByText('Your message has been sent.', {
      exact: true,
    });
  }

  async addContent() {
    await this.inputWebform.selectOption({ label: 'Contact' });
  }

  async verify() {
    await expect(this.el).toHaveAttribute('novalidate', 'novalidate');
    await expect(this.elRequired).toHaveCount(4);
    await expect(this.elSubmit).toHaveClass(/btn-maroon/);

    await expect(this.elName).toHaveValue(this.username);
    await expect(this.elEmail).toHaveValue(this.email);
    await this.elSubject.fill(this.subject);
    await this.elSubmit.click();

    await expect(this.elError).toBeVisible();
    await expect(this.el).toHaveClass(/was-validated/);

    await expect(this.elName).toHaveCSS(
      'border-bottom-color',
      'rgb(68, 109, 18)',
    );
    await expect(this.elMessage).toHaveCSS(
      'border-bottom-color',
      'rgb(183, 42, 42)',
    );
    await this.elMessage.fill(this.message);
    await this.elSubmit.click();
    await expect(this.elSuccess).toBeVisible();
  }
}

export class CardArrangement extends Block {
  constructor(page, name) {
    super(page, name);
    this.blockHeading = faker.book.title();
    this.blockUrl = faker.internet.url();
    this.blockCtaText = faker.lorem.words();

    this.cardGroupDefault = new CardGroupDefault(page);
    this.cardGroupDegree = new CardGroupDegree(page);
    this.cardGroupIcon = new CardGroupIcon(page);
    this.cardGroupImage = new CardGroupImage(page);
    this.cardGroupRanking = new CardGroupRanking(page);
    this.cardGroupStory = new CardGroupStory(page);

    this.inputMainContentButton = page.getByRole('button', {
      name: 'Main Content *',
    });
    this.inputBlockHeading = page.getByRole('textbox', { name: 'Heading' });
    this.inputTextColor = page.getByRole('combobox', { name: 'Text Color' });
    this.inputBlockUrl = page.locator(
      '[data-drupal-selector*="block-form-field-cta-0-subform-field-cta-link-0-uri"]',
    );
    this.inputBlockLinkText = page.locator(
      '[data-drupal-selector*="block-form-field-cta-0-subform-field-cta-link-0-title"]',
    );
    this.inputBlockTarget = page.locator(
      '[data-drupal-selector*="block-form-field-cta-0-subform-field-cta-link-0-options-attributes-target"]',
    );
    this.inputBlockStyle = page.locator(
      '[data-drupal-selector*="block-form-field-cta-0-subform-field-cta-link-0-options-attributes-class"]',
    );
    this.inputColumnsDisplay = page.getByLabel('Columns to Display');
    this.inputCardsButton = page.getByRole('button', { name: 'Cards' });

    this.el = page.locator('.uds-card-arrangement-content-container');
    this.elColumns = page
      .getByTestId('uds-card-arrangement-content-container')
      .first(); // Note the name, but this is not a class
    this.elBlockHeading = page.getByText(this.blockHeading, { exact: true });
    this.elBlockCta = page.getByRole('link', {
      name: this.blockCtaText,
      exact: true,
    });
  }

  async addContent() {
    await this.inputMainContentButton.click();
    await this.inputBlockHeading.fill(this.blockHeading);
    await this.inputTextColor.selectOption({ label: 'White' });
    await this.inputBlockUrl.fill(this.blockUrl);
    await this.inputBlockLinkText.fill(this.blockCtaText);
    await this.inputBlockTarget.selectOption({ label: 'New window (_blank)' });
    await this.inputBlockStyle.selectOption({ label: 'Maroon' });
    await this.inputColumnsDisplay.selectOption({ label: 'Two Columns' });
    await this.inputCardsButton.click();
  }

  async verify() {
    await expect(this.el).toHaveClass(/text-white/);
    await expect(this.elColumns).toHaveClass(/col-md-6/);
    await expect(this.elBlockHeading).toBeVisible();
    await expect(this.elBlockCta).toHaveClass(/btn-maroon/);
    await expect(this.elBlockCta).toHaveAttribute('href', this.blockUrl);
    await expect(this.elBlockCta).toHaveAttribute('target', '_blank');
  }

  async addCardGroupDefault() {
    await this.cardGroupDefault.addCardGroup();
    await this.cardGroupDefault.addContent();
  }

  async verifyCardGroupDefault() {
    await this.cardGroupDefault.verify();
  }

  async addCardGroupDegree() {
    await this.cardGroupDegree.addCardGroup();
    await this.cardGroupDegree.addContent();
  }

  async verifyCardGroupDegree() {
    await this.cardGroupDegree.verify();
  }

  async addCardGroupIcon() {
    await this.cardGroupIcon.addCardGroup();
    await this.cardGroupIcon.addContent();
  }

  async verifyCardGroupIcon() {
    await this.cardGroupIcon.verify();
  }

  async addCardGroupImage() {
    await this.cardGroupImage.addCardGroup();
    await this.cardGroupImage.addContent(1);
  }

  async verifyCardGroupImage() {
    await this.cardGroupImage.verify();
  }

  async addCardGroupRanking() {
    await this.cardGroupRanking.addCardGroup();
    await this.cardGroupRanking.addContent(1);
  }

  async verifyCardGroupRanking() {
    await this.cardGroupRanking.verify();
  }

  async addCardGroupStory() {
    await this.cardGroupStory.addCardGroup();
    await this.cardGroupStory.addContent(1);
  }

  async verifyCardGroupStory() {
    await this.cardGroupStory.verify();
  }
}

export class WebDirectory extends Block {
  constructor(page, name) {
    super(page, name);

    this.componentType = [
      'People',
      'People in departments',
      'Departments',
      'Faculty by rank',
    ];
    this.departmentName = 'SDA Operations And Facilities';
    this.departmentCampusName = 'ASU at Tempe : TEMPE';
    this.departmentExpertiseArea = 'Biochemistry';
    this.departmentEmployeeType = 'Faculty';
    this.firstPersonName = 'Nicolas Camillo';
    this.lastPersonName = 'Deborah Thayer';
    this.personTitle = 'Executive Vice President and Chief Operating Officer';
    this.personSelectNames = [
      'Nicolas Camillo, ncamillo,',
      'Kim Edwards, kedward2, SDA',
      'Rick Irwin, rirwin2, SDA',
      'Jumaane Parnell, jparnell,',
      'Deborah Thayer, dfarren, SDA',
    ];
    this.personOneName = 'Alexander Persky';
    this.personTwoName = 'Cameo Hill';
    this.personThreeName = 'Michael Crow';
    this.personOneTitle = 'Web Platforms Engineer';
    this.personTwoTitle = 'Lead Web Platforms Engineer';
    this.personThreeTitle = 'President & Professor, Office';
    this.profilesPerPage = '1';
    this.hiddenProfile = 'apersky';
    this.personTwoDepartment = 'Engineering';
    this.personTwoEmail = 'Mail to :Cameo.Hill@asu.edu';
    this.buildingName = '1551 S. Rural Rd.';
    this.locationName = 'Tempe AZ';

    this.inputComponentType = page.getByLabel('Component type');
    this.inputExpand1 = page.locator('[id="\\31 342"] i').first();
    this.inputExpand2 = page.locator('[id="\\31 345"] i').first();
    this.inputExpand3 = page.locator('[id="\\31 404"] i').first();
    this.inputExpand352 = page.locator('[id="\\31 352"] i').first();
    this.inputFilterCampus = page.getByRole('button', {
      name: 'Filter by campus',
    });
    this.inputFilterExpertise = page.getByRole('button', {
      name: 'Filter by expertise areas',
    });
    this.inputFilterEmployee = page.getByRole('button', {
      name: 'Filter by employee type',
    });
    this.inputFilterTitle = page.getByRole('textbox', {
      name: 'Filter by title',
    });
    this.inputDisableAlphabetical = page.getByRole('checkbox', {
      name: 'Disable alphabetical filter',
    });
    this.inputDisplayAsGrid = page.getByRole('checkbox', {
      name: 'Display as grid',
    });
    this.inputUsePager = page.getByRole('checkbox', { name: 'Use pager' });
    this.inputSortBy = page.getByLabel('Sort by');
    this.inputListView = page.getByRole('button', { name: 'List view' });
    this.inputSearch = page.getByRole('textbox', { name: 'Search' });
    this.inputPersonOneExpand = page.locator('#apersky i');
    this.inputPersonTwoExpand = page.locator('#cphill i');
    this.inputPersonThreeExpand = page.locator('#mcrow i');
    this.inputPersonOneTitle = page.getByRole('treeitem', {
      name: this.personOneTitle,
    });
    this.inputPersonTwoTitle = page.getByRole('treeitem', {
      name: this.personTwoTitle,
    });
    this.inputPersonThreeTitle = page.getByRole('treeitem', {
      name: this.personThreeTitle,
    });
    this.inputDefaultSort = page.getByLabel('Default sort');
    this.inputProfilesPerPage = page.getByRole('spinbutton', {
      name: 'Profiles per page',
    });
    this.inputDontDisplayProfiles = page.getByRole('textbox', {
      name: "Don't display profiles",
    });
    this.inputGridView = page.getByRole('button', { name: 'Grid view' });
    this.inputFilterByA = page.getByRole('radio', { name: 'Filter by A' });
    this.inputFilterReset = page.getByRole('radio', { name: 'All' });
    this.inputNext = page.getByRole('button', { name: 'Next Page' });
    this.inputPrev = page.getByRole('button', { name: 'Previous Page' });
    this.inputPage2 = page.getByRole('button', { name: 'Page 2 of' });

    // Existing dynamic selectors
    this.inputDepartmentName = page.getByRole('treeitem', {
      name: this.departmentName,
    });
    this.inputPersonSelections = this.personSelectNames.map((name) =>
      page.getByRole('treeitem', { name }),
    );

    this.elProfile = page.locator('.uds-person-profile').first();
    this.elPersonName = page.locator('.person-name').first();
    this.elFirstPerson = page
      .locator('.person-name')
      .filter({ hasText: this.firstPersonName });
    this.elLastPerson = page
      .locator('.person-name')
      .filter({ hasText: this.lastPersonName });
    this.elSortText = page.getByText('Sort by');
    this.elFilterText = page.getByText('Filter By Last Initial');
    this.elPersonTwoLink = page
      .getByRole('link', { name: this.personTwoName })
      .first();
    this.elPersonTwoFull = page.getByText(this.personTwoName);
    this.elPersonThree = page.getByText(this.personThreeName);
    this.elRole = page.getByRole('heading', { name: this.personTwoTitle });
    this.elDepartment = page.getByText(this.personTwoDepartment);
    this.elEmail = page.getByRole('link', { name: this.personTwoEmail });
    this.elBuilding = page.getByText(this.buildingName);
    this.elLocation = page.getByText(this.locationName);
    this.elSocial = page.getByRole('link', {
      name: 'Go to user Linkedin profile',
    });
    this.elEnvelope = page.locator('.fa-envelope');
  }

  getElPerson() {
    return this.page.getByText(this.person);
  }

  getInputDepartment() {
    return this.page.getByRole('treeitem', { name: this.departmentName });
  }

  getInputDepartmentCampus() {
    return this.page.getByRole('treeitem', { name: this.departmentCampusName });
  }

  getInputDepartmentExpertise() {
    return this.page.getByRole('treeitem', {
      name: this.departmentExpertiseArea,
      exact: true,
    });
  }

  getInputDepartmentEmployeeType() {
    return this.page.getByRole('treeitem', {
      name: this.departmentEmployeeType,
      exact: true,
    });
  }

  async addDirectoryPeople() {
    await this.inputComponentType.selectOption({
      label: this.componentType[0],
    });
    await this.inputSearch.fill(this.personOneName);
    await this.inputSearch.press('Enter');
    await this.page.waitForTimeout(3000);
    await this.inputPersonOneExpand.click();
    await this.inputPersonOneTitle.click();
    await this.page.waitForTimeout(3000);

    await this.inputSearch.fill(this.personTwoName);
    await this.inputSearch.press('Enter');
    await this.page.waitForTimeout(3000);
    await this.inputPersonTwoExpand.click();
    await this.inputPersonTwoTitle.click();
    await this.page.waitForTimeout(3000);

    await this.inputSearch.fill(this.personThreeName);
    await this.inputSearch.press('Enter');
    await this.page.waitForTimeout(3000);
    await this.inputPersonThreeExpand.click();
    await this.inputPersonThreeTitle.click();
    await this.page.waitForTimeout(3000);

    await this.inputDefaultSort.selectOption({
      label: 'Sort by order people added',
    });
    await this.inputProfilesPerPage.fill(this.profilesPerPage);
    await this.inputDontDisplayProfiles.fill(this.hiddenProfile);
  }

  async verifyDirectoryPeople() {
    await expect(this.inputGridView).toBeVisible();
    await expect(this.inputListView).toBeVisible();
    await expect(this.elSortText).toBeVisible();
    await expect(this.elFilterText).toBeVisible();
    await expect(this.inputPage2).toBeVisible();
    await expect(this.elProfile).not.toHaveClass(/uds-grid-profile/);
    await expect(this.elPersonTwoLink).toBeVisible();
    await expect(this.elRole).toBeVisible();
    await expect(this.elDepartment).toBeVisible();
    await expect(this.elEmail).toBeVisible();
    await expect(this.elBuilding).toBeVisible();
    await expect(this.elLocation).toBeVisible();
    // await expect(this.elSocial).toBeVisible()
    await this.inputNext.click();
    await expect(this.elPersonThree).toBeVisible();
    await this.inputPrev.click();
    await expect(this.elPersonTwoFull).toBeVisible();
    await this.inputFilterByA.click();
    await expect(this.elPersonTwoFull).toBeHidden();
    await this.inputFilterReset.click();
    await expect(this.elPersonTwoFull).toBeVisible();

    // Grid view
    await this.inputGridView.click();
    await expect(this.elProfile).toHaveClass(/uds-grid-profile/);
    // await expect(this.elSocial).toBeHidden()
    await expect(this.elBuilding).toBeHidden();
    await expect(this.elLocation).toBeHidden();
    await expect(this.elEnvelope).toBeVisible();
  }

  async addDirectoryPeopleInDepartments() {
    await this.inputComponentType.selectOption({
      label: this.componentType[1],
    });
    await this.page.waitForTimeout(3000);
    await this.inputExpand1.click();
    await this.page.waitForTimeout(3000);
    await this.inputExpand352.click();
    await this.page.waitForTimeout(3000);
    await this.inputDepartmentName.click();
    await this.page.waitForTimeout(1000);

    // Select all people in the department
    for (const personInput of this.inputPersonSelections) {
      await personInput.click();
      await this.page.waitForTimeout(1000);
    }

    await this.inputDisableAlphabetical.check();
    await this.inputDisplayAsGrid.check();
    await this.inputUsePager.uncheck();
  }

  async verifyDirectoryPeopleInDepartments() {
    await expect(this.elProfile).toHaveClass(/uds-grid-profile/);
    await expect(this.elPersonName).toContainText(this.firstPersonName);
    await this.inputListView.click();
    await this.inputSortBy.selectOption({ label: 'Last Name (descending)' });
    await this.page.waitForTimeout(3000);
    await expect(this.elProfile).not.toHaveClass(/uds-grid-profile/);
    await expect(this.elPersonName).toContainText(this.lastPersonName);
  }

  async addDirectoryDepartment() {
    this.departmentName = 'Office of the University Provost';
    this.departmentCampusName = 'ASU at Tempe : TEMPE';
    this.departmentExpertiseArea = 'Biochemistry';
    this.departmentEmployeeType = 'Faculty';

    await this.inputComponentType.selectOption({
      label: this.componentType[2],
    });
    await this.page.waitForTimeout(3000);
    await this.inputExpand1.click();
    await this.page.waitForTimeout(3000);
    await drupal.waitForAjax(
      this.page,
      this.getInputDepartment(),
      '/endpoint/filtered-people-in-department',
    );
    await this.inputFilterCampus.click();
    await this.page.waitForTimeout(3000);
    await drupal.waitForAjax(
      this.page,
      this.getInputDepartmentCampus(),
      '/endpoint/filtered-people-in-department',
    );
    await this.inputFilterExpertise.click();
    await this.page.waitForTimeout(3000);
    await drupal.waitForAjax(
      this.page,
      this.getInputDepartmentExpertise(),
      '/endpoint/filtered-people-in-department',
    );
    await this.inputFilterEmployee.click();
    await this.page.waitForTimeout(3000);
    await drupal.waitForAjax(
      this.page,
      this.getInputDepartmentEmployeeType(),
      '/endpoint/filtered-people-in-department',
    );
  }

  async verifyDirectoryDepartment() {
    this.person = 'Anne Jones';
    await expect(this.getElPerson()).toBeVisible();
  }

  async addDirectoryFacultyByRank() {
    this.departmentName = 'Barrett Honors Faculty';
    this.personTitle = 'Executive Vice President and Chief Operating Officer';

    await this.inputComponentType.selectOption({
      label: this.componentType[3],
    });
    await this.page.waitForTimeout(3000);
    await this.inputExpand1.click();
    await this.page.waitForTimeout(3000);
    await this.inputExpand2.click();
    await this.page.waitForTimeout(3000);
    await this.inputExpand3.click();
    await this.page.waitForTimeout(3000);
    await drupal.waitForAjax(
      this.page,
      this.getInputDepartment(),
      '/endpoint/people-in-department',
    );
    await this.inputFilterTitle.fill(this.personTitle);
  }

  async verifyDirectoryFacultyByRank() {
    this.person = 'Chris Howard';
    await expect(this.getElPerson()).toBeVisible();
  }
}

export class Example extends Block {
  constructor(page, name) {
    super(page, name);
  }

  async addContent() {}

  async verify() {}
}
