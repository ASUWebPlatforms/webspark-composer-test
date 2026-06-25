import { expect } from '@playwright/test'
import { faker } from '@faker-js/faker/locale/en'
import drupal from '../helpers/Drupal'

class Card {
  /**
   * React model for Playwright tests.
   * @param {import('playwright').Page} page
   */
  constructor (page) {
    this.page = page
    this.cardHeading = faker.book.title()
    this.cardContent = faker.lorem.paragraph()
    this.cardCtaText = faker.lorem.words()
    this.cardCtaUrl = faker.internet.url()
    this.cardLinkText = faker.lorem.words()
    this.cardLinkUrl = faker.internet.url()
    this.cardCaptionTitle = faker.lorem.words()
    this.icon = 'Pyramid,ASUAwesome,Shapes,'

    this.inputAddCard = page.getByRole('button', { name: 'Add Card' })
    this.inputSelectTarget = page.getByRole('combobox', { name: 'Select a target' })
    this.inputCTAStyle = page.getByRole('combobox', { name: 'Style' })
    this.inputAddCTA = page.getByRole('button', { name: 'Add CTA' })
    this.inputShowBorders = page.getByRole('checkbox', { name: 'Show borders' })
    this.inputCaptionTitle = page.getByRole('textbox', { name: 'Caption title', exact: true })
    this.inputCitation = page.getByRole('textbox', { name: 'Citation' })

    this.elCard = page.locator('.card')
    this.elCardImg = page.getByTestId('card-image')
    this.elCardIcon = page.getByTestId('card-icon')
    this.elCardHeading = page.getByText(this.cardHeading, { exact: true })
    this.elCardContent = page.getByText(this.cardContent, { exact: true })
    this.elCardCta = page.getByRole('link', { name: this.cardCtaText, exact: true })
    this.elCardLink = page.getByRole('link', { name: this.cardLinkText })
    this.elCardImageLink = page.getByRole('link', { name: 'sample image' })
    this.elCardGroupImageImg = page.locator('.ws2-img')
    this.elCardCaption = page.getByText(this.cardCaptionTitle, { exact: true })
    this.elCardRanking = page.locator('.card-ranking')
    this.elCardRakingImg = page.locator('.uds-img')
    this.elCardRankingLink = page.getByRole('link', { name: 'Read more' })
  }

  getCardHeadingInput(i) {
    return this.page.locator(`[data-drupal-selector*="field-cards-${i}-subform-field-heading-0-value"]`)
  }

  getCardContentInput(i) {
    return this.page.locator(`[data-drupal-selector*="field-cards-${i}"]`).getByLabel('Rich Text Editor').getByRole('textbox')
  }

  getCTAURLInput(i) {
    return this.page.locator(`[data-drupal-selector*="field-cards-${i}-subform-field-cta-0-subform-field-cta-link-0-uri"]`)
  }

  getCTATextInput(i) {
    return this.page.locator(`[data-drupal-selector*="field-cards-${i}-subform-field-cta-0-subform-field-cta-link-0-title"]`)
  }

  getCTASecondaryURLInput(i) {
    return this.page.locator(`[data-drupal-selector*="field-cards-${i}-subform-field-cta-secondary-0-subform-field-cta-link-0-uri"]`)
  }

  getCTASecondaryTextInput(i) {
    return this.page.locator(`[data-drupal-selector*="field-cards-${i}-subform-field-cta-secondary-0-subform-field-cta-link-0-title"]`)
  }

  getLinkURLInput(i) {
    return this.page.locator(`[data-drupal-selector*="field-cards-${i}-subform-field-link-0-uri"]`)
  }

  getLinkTextInput(i) {
    return this.page.locator(`[data-drupal-selector*="field-cards-${i}-subform-field-link-0-title"]`)
  }

  getShowBordersInput(i) {
    return this.page.locator(`[data-drupal-selector*="field-cards-${i}-subform-field-show-borders-value"]`)
  }

  getIconWidgetInput(i) {
    return this.page.locator(`[data-drupal-selector*="field-cards-${i}"]`).locator('.fip-icon-down-dir').first()
  }

  getIconInput(i) {
    return this.page.locator(`[data-drupal-selector*="field-cards-${i}"]`).getByTitle(this.icon).first()
  }

  getLoadingInput(i) {
    return this.page.locator(`[data-drupal-selector*="field-cards-${i}-subform-field-loading"]`).getByLabel('Loading')
  }

  getImageSizeInput(i) {
    return this.page.locator(`[data-drupal-selector*="field-cards-${i}-subform-field-card-ranking-image-size"]`).getByLabel('Card Ranking Image Size')
  }

  /**
   * Add a new card group.
   * @returns {Promise<void>}
   */
  async addCardGroup () {
    throw new Error('addCardGroup() must be implemented in the subclass')
  }

  /**
   * Add a new card to the card group.
   * @param {number} i The locator index
   * @returns {Promise<void>}
   */
  async addCard (i = 0) {
    throw new Error('addCard() must be implemented in the subclass')
  }

  /**
   * Add content to the card group.
   * @returns {Promise<void>}
   */
  async addContent (cards = 3) {
    for (let i = 0; i < cards; i++) {
      await this.addCard(i)
      if (i < cards - 1) {
        await drupal.waitForAjax(this.page, this.inputAddCard)
      }
    }
  }

  /**
   * Verify the card group via tests.
   * @returns {Promise<void>}
   */
  async verify () {
    throw new Error('verify() must be implemented in the subclass')
  }
}

export { Card }
