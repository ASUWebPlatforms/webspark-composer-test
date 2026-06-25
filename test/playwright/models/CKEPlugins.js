import { expect } from '@playwright/test';
import { faker } from '@faker-js/faker/locale/en';
import { CKEditor } from './CKEditor';
import drupal from '../helpers/Drupal';

export class BlockquoteAnimated extends CKEditor {
  constructor(page, name) {
    super(page, name);
    this.title = faker.lorem.words();
    this.content = faker.lorem.paragraph();
    this.name = faker.person.fullName();
    this.description = faker.lorem.sentence();

    this.inputTitle = this.inputToolbar.getByRole('textbox').first();
    this.inputContent = this.inputToolbar
      .locator('form')
      .filter({ hasText: 'Animated quotes only support' })
      .locator('textarea');
    this.inputName = this.inputToolbar.getByRole('textbox').nth(2);
    this.inputDescription = this.inputToolbar.getByRole('textbox').nth(3);
    this.inputHighlight = page
      .getByRole('button', { name: 'Highlight' })
      .nth(1);

    this.elTitle = page.getByRole('heading', { name: this.title });
    this.elContent = page.getByRole('mark');
    this.elName = page.getByText(this.name, { exact: true });
    this.elDescription = page.getByText(this.description, { exact: true });
  }

  async addContent() {
    await this.inputTitle.fill(this.title);
    await this.inputContent.fill(this.content);
    await this.inputName.fill(this.name);
    await this.inputDescription.fill(this.description);
    await this.saveToolbar();
    await this.inputHighlight.click();
  }

  async verify() {
    await expect(this.elTitle).toBeVisible();
    await expect(this.elContent).toBeVisible();
    await expect(this.elContent).toHaveClass(/pen-yellow/);
    await expect(this.elContent).toHaveClass(/animate-bg-in-scroll/);
    await expect(this.elName).toBeVisible();
    await expect(this.elDescription).toBeVisible();
  }
}

export class Blockquote extends CKEditor {
  constructor(page, name) {
    super(page, name);
    this.content = faker.lorem.paragraph();
    this.citation = faker.person.fullName();
    this.description = faker.lorem.sentence();

    this.inputContent = page
      .locator('form')
      .filter({
        hasText: /^ContentCitation NameCitation DescriptionSaveCancel$/,
      })
      .locator('textarea');
    this.inputCitation = this.inputToolbar.getByRole('textbox').nth(1);
    this.inputDescription = this.inputToolbar.getByRole('textbox').nth(2);

    this.elContent = page.locator('#skip-to-content').getByText(this.content);
    this.elCitation = page.getByText(this.citation);
    this.elDescription = page.getByText(this.description);
    this.elQuote = page.locator('.accent-maroon');
  }

  async addContent() {
    await this.inputContent.fill(this.content);
    await this.inputCitation.fill(this.citation);
    await this.inputDescription.fill(this.description);
    await this.saveToolbar();
  }

  async verify() {
    await expect(this.elContent).toBeVisible();
    await expect(this.elCitation).toBeVisible();
    await expect(this.elDescription).toBeVisible();
    await expect(this.elQuote).toBeVisible();
  }
}

export class Button extends CKEditor {
  constructor(page, name) {
    super(page, name);
    this.buttonText = faker.lorem.words(2);
    this.buttonUrl = faker.internet.url();
    this.buttonStyle = 'maroon';
    this.buttonSize = 'md';
    this.buttonTarget = '_blank';

    this.inputUrl = page
      .locator('div')
      .filter({ hasText: /^URL$/ })
      .getByRole('textbox');
    this.inputStyle = page
      .locator('div')
      .filter({
        hasText: /^StyleButton GoldButton MaroonButton Gray 2Button Gray 7$/,
      })
      .getByRole('combobox');
    this.inputSize = page
      .locator('div')
      .filter({ hasText: /^SizeDefaultMediumSmall$/ })
      .getByRole('combobox');
    this.inputTarget = page.getByRole('combobox').nth(2);

    this.elButton = page.getByRole('link', { name: 'Button', exact: true });
    this.elButtonText = page.getByText(this.buttonText, { exact: true });
  }

  async addContent() {
    await this.inputUrl.fill(this.buttonUrl);
    await this.inputStyle.selectOption(this.buttonStyle);
    await this.inputSize.selectOption(this.buttonSize);
    await this.inputTarget.selectOption(this.buttonTarget);
    await this.saveToolbar();
  }

  async verify() {
    await expect(this.elButton).toBeVisible();
    await expect(this.elButton).toHaveClass(
      new RegExp(`btn-${this.buttonStyle}`),
    );
    await expect(this.elButton).toHaveClass(
      new RegExp(`btn-${this.buttonSize}`),
    );
    await expect(this.elButton).toHaveAttribute('href', this.buttonUrl);
    await expect(this.elButton).toHaveAttribute('target', this.buttonTarget);
  }
}

export class Divider extends CKEditor {
  constructor(page, name) {
    super(page, name);

    this.el = page.getByRole('separator');
  }

  async verify() {
    await expect(this.el).toBeVisible();
    await expect(this.el).toHaveClass('copy-divider');
  }
}

export class HighlightedHeading extends CKEditor {
  constructor(page, name) {
    super(page, name);
    this.headingText = faker.lorem.words(3);
    this.highlightStyle = 'black';
    this.headingLevel = 'h3';

    this.inputHeadingText = this.inputToolbar.getByRole('textbox');
    this.inputHighlightStyle = page
      .locator('div')
      .filter({
        hasText: /^StyleGold HighlightGray 7 HighlightWhite Highlight$/,
      })
      .getByRole('combobox');
    this.inputHeadingLevel = page
      .locator('div')
      .filter({ hasText: /^HeadingH2H3H4$/ })
      .getByRole('combobox');

    this.elHeadingContainer = page.locator('.uds-highlighted-heading > h3');
    this.elHeadingText = page.getByText(this.headingText, { exact: true });
  }

  async addContent() {
    await this.inputHeadingText.fill(this.headingText);
    await this.inputHighlightStyle.selectOption(this.highlightStyle);
    await this.inputHeadingLevel.selectOption(this.headingLevel);
    await this.saveToolbar();
  }

  async verify() {
    await expect(this.elHeadingContainer).toBeVisible();
    await expect(this.elHeadingText).toHaveClass(
      new RegExp(`highlight-${this.highlightStyle}`),
    );
  }
}

export class HorizonalRule extends CKEditor {
  constructor(page, name) {
    super(page, name);

    this.el = page.getByRole('separator');
  }

  async verify() {
    await expect(this.el).toBeVisible();
  }
}

export class Lead extends CKEditor {
  constructor(page, name) {
    super(page, name);
    this.leadText = faker.lorem.sentence();

    this.inputLeadText = this.inputToolbar.getByRole('textbox');

    this.elLeadText = page.getByText(this.leadText, { exact: true });
  }

  async addContent() {
    await this.inputLeadText.fill(this.leadText);
    await this.saveToolbar();
  }

  async verify() {
    await expect(this.elLeadText).toHaveClass('lead');
  }
}

export class Table extends CKEditor {
  constructor(page, name) {
    super(page, name);
    this.caption = faker.lorem.words();
    this.header1 = faker.lorem.word();
    this.header2 = faker.lorem.word();
    this.cell1 = faker.number.int(20).toString();
    this.cell2 = faker.number.int(20).toString();
    this.cell3 = faker.number.int(20).toString();
    this.cell4 = faker.number.int(20).toString();

    this.inputRowType = this.page.getByRole('combobox').nth(0);
    this.inputTableType = this.page.getByRole('combobox').nth(1);
    this.inputCaption = this.inputToolbar.getByRole('textbox').nth(2);
    this.inputEditorArea = this.page.getByLabel('Rich Text Editor. Editing');
    this.inputHeader1 = this.inputEditorArea.locator('thead span').nth(0);
    this.inputHeader2 = this.inputEditorArea.locator('thead span').nth(1);
    this.inputCell1 = this.inputEditorArea.locator('tbody span').nth(0);
    this.inputCell2 = this.inputEditorArea.locator('tbody span').nth(1);
    this.inputCell3 = this.inputEditorArea.locator('tbody span').nth(2);
    this.inputCell4 = this.inputEditorArea.locator('tbody span').nth(3);

    this.elTable = this.page.locator('.uds-table');
    this.elCaption = this.page.getByText(this.caption, { exact: true });
    this.elHeader1 = this.page.getByRole('cell', {
      name: this.header1,
      exact: true,
    });
    this.elHeader2 = this.page.getByRole('cell', {
      name: this.header2,
      exact: true,
    });
    this.elCell1 = this.page.getByRole('cell', {
      name: this.cell1,
      exact: true,
    });
    this.elCell2 = this.page.getByRole('cell', {
      name: this.cell2,
      exact: true,
    });
    this.elCell3 = this.page.getByRole('cell', {
      name: this.cell3,
      exact: true,
    });
    this.elCell4 = this.page.getByRole('cell', {
      name: this.cell4,
      exact: true,
    });
  }

  async addContent() {
    await this.inputRowType.selectOption('row');
    await this.inputTableType.selectOption('fixed');
    await this.inputCaption.fill(this.caption);
    await this.saveToolbar();
    await this.inputHeader1.fill(this.header1);
    await this.inputHeader2.fill(this.header2);
    await this.inputCell1.fill(this.cell1);
    await this.inputCell2.fill(this.cell2);
    await this.inputCell3.fill(this.cell3);
    await this.inputCell4.fill(this.cell4);
    await this.save();
  }

  async verify() {
    await expect(this.elTable).toHaveClass(/uds-table-fixed/);
    await expect(this.elCaption).toBeVisible();
    await expect(this.elHeader1).toBeVisible();
    await expect(this.elHeader2).toBeVisible();
    await expect(this.elCell1).toBeVisible();
    await expect(this.elCell2).toBeVisible();
    await expect(this.elCell3).toBeVisible();
    await expect(this.elCell4).toBeVisible();
  }
}

export class Toolbar extends CKEditor {
  constructor(page, name) {
    super(page, name);

    this.elBold = this.page.getByLabel('Bold', { exact: true });
    this.elItalic = this.page.getByLabel('Italic', { exact: true });
    this.elLink = this.page.getByLabel('Link', { exact: true });
    this.elBulletedList = this.page.getByLabel('Bulleted List', {
      exact: true,
    });
    this.elNumberedList = this.page.getByLabel('Numbered List', {
      exact: true,
    });
    this.elListProperties = this.page.getByLabel('List Properties', {
      exact: true,
    });
    this.elFontawesome = this.page.getByLabel('Insert Fontawesome Icon', {
      exact: true,
    });
    this.elUploadImage = this.page.getByLabel('Upload image from computer', {
      exact: true,
    });
    this.elInsertMedia = this.page.getByLabel('Insert Media', { exact: true });
    this.elParagraphHeading = this.page.getByLabel('Paragraph, Heading', {
      exact: true,
    });
    this.elSource = this.page.getByLabel('Source', { exact: true });
    this.elResponsive = this.page.getByLabel('CKEditor Responsive', {
      exact: true,
    });
    this.elButton = this.page.getByLabel('Button', { exact: true });
    this.elHorizontalLine = this.page.getByLabel('Horizontal line', {
      exact: true,
    });
    this.elDivider = this.page.getByLabel('Divider', { exact: true });
    this.elLead = this.page.getByLabel('Lead', { exact: true });
    this.elHighlightedHeading = this.page.getByLabel('Highlighted Heading', {
      exact: true,
    });
    this.elBlockquote = this.page.getByLabel('Blockquote', { exact: true });
    this.elWebsparkTable = this.page.getByLabel('Webspark table', {
      exact: true,
    });
    this.elHighlight = this.page
      .getByLabel('Highlight', { exact: true })
      .nth(1);
  }

  async verify() {
    await expect(this.elBold).toBeVisible();
    await expect(this.elItalic).toBeVisible();
    await expect(this.elLink).toBeVisible();
    await expect(this.elBulletedList).toBeVisible();
    await expect(this.elNumberedList).toBeVisible();
    await expect(this.elListProperties).toBeVisible();
    await expect(this.elFontawesome).toBeVisible();
    await expect(this.elUploadImage).toBeVisible();
    await expect(this.elInsertMedia).toBeVisible();
    await expect(this.elParagraphHeading).toBeVisible();
    await expect(this.elSource).toBeVisible();
    await expect(this.elResponsive).toBeVisible();
    await expect(this.elButton).toBeVisible();
    await expect(this.elHorizontalLine).toBeVisible();
    await expect(this.elDivider).toBeVisible();
    await expect(this.elLead).toBeVisible();
    await expect(this.elHighlightedHeading).toBeVisible();
    await expect(this.elBlockquote).toBeVisible();
    await expect(this.elWebsparkTable).toBeVisible();
    await expect(this.elHighlight).toBeVisible();
  }

  // TODO: This is now only available in the FULL_HTML
  // Need a new test to switch the editor then verify
  async verifyVariant() {
    this.elBlockquoteAnimated = this.page.getByLabel('Blockquote Animated', {
      exact: true,
    });
    await expect(this.elBlockquoteAnimated).toBeVisible();
  }
}
