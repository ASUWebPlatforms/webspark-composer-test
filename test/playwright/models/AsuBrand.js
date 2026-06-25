import { expect } from '@playwright/test';
import { faker } from '@faker-js/faker/locale/en';
import drupal from '../helpers/Drupal';
import { Accordion, TextContent } from './WebsparkBlocks.js';

export class AnchorMenu {
  constructor(page, name) {
    this.page = page;
    this.name = name;
    this.block = new TextContent(page, 'Text Content');
    this.elHeading = page.getByRole('heading', { name: 'On This Page:' });
    this.elLink = page.getByRole('link', { name: this.block.name });
  }

  async add() {
    await this.block.add();
    await this.block.addContent();
    await this.block.addAnchorMenuSettings();
  }

  async save() {
    await this.block.save();
  }

  async verify() {
    await expect(this.elHeading).toBeVisible();
    await expect(this.elLink).toBeVisible();
  }
}

export class Cas {
  constructor(page, name) {
    this.page = page;
    this.name = name;
    this.elSignIn = page.getByRole('link', { name: 'Sign In', exact: true });
    this.elCasHeader = page.getByRole('heading', {
      name: 'Application Not Authorized to Use CAS',
      exact: true,
    });
  }

  async verify() {
    await this.page.goto('/');
    await this.page.waitForTimeout(3000);
    await this.elSignIn.click();
    await expect(this.elCasHeader).toBeVisible();
  }
}

export class Spacing {
  constructor(page, name) {
    this.page = page;
    this.name = name;
    this.block = new TextContent(page, 'Text Content');
    this.el = this.page.locator('.spacing-top-8.spacing-bottom-8');
  }

  async add() {
    await this.block.add();
    await this.block.addContent();
    await this.block.addSpacingSettings();
  }

  async save() {
    await this.block.save();
  }

  async verify() {
    await expect(this.el).toBeVisible();
  }
}

export class Breadcrumb {
  constructor(page, name) {
    this.page = page;
    this.name = name;
    this.el = page.locator('.bg-gray-1.spacing-top-8.spacing-bottom-8');
    this.elHome = page
      .getByLabel('breadcrumbs')
      .getByRole('link', { name: 'Home' });
    this.elLink = page
      .getByLabel('breadcrumbs')
      .getByRole('link', { name: `Playwright ${this.name}` });
  }

  async verify() {
    await expect(this.el).toBeVisible();
    await expect(this.elHome).toHaveAttribute('href', '/');
    await expect(this.elLink).toHaveAttribute('href', '');
  }
}

export class FontAwesome {
  constructor(page, name) {
    this.page = page;
    this.name = name;
    this.block = new Accordion(page, 'Accordion');
    this.elIcon = page.locator('.accordion-header.accordion-header-icon');
  }

  async add() {
    await this.block.add();
    await this.block.addContent();
  }

  async save() {
    await this.block.save();
  }

  async verify() {
    await expect(this.elIcon).toBeVisible();
  }
}

export class Footer {
  constructor(page, name) {
    this.page = page;
    this.name = name;
    this.defaultUrl = '#';
    this.unitName = faker.company.name();
    this.unitTitle = faker.lorem.words();
    this.ctaTitle = faker.lorem.words();
    this.menuTitle = faker.lorem.words();
    this.innovationLinks = [
      {
        name: 'Maps and Locations',
        url: 'https://www.asu.edu/about/locations-maps',
      },
      { name: 'Jobs', url: 'https://cfo.asu.edu/applicant' },
      {
        name: 'Directory',
        url: 'https://search.asu.edu/?search-tabs=web_dir_faculty_staff',
      },
      { name: 'Contact ASU', url: 'https://www.asu.edu/about/contact' },
      { name: 'My ASU', url: 'https://my.asu.edu' },
    ];
    this.colophonLinks = [
      {
        name: 'Copyright and Trademark',
        url: 'https://www.asu.edu/about/copyright-trademark',
      },
      { name: 'Accessibility', url: 'https://accessibility.asu.edu/report' },
      { name: 'Privacy', url: 'https://www.asu.edu/about/privacy' },
      { name: 'Terms of Use', url: 'https://www.asu.edu/about/terms-of-use' },
      { name: 'Emergency', url: 'https://www.asu.edu/emergency' },
    ];
    this.rankingImage = {
      alt: 'Repeatedly ranked #1 on 30+ lists in the last 3 years',
      src: '/profiles/contrib/webspark/modules/asu_footer/img/footer-rank.png',
      href: 'https://www.asu.edu/rankings',
    };
    this.socialLinks = [
      { name: 'Facebook Social Media Icon' },
      { name: 'X / Twitter Social Media Icon' },
      { name: 'Instagram Social Media Icon' },
      { name: 'YouTube Social Media Icon' },
      { name: 'LinkedIn Social Media Icon' },
    ];

    this.inputShowSocialMedia = page.getByRole('checkbox', {
      name: 'Show social media and unit',
    });
    this.inputLogoUrl = page.getByRole('textbox', { name: 'Logo URL' });
    this.inputFacebookUrl = page.getByRole('textbox', {
      name: 'Facebook Social Media',
    });
    this.inputXUrl = page.getByRole('textbox', {
      name: 'X / Twitter Social Media',
    });
    this.inputLinkedinUrl = page.getByRole('textbox', {
      name: 'LinkedIn Social Media',
    });
    this.inputInstagramUrl = page.getByRole('textbox', {
      name: 'Instagram Social Media',
    });
    this.inputYoutubeUrl = page.getByRole('textbox', {
      name: 'YouTube Social Media',
    });
    this.inputShowColumns = page.getByRole('checkbox', {
      name: 'Show columns',
    });
    this.inputUnitName = page.getByRole('textbox', {
      name: 'Name of Unit/School/College *',
    });
    this.inputLinkTitle = page.getByRole('textbox', { name: 'Link Title' });
    this.inputLinkUrl = page.locator(
      '#edit-settings-asu-footer-block-link-asu-footer-block-link-url',
    );
    this.inputCtaTitle = page.getByRole('textbox', { name: 'CTA Title' });
    this.inputCtaUrl = page.locator(
      '#edit-settings-asu-footer-block-cta-asu-footer-block-cta-url',
    );
    this.inputSecondColumnName = page.locator(
      '#edit-settings-second-column-asu-footer-block-menu-second-column-name',
    );
    this.inputMenuTitle = page.getByRole('textbox', { name: 'Menu title *' });
    this.inputSave = page.getByRole('button', { name: 'Save block' });

    this.elEndorsedFooter = page.locator('#wrapper-endorsed-footer');
    this.elASULink = page.getByRole('link', {
      name: 'Arizona State University.',
    });
    this.elColumns = page.locator('#footer-columns div.col-xl');
    this.elFooterColumns = page.locator('#wrapper-footer-columns');
    this.elUnitName = page.getByText(this.unitName, { exact: true });
    this.elUnitTitle = page.getByRole('link', { name: this.unitTitle });
    this.elCtaTitle = page.getByRole('link', { name: this.ctaTitle });
    this.elHomeLink = page.getByRole('link', { name: 'Home', exact: true });
    this.elRankingImageAlt = page.getByAltText(this.rankingImage.alt);
    this.elRankingImageLink = page.getByRole('link', {
      name: this.rankingImage.alt,
    });
    this.elFooterInovation = page.locator('#wrapper-footer-innovation');
    this.elFooterColophon = page.locator('#wrapper-footer-colophon');
  }

  async add() {
    await this.page.goto(
      '/admin/structure/block/manage/asufooter?destination=/admin/structure/block',
    );
    await this.inputShowSocialMedia.check();
    await this.inputLogoUrl.fill(this.defaultUrl);
    await this.inputFacebookUrl.fill(this.defaultUrl);
    await this.inputXUrl.fill(this.defaultUrl);
    await this.inputLinkedinUrl.fill(this.defaultUrl);
    await this.inputInstagramUrl.fill(this.defaultUrl);
    await this.inputYoutubeUrl.fill(this.defaultUrl);

    await this.inputShowColumns.check();
    await this.inputUnitName.fill(this.unitName);
    await this.inputLinkTitle.fill(this.unitTitle);
    await this.inputLinkUrl.fill(this.defaultUrl);
    await this.inputCtaTitle.fill(this.ctaTitle);
    await this.inputCtaUrl.fill(this.defaultUrl);

    await this.inputSecondColumnName.selectOption({ label: 'Main navigation' });
    await this.inputMenuTitle.fill(this.menuTitle);
  }

  async save() {
    await this.inputSave.click();
  }

  async verify() {
    await this.page.goto('/');
    await this.verifyEndorsed();
    await this.verifyColumns();
    await this.verifyInnovation();
    await this.verifyColophon();
  }

  async verifyEndorsed() {
    await expect(this.elEndorsedFooter).toBeVisible();
    await expect(this.elASULink).toBeVisible();
    for (const link of this.socialLinks) {
      await expect(
        this.page
          .getByLabel('Social Media')
          .getByRole('link', { name: link.name }),
      ).toBeVisible();
    }
  }

  async verifyColumns() {
    await expect(this.elColumns).toHaveCount(2);
    await expect(this.elFooterColumns).toBeVisible();
    await expect(this.elUnitName).toBeVisible();
    await expect(this.elUnitTitle).toBeVisible();
    await expect(this.elCtaTitle).toBeVisible();
    await expect(this.elHomeLink).toBeVisible();
  }

  async verifyInnovation() {
    await expect(this.elFooterInovation).toBeVisible();
    await expect(this.elRankingImageAlt).toBeVisible();
    await expect(this.elRankingImageAlt).toHaveAttribute(
      'src',
      this.rankingImage.src,
    );
    await expect(this.elRankingImageLink).toHaveAttribute(
      'href',
      this.rankingImage.href,
    );
    for (const link of this.innovationLinks) {
      await expect(
        this.page
          .getByLabel('University Services')
          .getByRole('link', { name: link.name, exact: true }),
      ).toHaveAttribute('href', link.url);
    }
  }

  async verifyColophon() {
    await expect(this.elFooterColophon).toBeVisible();
    for (const link of this.colophonLinks) {
      await expect(
        this.page
          .getByLabel('University Legal and Compliance')
          .getByRole('link', { name: link.name, exact: true }),
      ).toHaveAttribute('href', link.url);
    }
  }
}

export class Header {
  constructor(page, name) {
    this.page = page;
    this.name = name;
    this.siteName = null;

    this.partnerUrl = faker.internet.url();
    this.partnerLogoUrl = faker.image.url();
    this.partnerLogoAlt = faker.lorem.words(2);
    this.unitName = faker.company.name();
    this.unitUrl = faker.internet.url();
    this.ctaText = faker.lorem.words(2);
    this.ctaUrl = faker.internet.url();

    this.links = [
      { name: 'ASU Home', url: 'https://asu.edu' },
      { name: 'My ASU', url: 'https://my.asu.edu' },
      {
        name: 'Colleges and Schools',
        url: 'https://www.asu.edu/academics/colleges-schools',
      },
    ];
    this.menuLinks = {
      standard: { name: 'Kitchen Sink', url: '/kitchen-sink' },
      dropdown: { name: 'Degree Pages' },
      mega: { name: 'Mega Menu' },
      firstLevel: { name: 'Degree Detail Page' },
      secondLevel: { name: 'Second level' },
      link: { name: 'Link' },
      button: { name: 'Button' },
      trayButton: { name: 'Tray Button' },
    };
    this.columnHeading = 'Optional heading';

    this.inputParentUnitName = page.getByRole('textbox', {
      name: 'Parent unit name',
    });
    this.inputParentUnitUrl = page.getByRole('textbox', {
      name: 'Parent department URL *',
    });
    this.inputCallToActionButtons = page.getByRole('button', {
      name: 'Call To Action Buttons',
    });
    this.inputCtaText = page.getByRole('textbox', { name: 'Text' });
    this.inputCtaTarget = page.getByRole('textbox', { name: 'URL Target' });
    this.inputCtaColor = page.getByRole('combobox', { name: 'Style' });
    this.inputAsuPartnerHeader = page.getByRole('button', {
      name: 'ASU Partner Header',
    });
    this.inputIsPartner = page.getByRole('checkbox', { name: 'Is Partner?' });
    this.inputPartnerUrl = page.getByRole('textbox', { name: 'Partner URL *' });
    this.inputPartnerLogoUrl = page.getByRole('textbox', {
      name: 'Partner Logo URL *',
    });
    this.inputPartnerLogoAlt = page.getByRole('textbox', {
      name: 'Partner Logo Alt *',
    });
    this.inputSaveBlock = page.getByRole('button', { name: 'Save block' });

    // Element selectors for verification
    this.elSiteTitle = null;
    this.elHomeLink = null;
    this.elSkipToContent = page.getByRole('link', {
      name: 'Skip to main content',
    });
    this.elAccessibilityReport = page.getByRole('link', {
      name: 'Report an accessibility problem',
    });
    this.elAsuLogo = page.getByRole('link', {
      name: 'Arizona State University logo',
    });
    this.elHomeIcon = page.locator('.fa-house');
    this.elCta1 = page.getByRole('link', { name: this.ctaText });
    this.elUniversalNavbar = page.getByTestId('universal-navbar');
    this.elSearchButton = page.getByTestId('search-button');

    // Navigation elements
    this.elNavigation = page.getByTestId('navigation');
    this.elStandardLink = page
      .getByTestId('navigation')
      .getByRole('link', { name: this.menuLinks.standard.name });
    this.elDropdownButton = page.getByRole('link', {
      name: this.menuLinks.dropdown.name,
      exact: true,
    });
    this.elMegaButton = page.getByRole('link', {
      name: this.menuLinks.mega.name,
      exact: true,
    });
    this.elDropdown = page.locator('.dropdown-2');
    this.elMega = page.locator('.dropdown-3');
    this.elFirstLevel = page.getByRole('link', {
      name: this.menuLinks.firstLevel.name,
      exact: true,
    });
    this.elSecondLevel = page.getByRole('link', {
      name: this.menuLinks.secondLevel.name,
      exact: true,
    });
    this.elMenuLinks = page.getByRole('link', {
      name: this.menuLinks.link.name,
      exact: true,
    });
    this.elButtonLink = page.getByTestId('navigation').getByRole('link', {
      name: this.menuLinks.button.name,
      exact: true,
    });
    this.elTrayButtonLink = page.getByRole('link', {
      name: this.menuLinks.trayButton.name,
      exact: true,
    });

    // Partner elements
    this.elPartnerSection = page.getByTestId('partner');
    this.elPartnerLink = page
      .getByTestId('partner')
      .getByRole('link', { name: this.partnerLogoAlt });
    this.elPartnerImg = page
      .getByTestId('partner')
      .getByRole('img', { name: this.partnerLogoAlt });

    // Parent elements
    this.elParentUnit = page.getByRole('link', { name: 'Parent unit' });

    this.elColumnSpan = page.locator('.uds-hdr-dropdown-container-column');
    this.elColumnHeadings = page.getByRole('heading', {
      name: this.columnHeading,
      exact: true,
    });
  }

  /**
   * Initialize the Header with dynamic site name from Drupal
   * Call this method after constructor to set the actual site name
   * @returns {Promise<void>}
   */
  async #init() {
    this.siteName = await drupal.getSiteName();
    this.elSiteTitle = this.page
      .getByTestId('title')
      .getByRole('link', { name: this.siteName, exact: true });
    this.elHomeLink = this.page.getByRole('link', {
      name: `${this.siteName} home page`,
    });
  }

  async add() {
    await this.#init();
    await this.page.goto(
      '/admin/structure/block/manage/asubrandheader?destination=/admin/structure/block',
    );
    await this.inputCallToActionButtons.click();
    await this.inputCtaText.first().fill(this.ctaText);
    await this.inputCtaTarget.first().fill(this.ctaUrl);
    await this.inputCtaColor.first().selectOption({ label: 'Gold' });
    await this.inputCtaText.nth(1).fill(this.ctaText);
    await this.inputCtaTarget.nth(1).fill(this.ctaUrl);
    await this.inputCtaColor.nth(1).selectOption({ label: 'Maroon' });
  }

  async addParent() {
    await this.page.goto(
      '/admin/structure/block/manage/asubrandheader?destination=/admin/structure/block',
    );
    // Note the use of type() instead of fill() here
    await this.inputParentUnitName.type(this.unitName);
    await this.inputParentUnitUrl.fill(this.unitUrl);
  }

  async addPartner() {
    await this.page.goto(
      '/admin/structure/block/manage/asubrandheader?destination=/admin/structure/block',
    );
    await this.inputAsuPartnerHeader.click();
    await this.inputIsPartner.check();
    await this.inputPartnerUrl.fill(this.partnerUrl);
    await this.inputPartnerLogoUrl.fill(this.partnerLogoUrl);
    await this.inputPartnerLogoAlt.fill(this.partnerLogoAlt);
  }

  async save() {
    await this.inputSaveBlock.click();
  }

  async verifyDefault() {
    await this.page.goto('/');
    await expect(this.elSkipToContent).toBeVisible();
    await expect(this.elAccessibilityReport).toBeVisible();
    await expect(this.elAsuLogo).toBeVisible();
    await expect(this.elSiteTitle).toBeVisible();
    await expect(this.elHomeLink).toHaveAttribute('href', '/');
    await expect(this.elHomeIcon).toBeVisible();
    await expect(this.elCta1.first()).toHaveAttribute('href', this.ctaUrl);
    await expect(this.elCta1.first()).toHaveClass(/button-gold/);

    for (const link of this.links) {
      await expect(
        this.elUniversalNavbar.getByRole('link', {
          name: link.name,
          exact: true,
        }),
      ).toHaveAttribute('href', link.url);
    }
    await expect(this.elSearchButton).toBeVisible();
  }

  async verifyPartner() {
    await this.page.goto('/');
    await expect(this.elPartnerLink).toHaveAttribute('href', this.partnerUrl);
    await expect(this.elPartnerImg).toBeVisible();
  }

  async verifyParent() {
    await this.page.goto(
      'https://websparkreleasestable-asufactory1.acquia.asu.edu',
    );
    await expect(this.elParentUnit).toHaveAttribute('href', '#');
  }

  async verifyMenu() {
    await this.page.goto(
      'https://websparkreleasestable-asufactory1.acquia.asu.edu',
    );
    await expect(this.elStandardLink).toBeVisible();
    await expect(this.elStandardLink).toHaveAttribute(
      'href',
      this.menuLinks.standard.url,
    );
    await expect(this.elDropdown).toBeHidden();
    await expect(this.elMega).toBeHidden();

    // Standard dropdown
    await this.elDropdownButton.click();
    await expect(this.elFirstLevel).toBeVisible();
    await expect(this.elSecondLevel).toBeHidden();

    // Mega menu
    await this.elMegaButton.click();
    await expect(this.elColumnSpan).toHaveCount(3);
    await expect(this.elColumnSpan.nth(1)).toHaveAttribute(
      'style',
      '--span: 2;',
    );
    await expect(this.elColumnHeadings).toHaveCount(3);
    await expect(this.elColumnHeadings.first()).toBeVisible();
    await expect(this.elMenuLinks).toHaveCount(8);
    await expect(this.elButtonLink).toHaveClass(/button-maroon/);
    await expect(this.elTrayButtonLink.first()).toHaveClass(/button-maroon/);
    await expect(this.elTrayButtonLink.nth(1)).toHaveClass(/button-gold/);
  }
}

export class Layout {
  constructor(page, name) {
    this.page = page;
    this.name = name;
    this.blockTitle = faker.lorem.words();
    this.sectionStyles = {
      style: 'Gray 1 Background',
      position: 'Bottom',
      fill: '75%',
      flexDirection: 'Reverse',
    };
    this.sectionNames = {
      oneColumnFullWidth: 'One Column full-width section',
      oneColumnFixedWidth: 'One Column fixed-width',
      twoColumnBootstrap: 'Two column bootstrap Two',
      threeColumnFixedWidth: 'Three Column fixed-width',
      fourColumnFixedWidth: 'Four Column fixed-width',
    };

    this.inputAddSectionStart = page.getByRole('link', {
      name: 'Add section at start of layout',
    });
    this.inputAddSection = page.getByRole('button', { name: 'Add section' });
    this.inputOneColumnFullWidth = page.getByRole('link', {
      name: this.sectionNames.oneColumnFullWidth,
    });
    this.inputOneColumnFixedWidth = page.getByRole('link', {
      name: this.sectionNames.oneColumnFixedWidth,
    });
    this.inputTwoColumnBootstrap = page.getByRole('link', {
      name: this.sectionNames.twoColumnBootstrap,
    });
    this.inputThreeColumnFixedWidth = page.getByRole('link', {
      name: this.sectionNames.threeColumnFixedWidth,
    });
    this.inputFourColumnFixedWidth = page.getByRole('link', {
      name: this.sectionNames.fourColumnFixedWidth,
    });
    this.inputStyle = page.getByLabel('Style');
    this.inputBackgroundPosition = page.getByLabel('Background position');
    this.inputBackgroundFill = page.getByLabel('Background fill');
    this.inputFlexDirection = page.getByLabel('Flex direction');
    this.inputChooseBlock = page
      .getByLabel('Choose a block')
      .getByRole('link', { name: 'Title' });
    this.inputAddBlock = page.getByRole('button', { name: 'Add block' });
    this.inputSave = page.getByRole('button', { name: 'Save layout' });

    this.inputAddBlockSection5First = page.getByRole('link', {
      name: 'Add block in Section 5, First region',
    });
    this.inputAddBlockSection4First = page.getByRole('link', {
      name: 'Add block in Section 4, First region',
    });
    this.inputAddBlockSection3First = page.getByRole('link', {
      name: 'Add block in Section 3, First region',
    });
    this.inputAddBlockSection3Second = page.getByRole('link', {
      name: 'Add block in Section 3, Second region',
    });
    this.inputAddBlockSection2First = page.getByRole('link', {
      name: 'Add block in Section 2, First region',
    });
    this.inputAddBlockSection2Second = page.getByRole('link', {
      name: 'Add block in Section 2, Second region',
    });
    this.inputAddBlockSection2Third = page.getByRole('link', {
      name: 'Add block in Section 2, Third region',
    });
    this.inputAddBlockSection1First = page.getByRole('link', {
      name: 'Add block in Section 1, First region',
    });
    this.inputAddBlockSection1Second = page.getByRole('link', {
      name: 'Add block in Section 1, Second region',
    });
    this.inputAddBlockSection1Third = page.getByRole('link', {
      name: 'Add block in Section 1, Third region',
    });
    this.inputAddBlockSection1Fourth = page.getByRole('link', {
      name: 'Add block in Section 1, Fourth region',
    });

    this.elLayout5 = page.locator('.uds-full-width > .bg');
    this.elLayout4 = page.locator('.layout__fixed-width > .bg-top');
    this.elLayout3 = page.locator('.uds-flex-order-reversed .col-md-6');
    this.elLayout2 = page.locator(
      '.bg-top.bg-percent-100.max-size-container.center-container.clearfix.bg-white .col-md-4',
    );
    this.elLayout1 = page.locator(
      '.bg-top.bg-percent-100.max-size-container.center-container.clearfix.bg-white .col-md-3',
    );
  }

  async add() {
    await this.page.waitForTimeout(3000);

    // Add One Column full-width section
    await this.inputAddSectionStart.click();
    await this.inputOneColumnFullWidth.click();
    await this.inputStyle.selectOption({ label: this.sectionStyles.style });
    await this.inputBackgroundPosition.selectOption({
      label: this.sectionStyles.position,
    });
    await this.inputBackgroundFill.selectOption({
      label: this.sectionStyles.fill,
    });
    await this.inputAddSection.click();

    // Add One Column fixed-width section
    await this.inputAddSectionStart.click();
    await this.inputOneColumnFixedWidth.click();
    await this.inputAddSection.click();

    // Add Two column bootstrap section
    await this.inputAddSectionStart.click();
    await this.inputTwoColumnBootstrap.click();
    await this.inputFlexDirection.selectOption({
      label: this.sectionStyles.flexDirection,
    });
    await this.inputAddSection.click();

    // Add Three Column fixed-width section
    await this.inputAddSectionStart.click();
    await this.inputThreeColumnFixedWidth.click();
    await this.inputAddSection.click();

    // Add Four Column fixed-width section
    await this.inputAddSectionStart.click();
    await this.inputFourColumnFixedWidth.click();
    await this.inputAddSection.click();

    // Add content blocks to sections
    await this.addContentBlocks();
  }

  async addContentBlocks() {
    // Add content to Section 5
    await this.page.waitForTimeout(1000);
    await this.inputAddBlockSection5First.click();
    await this.inputChooseBlock.click();
    await this.inputAddBlock.click();

    // Add content to Section 4
    await this.page.waitForTimeout(1000);
    await this.inputAddBlockSection4First.click();
    await this.inputChooseBlock.click();
    await this.inputAddBlock.click();

    // Add content to Section 3
    await this.page.waitForTimeout(1000);
    await this.inputAddBlockSection3First.click();
    await this.inputChooseBlock.click();
    await this.inputAddBlock.click();
    await this.page.waitForTimeout(1000);
    await this.inputAddBlockSection3Second.click();
    await this.inputChooseBlock.click();
    await this.inputAddBlock.click();

    // Add content to Section 2
    await this.page.waitForTimeout(1000);
    await this.inputAddBlockSection2First.click();
    await this.inputChooseBlock.click();
    await this.inputAddBlock.click();
    await this.page.waitForTimeout(1000);
    await this.inputAddBlockSection2Second.click();
    await this.inputChooseBlock.click();
    await this.inputAddBlock.click();
    await this.page.waitForTimeout(1000);
    await this.inputAddBlockSection2Third.click();
    await this.inputChooseBlock.click();
    await this.inputAddBlock.click();

    // Add content to Section 1
    await this.page.waitForTimeout(1000);
    await this.inputAddBlockSection1First.click();
    await this.inputChooseBlock.click();
    await this.inputAddBlock.click();
    await this.page.waitForTimeout(1000);
    await this.inputAddBlockSection1Second.click();
    await this.inputChooseBlock.click();
    await this.inputAddBlock.click();
    await this.page.waitForTimeout(1000);
    await this.inputAddBlockSection1Third.click();
    await this.inputChooseBlock.click();
    await this.inputAddBlock.click();
    await this.page.waitForTimeout(1000);
    await this.inputAddBlockSection1Fourth.click();
    await this.inputChooseBlock.click();
    await this.inputAddBlock.click();
  }

  async save() {
    await this.inputSave.click();
  }

  async verify() {
    // NOTE: This highlights just how odd our layout markup is, really not good or consistent at all
    await expect(this.elLayout5).toHaveClass(
      'bg gray-1-bg bg-bottom bg-percent-75 layout__full-width',
    );
    await expect(this.elLayout4).toHaveClass(
      'bg-top bg-percent-100 max-size-container center-container bg-white',
    );
    await expect(this.elLayout3).toHaveCount(2);
    await expect(this.elLayout2).toHaveCount(3);
    await expect(this.elLayout1).toHaveCount(4);
  }
}

export class Search {
  constructor(page, name) {
    this.page = page;
    this.name = name;
    this.query = faker.lorem.words();

    this.inputSearch = page.getByPlaceholder('Search asu.edu');
    this.inputSearchButton = page.getByTestId('search-button');
    this.elSearchHeading = page
      .getByRole('heading', { name: 'Search' })
      .locator('span');
  }

  async add() {
    await this.page.goto('/');
    await this.inputSearchButton.click();
    await this.inputSearch.fill(this.query);
    await this.inputSearch.press('Enter');
  }

  async verify() {
    await expect(this.elSearchHeading).toBeVisible();
    await expect(this.inputSearch).toHaveValue(this.query);
  }
}

export class Example {
  constructor(page, name) {
    this.page = page;
    this.name = name;
  }
  async add() {}
  async save() {}
  async verify() {}
}
