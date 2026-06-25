import { expect } from '@playwright/test'
import { faker } from '@faker-js/faker/locale/en'
import { Node } from './Node'
import drupal from '../helpers/Drupal'

class DegreeDetail extends Node {
  /**
   * Degree Detail model for Playwright tests.
   * @param {import('playwright').Page} page
   * @param {string} name
   */
  constructor (page, name) {
    super(page, name)
    this.degreeURL = null
    this.title = 'Playwright Computer Gaming'
    this.offeredByText = faker.company.name()
    this.offeredByUrl = faker.internet.url()
    this.locationText = faker.location.city()
    this.locationUrl = faker.internet.url()
    this.firstRequirementMath = faker.lorem.words(3)
    this.mathIntensity = faker.lorem.words(2)
    this.timeCommitment = faker.lorem.words(2)
    this.cardIcon = 'star'
    this.cardTitle = faker.lorem.words(3)
    this.cardContent = faker.lorem.sentence()
    this.cardLinkText = faker.lorem.words(2)
    this.cardLinkUrl = faker.internet.url()
    this.whyChooseContent = faker.lorem.paragraph()
    this.programDepartmentUrl = faker.internet.url()
    this.programDepartmentEmail = faker.internet.email()
    this.externalAnchorText = faker.lorem.words(2)

    this.inputTitle = page.getByRole('textbox', { name: 'Title *', exact: true })
    this.inputOfferedByUrl = page.getByRole('group', { name: 'Offered by link' }).getByLabel('URL')
    this.inputOfferedByText = page.getByRole('group', { name: 'Offered by link' }).getByLabel('Link text')
    this.inputLocationUrl = page.locator('#edit-field-degree-detail-locations-0-uri')
    this.inputLocationText = page.locator('#edit-field-degree-detail-locations-0-title')
    this.inputFirstRequirementMath = page.getByRole('textbox', { name: 'First requirement math course', exact: true })
    this.inputMathIntensity = page.getByRole('textbox', { name: 'Math intensity', exact: true })
    this.inputTimeCommitment = page.getByRole('textbox', { name: 'Time commitment', exact: true })
    this.inputAddNextStepsCard = page.getByRole('button', { name: 'Add Degree details next steps card' })
    this.inputCardIcon = page.getByRole('textbox', { name: 'Card icon', exact: true })
    this.inputCardTitle = page.getByRole('textbox', { name: 'Card title', exact: true })
    this.inputCardContent = page.getByRole('textbox', { name: 'Card content', exact: true })
    this.inputNextStepsUrl = page.getByRole('cell', { name: 'Degree details next steps' }).getByLabel('URL')
    this.inputNextStepsText = page.getByRole('cell', { name: 'Degree details next steps' }).getByLabel('Link text')
    this.inputCardButtonColor = page.getByRole('combobox', { name: 'Card button link color' })
    this.inputWhyChooseContent = page.getByLabel('Rich Text Editor').getByRole('textbox').nth(1)
    this.inputAddWhyChoose = page.getByRole('button', { name: 'Add Degree Details Why Choose' }).nth(0)
    this.inputWhyChooseCardTitle = page.getByRole('textbox', { name: 'Card title *', exact: true })
    this.inputWhyChooseCardContent = page.getByRole('textbox', { name: 'Card content *', exact: true })
    this.inputWhyChooseUrl = page.getByRole('cell', { name: 'Degree Details Why Choose' }).getByLabel('URL')
    this.inputWhyChooseText = page.getByRole('cell', { name: 'Degree Details Why Choose' }).getByLabel('Link text')
    this.inputProgramDepartmentUrl = page.getByRole('textbox', { name: 'Program department URL', exact: true })
    this.inputProgramDepartmentEmail = page.getByRole('textbox', { name: 'Program department email link', exact: true })
    this.inputAtAGlance = page.getByRole('checkbox', { name: 'At a glance', exact: true })
    this.inputApplicationRequirements = page.getByRole('checkbox', { name: 'Application requirements', exact: true })
    this.inputChangeMajorRequirements = page.getByRole('checkbox', { name: 'Change major requirements', exact: true })
    this.inputNextSteps = page.getByRole('checkbox', { name: 'Next steps', exact: true })
    this.inputAffordingCollege = page.getByRole('checkbox', { name: 'Affording college', exact: true })
    this.inputCareerOutlook = page.getByRole('checkbox', { name: 'Career outlook', exact: true })
    this.inputWhyChooseASU = page.getByRole('checkbox', { name: 'Why Choose ASU', exact: true })
    this.inputExampleCareers = page.getByRole('checkbox', { name: 'Example careers', exact: true })
    this.inputCustomizeExperience = page.getByRole('checkbox', { name: 'Customize your college experience', exact: true })
    this.inputGlobalOpportunity = page.getByRole('checkbox', { name: 'Global opportunity', exact: true })
    this.inputAttendOnline = page.getByRole('checkbox', { name: 'Attend online', exact: true })
    this.inputProgramContactInfo = page.getByRole('checkbox', { name: 'Program contact info', exact: true })
    this.inputExternalAnchorUrl = page.locator('[data-drupal-selector*="field-deg-dtl-anchor-addl-anchor-0-uri"]')
    this.inputExternalAnchorText = page.locator('[data-drupal-selector*="field-deg-dtl-anchor-addl-anchor-0-title"]')
    this.inputHideMarketText = page.getByRole('checkbox', { name: 'Hide market text (includes custom Intro content)', exact: true })
    this.inputHideProgramDescription = page.getByRole('checkbox', { name: 'Hide program description', exact: true })
    this.inputHideRequiredCourses = page.getByRole('checkbox', { name: 'Hide required courses', exact: true })
    this.inputHideApplicationRequirements = page.getByRole('checkbox', { name: 'Hide application requirements', exact: true })
    this.inputHideChangeMajorRequirements = page.getByRole('checkbox', { name: 'Hide change major requirements', exact: true })
    this.inputHideAffordingCollege = page.getByRole('checkbox', { name: 'Hide affording college', exact: true })
    this.inputHideFlexibleDegreeOptions = page.getByRole('checkbox', { name: 'Hide flexible degree options', exact: true })
    this.inputHideExampleCareers = page.getByRole('checkbox', { name: 'Hide example careers', exact: true })
    this.inputHideGlobalOpportunity = page.getByRole('checkbox', { name: 'Hide global opportunity', exact: true })
    this.inputHideWhyChooseASU = page.getByRole('checkbox', { name: 'Hide Why Choose ASU', exact: true })
    this.inputHideAttendOnline = page.getByRole('checkbox', { name: 'Hide attend online', exact: true })

    this.elComputerGaming = page.getByRole('link', { name: 'Computer Gaming', exact: true })
    this.elOfferedByText = page.getByText(this.offeredByText, { exact: true })
    this.elLocationText = page.getByText(this.locationText, { exact: true })
    this.elCardTitle = page.getByText(this.cardTitle, { exact: true })
    this.elCardContent = page.getByText(this.cardContent, { exact: true })
    this.elWhyChooseContent = page.getByText(this.whyChooseContent)
    this.elExternalAnchorText = page.getByText(this.externalAnchorText, { exact: true })
  }

  /**
   * Add a new degree detail node.
   * @returns {Promise<void>}
   */
  async add () {
    await this.page.goto('/playwright-degree-listing')
    await this.elComputerGaming.click()
    await this.setNodeProperties()
    this.degreeURL = this.page.url()
  }

  /**
   * Add content to the degree detail node.
   * @returns {Promise<void>}
   */
  async addContent () {
    await this.inputTitle.fill(this.title)
    await this.inputOfferedByUrl.fill(this.offeredByUrl)
    await this.inputOfferedByText.fill(this.offeredByText)
    await this.inputLocationUrl.fill(this.locationUrl)
    await this.inputLocationText.fill(this.locationText)
    await this.inputFirstRequirementMath.fill(this.firstRequirementMath)
    await this.inputMathIntensity.fill(this.mathIntensity)
    await this.inputTimeCommitment.fill(this.timeCommitment)
    await drupal.addMediaField(this.page, 2)
    await drupal.addMediaField(this.page, 3)
    await this.inputAddNextStepsCard.click()
    await this.page.waitForTimeout(3000)
    await this.inputCardIcon.fill(this.cardIcon)
    await this.inputCardTitle.fill(this.cardTitle)
    await this.inputCardContent.fill(this.cardContent)
    await this.inputNextStepsUrl.fill(this.cardLinkUrl)
    await this.inputNextStepsText.fill(this.cardLinkText)
    await this.inputCardButtonColor.selectOption({ label: 'maroon' })
    await this.inputWhyChooseContent.fill(this.whyChooseContent)
    await this.inputAddWhyChoose.click()
    await this.page.waitForTimeout(3000)
    await drupal.addMediaField(this.page, 4)
    await this.inputWhyChooseCardTitle.fill(this.cardTitle)
    await this.inputWhyChooseCardContent.fill(this.cardContent)
    await this.inputWhyChooseUrl.fill(this.cardLinkUrl)
    await this.inputWhyChooseText.fill(this.cardLinkText)
    await this.page.waitForTimeout(3000)
    await drupal.addMediaField(this.page, 5)
    await this.inputProgramDepartmentUrl.fill(this.programDepartmentUrl)
    await this.inputProgramDepartmentEmail.fill(this.programDepartmentEmail)
    await this.inputAtAGlance.setChecked(true)
    await this.inputApplicationRequirements.setChecked(true)
    await this.inputChangeMajorRequirements.setChecked(true)
    await this.inputNextSteps.setChecked(true)
    await this.inputAffordingCollege.setChecked(true)
    await this.inputCareerOutlook.setChecked(true)
    await this.inputWhyChooseASU.setChecked(true)
    await this.inputExampleCareers.setChecked(true)
    await this.inputCustomizeExperience.setChecked(true)
    await this.inputGlobalOpportunity.setChecked(true)
    await this.inputAttendOnline.setChecked(true)
    await this.inputProgramContactInfo.setChecked(true)
    await this.inputExternalAnchorUrl.fill('<nolink>')
    await this.inputExternalAnchorText.fill(this.externalAnchorText)
    await this.inputHideMarketText.setChecked(true)
    await this.inputHideProgramDescription.setChecked(true)
    await this.inputHideRequiredCourses.setChecked(true)
    await this.inputHideApplicationRequirements.setChecked(true)
    await this.inputHideChangeMajorRequirements.setChecked(true)
    await this.inputHideAffordingCollege.setChecked(true)
    await this.inputHideFlexibleDegreeOptions.setChecked(true)
    await this.inputHideExampleCareers.setChecked(true)
    await this.inputHideGlobalOpportunity.setChecked(true)
    await this.inputHideWhyChooseASU.setChecked(true)
    await this.inputHideAttendOnline.setChecked(true)
  }

  /**
   * Verify the node.
   * @returns {Promise<void>}
   */
  async verify () {
    await this.page.goto(this.degreeURL)
    await this.page.waitForLoadState()
    const props = await this.page.evaluate(() => {
      return window.drupalSettings.asu_degree_rfi.program_detail_page
    })

    await expect(props).toBeDefined()
    expect(typeof props).toBe('object')
    await expect(props.atAGlance.offeredBy.text).toBe(this.offeredByText)
    await expect(props.atAGlance.offeredBy.url).toBe(this.offeredByUrl)
    await expect(props.atAGlance.locations[0].text).toBe(this.locationText)
    await expect(props.atAGlance.locations[0].url).toBe(this.locationUrl)
    await expect(props.atAGlance.firstRequirementMathCourse).toBe(this.firstRequirementMath)
    await expect(props.atAGlance.mathIntensity).toBe(this.mathIntensity)
    await expect(props.atAGlance.timeCommitment).toBe(this.timeCommitment)
    await expect(props.careerOutlook.image.altText).toBe('sample image')
    await expect(props.globalOpportunity.image.altText).toBe('sample image')
    await expect(props.nextSteps.cards.learnMore.title).toBe(this.cardTitle)
    await expect(props.nextSteps.cards.learnMore.icon[1]).toBe(this.cardIcon)
    await expect(props.nextSteps.cards.learnMore.content).toBe(this.cardContent)
    await expect(props.nextSteps.cards.learnMore.buttonLink.label).toBe(this.cardLinkText)
    await expect(props.nextSteps.cards.learnMore.buttonLink.href).toBe(this.cardLinkUrl)
    await expect(props.nextSteps.cards.learnMore.buttonLink.color).toBe('maroon')
    await expect(props.whyChooseAsu.sectionIntroText).toContain(this.whyChooseContent)
    await expect(props.whyChooseAsu.cards.faculty.title).toBe(this.cardTitle)
    await expect(props.whyChooseAsu.cards.faculty.image.altText).toBe('sample image')
    await expect(props.whyChooseAsu.cards.faculty.text).toBe(this.cardContent)
    await expect(props.whyChooseAsu.cards.faculty.button.label).toBe(this.cardLinkText)
    await expect(props.whyChooseAsu.cards.faculty.button.href).toBe(this.cardLinkUrl)
    await expect(props.whyChooseAsu.cards.faculty.button.color).toBe('maroon')
    await expect(props.attendOnline.image.altText).toBe('sample image')
    await expect(props.programContactInfo.departmentUrl).toBe(this.programDepartmentUrl)
    await expect(props.programContactInfo.emailUrl).toBe(this.programDepartmentEmail)
    await expect(props.anchorMenu.atAGlance).toBe(true)
    await expect(props.anchorMenu.applicationRequirements).toBe(true)
    await expect(props.anchorMenu.changeMajorRequirements).toBe(true)
    await expect(props.anchorMenu.nextSteps).toBe(true)
    await expect(props.anchorMenu.affordingCollege).toBe(true)
    await expect(props.anchorMenu.careerOutlook).toBe(true)
    await expect(props.anchorMenu.customizeYourCollegeExperience).toBe(true)
    await expect(props.anchorMenu.exampleCareers).toBe(true)
    await expect(props.anchorMenu.globalOpportunity).toBe(true)
    await expect(props.anchorMenu.whyChooseAsu).toBe(true)
    await expect(props.anchorMenu.attendOnline).toBe(true)
    await expect(props.anchorMenu.programContactInfo).toBe(true)
    await expect(props.anchorMenu.externalAnchors[0].text).toBe(this.externalAnchorText)
    await expect(props.affordingCollege.hide).toBe(true)
    await expect(props.applicationRequirements.hide).toBe(true)
    await expect(props.changeMajorRequirements.hide).toBe(true)
    await expect(props.exampleCareers.hide).toBe(true)
    await expect(props.flexibleDegreeOptions.hide).toBe(true)
    await expect(props.introContent.hideMarketText).toBe(true)
    await expect(props.introContent.hideProgramDesc).toBe(true)
    await expect(props.introContent.hideRequiredCourses).toBe(true)
    await expect(props.attendOnline.hide).toBe(true)
    await expect(props.whyChooseAsu.hide).toBe(true)
    await expect(props.globalOpportunity.hide).toBe(true)
  }
}

export { DegreeDetail }
