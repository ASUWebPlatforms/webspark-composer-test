import { expect } from '@playwright/test'
import { faker } from '@faker-js/faker/locale/en'
import { Node } from './Node'
import drupal from '../helpers/Drupal'

class DegreeListing extends Node {
  /**
   * Degree Listing model for Playwright tests.
   * @param {import('playwright').Page} page
   * @param {string} name
   */
  constructor (page, name) {
    super(page, name)
    this.title = `Playwright ${this.name}`
    this.content = faker.lorem.sentence()
    this.introTitle = faker.lorem.words(2)
    this.introContent = faker.lorem.paragraph()
    this.heroTitle = faker.lorem.words(3)
    this.linkText = faker.lorem.words(2)
    this.linkUrl = faker.internet.url()
    this.applyNowUrl = faker.internet.url()
    this.excludeFromDisplay = 'BAACCBS'

    this.inputTitle = page.getByRole('textbox', { name: 'Title *', exact: true })
    this.inputIntroTitle = page.getByRole('textbox', { name: 'Intro title *', exact: true })
    this.inputProgram = page.getByRole('combobox', { name: 'Program *' })
    this.inputIntroContent = page.getByLabel('Rich Text Editor').getByRole('textbox')
    this.inputCollege = page.getByRole('combobox', { name: 'College' })
    this.inputCertificatesAndMinors = page.getByRole('checkbox', { name: 'Certificates and Minors', exact: true })
    this.inputShowFilters = page.getByRole('checkbox', { name: 'Show filters', exact: true })
    this.inputShowSearch = page.getByRole('checkbox', { name: 'Show search', exact: true })
    this.inputHideCollegeSchool = page.getByRole('checkbox', { name: 'Hide College/School column', exact: true })
    this.inputDegreesPerPage = page.getByRole('combobox', { name: 'Degrees per page *' })
    this.inputHeroSize = page.getByRole('combobox', { name: 'Hero size' })
    this.inputHeroTitle = page.getByRole('textbox', { name: 'Hero title', exact: true })
    this.inputHeroTitleColor = page.getByRole('combobox', { name: 'Hero title highlight color' })
    this.inputLinkUrl = page.getByRole('textbox', { name: 'URL', exact: true })
    this.inputLinkText = page.getByRole('textbox', { name: 'Link text' })
    this.inputIntroType = page.getByRole('combobox', { name: 'Intro type *' })
    this.inputApplyNowUrl = page.getByRole('textbox', { name: 'Apply now URL', exact: true })
    this.inputExcludeFromDisplay = page.getByRole('textbox', { name: 'Exclude from display', exact: true })

    this.elTitle = page.getByRole('heading', { name: this.title })
    this.elIntroTitle = page.getByText(this.introTitle, { exact: true })
    this.elIntroContent = page.getByText(this.introContent)
    this.elHeroTitle = page.getByText(this.heroTitle, { exact: true })
    this.elLinkText = page.getByText(this.linkText, { exact: true })
  }

  /**
   * Add a new degree listing node.
   * @returns {Promise<void>}
   */
  async add () {
    await this.page.goto('/node/add/degree_listing_page')
    await this.inputTitle.fill(this.title)
    await this.inputIntroTitle.fill(this.introTitle)
    await this.inputProgram.selectOption({ label: 'Undergraduate' })
    await this.inputIntroContent.fill(this.introContent)
    await this.save()
    await expect(this.status).toHaveClass(/alert-success/)
    await this.setNodeProperties()
  }

  /**
   * Add content to the degree listing node.
   * @returns {Promise<void>}
   */
  async addContent () {
    await this.inputCollege.selectOption({ label: 'Ira A. Fulton Schools of Engineering : CES' })
    await this.inputCertificatesAndMinors.setChecked(true)
    await this.inputShowFilters.setChecked(true)
    await this.inputShowSearch.setChecked(true)
    await this.inputHideCollegeSchool.setChecked(true)
    await this.inputDegreesPerPage.selectOption({ label: '10' })
    await drupal.addMediaField(this.page, 0)
    await drupal.addMediaField(this.page, 1)
    await this.inputHeroSize.selectOption({ label: 'small' })
    await this.inputHeroTitle.fill(this.heroTitle)
    await this.inputHeroTitleColor.selectOption({ label: 'black' })
    await this.inputLinkUrl.fill(this.linkUrl)
    await this.inputLinkText.fill(this.linkText)
    await this.inputIntroType.selectOption({ label: 'text-media' })
    await this.inputApplyNowUrl.fill(this.applyNowUrl)
    await this.inputExcludeFromDisplay.fill(this.excludeFromDisplay)
  }

  /**
   * Verify the node.
   * @returns {Promise<void>}
   */
  async verify () {
    await this.page.goto(this.alias)
    await this.page.waitForLoadState()
    const props = await this.page.evaluate(() => {
      return window.drupalSettings.asu_degree_rfi.degree_listing_page
    })

    await expect(props).toBeDefined()
    expect(typeof props).toBe('object')

    await expect(props.programList.dataSource.collegeAcadOrg).toBe('CES')
    await expect(props.programList.dataSource.program).toBe('undergrad')
    await expect(props.hasFilters).toBe(true)
    await expect(props.hasSearchBar).toBe(true)
    await expect(props.programList.settings.hideCollegeSchool).toBe(true)
    await expect(props.degreesPerPage).toBe(10)
    await expect(props.programList.settings.cardDefaultImage.altText).toBe('sample image')
    await expect(props.hero.image.altText).toBe('sample image')
    await expect(props.hero.image.size).toBe('small')
    await expect(props.hero.title.highlightColor).toBe('black')
    await expect(props.hero.title.text).toBe(this.heroTitle)
    await expect(props.introContent.breadcrumbs[0].url).toBe(this.linkUrl)
    await expect(props.introContent.breadcrumbs[0].text).toBe(this.linkText)
    await expect(props.introContent.contents[0].text).toContain(this.introContent)
    await expect(props.introContent.title.text).toBe(this.introTitle)
    await expect(props.introContent.type).toBe('text-media')
    await expect(props.actionUrls.applyNowUrl).toBe(this.applyNowUrl)
    await expect(props.programList.dataSource.blacklistAcadPlans[0]).toBe(this.excludeFromDisplay)
    // A bit odd sometimes the value is a bool true vs a string true, but egh
    await expect(props.programList.dataSource.cert).toBe('true')
  }
}

export { DegreeListing }
