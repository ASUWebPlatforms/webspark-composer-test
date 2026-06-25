import { expect } from '@playwright/test'
import { faker } from '@faker-js/faker/locale/en'
import drupal from '../helpers/Drupal'
import { Card } from './Card'

export class CardGroupDefault extends Card {
  constructor (page) {
    super(page)
    this.inputAddCardGroupDefault = page.getByRole('button', { name: 'Add Card Group Default' })
  }

  async addCardGroup () {
    await this.inputAddCardGroupDefault.click()
  }

  async addCard (i = 0) {
    await drupal.addMediaField(this.page, i)
    await this.getCardHeadingInput(i).fill(this.cardHeading)
    await this.getCardContentInput(i).fill(this.cardContent)
    await drupal.waitForAjax(this.page, this.inputAddCTA.first())
    await this.getCTAURLInput(i).fill(this.cardCtaUrl)
    await this.getCTATextInput(i).fill(this.cardCtaText)
    await this.inputSelectTarget.nth(i).selectOption({ label: 'New window (_blank)' })
    await this.inputCTAStyle.nth(i).selectOption({ label: 'Maroon' })
    await drupal.waitForAjax(this.page, this.inputAddCTA.first())
    await this.getCTASecondaryURLInput(i).fill(this.cardCtaUrl)
    await this.getCTASecondaryTextInput(i).fill(this.cardCtaText)
    await this.inputSelectTarget.nth(i + 1).selectOption({ label: 'New window (_blank)' })
    await this.inputCTAStyle.nth(i + 1).selectOption({ label: 'Gold' })
    await this.getLinkURLInput(i).fill(this.cardLinkUrl)
    await this.getLinkTextInput(i).fill(this.cardLinkText)
    await this.getShowBordersInput(i).check()
  }

  async verify () {
    await expect(this.elCardImg.first()).toBeVisible()
    await expect(this.elCardHeading.first()).toBeVisible()
    await expect(this.elCardContent.first()).toBeVisible()
    await expect(this.elCardCta.first()).toBeVisible()
    await expect(this.elCardCta.first()).toHaveClass(/btn-maroon/)
    await expect(this.elCardCta.first()).toHaveAttribute('href', this.cardCtaUrl)
    await expect(this.elCardCta.first()).toHaveAttribute('target', '_blank')
    await expect(this.elCardCta.nth(1)).toBeVisible()
    await expect(this.elCardLink.first()).toBeVisible()
    await expect(this.elCardLink.first()).toHaveAttribute('href', this.cardLinkUrl)
  }
}

export class CardGroupDegree extends Card {
  constructor (page) {
    super(page)
    this.inputAddCardGroupDegree = page.getByRole('button', { name: 'Add Card Group Degree' })
  }

  async addCardGroup () {
    await this.inputAddCardGroupDegree.click()
  }

  async addCard (i = 0) {
    await drupal.addMediaField(this.page, i)
    await this.getCardHeadingInput(i).fill(this.cardHeading)
    await this.getCardContentInput(i).fill(this.cardContent)
  }

  async verify () {
    await expect(this.elCard.first()).toHaveClass(/card-degree/)
  }
}

export class CardGroupIcon extends Card {
  constructor (page) {
    super(page)
    this.inputAddCardGroupIcon = page.getByRole('button', { name: 'Add Card Group with Icon' })
  }

  async addCardGroup () {
    await this.inputAddCardGroupIcon.click()
  }

  async addCard (i = 0) {
    await this.getCardHeadingInput(i).fill(this.cardHeading)
    await this.getCardContentInput(i).fill(this.cardContent)
    await this.getIconWidgetInput(i).click()
    await this.getIconInput(i).click()
  }

  async verify () {
    await expect(this.elCardIcon.first()).toBeVisible()
  }
}

export class CardGroupImage extends Card {
  constructor (page) {
    super(page)
    this.inputAddCardGroupImage = page.getByRole('button', { name: 'Add Image based cards' })
  }

  async addCardGroup () {
    await this.inputAddCardGroupImage.click()
  }

  async addCard (i = 0) {
    await drupal.addMediaField(this.page, i)
    await this.getLinkURLInput(i).fill(this.cardLinkUrl)
    await this.getLinkTextInput(i).fill(this.cardLinkText)
    await this.getLoadingInput(i).selectOption({ label: 'Eager' })
    await this.getCardContentInput(i).fill(this.cardContent)
    await this.inputCaptionTitle.fill(this.cardCaptionTitle)
  }

  async verify () {
    await expect(this.elCardImageLink).toHaveAttribute('href', this.cardLinkUrl)
    await expect(this.elCardGroupImageImg).toBeVisible()
    await expect(this.elCardGroupImageImg).toHaveAttribute('loading', 'eager')
    await expect(this.elCardCaption).toBeVisible()
    await expect(this.elCardContent).toBeVisible()
  }
}

export class CardGroupRanking extends Card {
  constructor (page) {
    super(page)
    this.inputAddCardGroupRanking = page.getByRole('button', { name: 'Add Card Group Ranking' })
    this.elCardHeadingBtn = page.getByRole('button', { name: this.cardHeading, exact: true })
  }

  async addCardGroup () {
    await this.inputAddCardGroupRanking.click()
  }

  async addCard (i = 0) {
    // await page.waitForTimeout(1000)
    await this.getCardHeadingInput(i).fill(this.cardHeading)
    await this.getImageSizeInput(i).selectOption({ label: 'Large' })
    await drupal.addMediaField(this.page, i)
    await this.getCardContentInput(i).fill(this.cardContent)
    await this.getLinkURLInput(i).fill(this.cardLinkUrl)
  }

  async verify () {
    await expect(this.elCardRanking).toHaveClass(/large-image/)
    await expect(this.elCardRakingImg).toHaveCount(1)
    await expect(this.elCardHeadingBtn).toBeVisible()
    await expect(this.elCardContent).toBeHidden()
    await expect(this.elCardRankingLink).toBeHidden()
    await this.elCardHeadingBtn.click()
    await expect(this.elCardContent).toBeVisible()
    await expect(this.elCardRankingLink).toBeVisible()
    await expect(this.elCardRankingLink).toHaveAttribute('href', this.cardLinkUrl)
  }
}

export class CardGroupStory extends Card {
  constructor (page) {
    super(page)
    this.inputAddCardGroupStory = page.getByRole('button', { name: 'Add Card Group Story' })
  }

  async addCardGroup () {
    await this.inputAddCardGroupStory.click()
  }

  async addCard (i = 0) {
    await drupal.addMediaField(this.page, i)
    await this.getCardHeadingInput(i).fill(this.cardHeading)
    await this.getCardContentInput(i).fill(this.cardContent)
  }

  async verify () {
    await expect(this.elCard.first()).toHaveClass(/card-story/)
  }
}
