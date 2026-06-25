import { expect } from '@playwright/test'
import { faker } from '@faker-js/faker/locale/en'
import { Block } from './Block'
import drupal from '../helpers/Drupal'

export class DegreeRfi extends Block {
  constructor (page, name) {
    super(page, name)

    this.rfiVariant = 'rfiVariant1'
    this.campusType = 'ONLNE' // Spelled incorrectly on purpose
    this.studentStatus = 'First Time Freshman'
    this.areaOfInterest = 'Business'
    this.programOfInterest = 'UGBA-BAACCBS'
    this.emailAddress = faker.internet.email()
    this.firstName = faker.person.firstName()
    this.lastName = faker.person.lastName()
    this.phoneNumber = faker.phone.number('+1 (555) ###-####')

    this.inputRfiFormType = page.getByLabel('RFI form type variation')
    this.inputTestMode = page.getByRole('checkbox', { name: 'Run in test mode' })
    this.inputCampusType = page.getByLabel(' Which applies to you?')
    this.inputStudentStatus = page.getByLabel('Select your student status')
    this.inputAreaOfInterest = page.getByLabel('Area of interest')
    this.inputProgramOfInterest = page.getByLabel('Program of interest')
    this.inputEmailAddress = page.getByRole('textbox', { name: 'Email Address' })
    this.inputFirstName = page.getByRole('textbox', { name: 'First name' })
    this.inputLastName = page.getByRole('textbox', { name: 'Last name' })
    this.inputPhoneNumber = page.getByRole('textbox', { name: '1 (702) 123-' })
    this.inputConsent = page.getByText('I consent', { exact: true })

    this.elRfiForm = page.locator('.degree-rfi-form')
    this.elFormData = page.getByText('{ "values": { "Campus":')
    this.elEmailField = page.getByText(this.emailAddress)
    this.elFirstNameField = page.getByText(this.firstName)
    this.elLastNameField = page.getByText(this.lastName)
  }

  async add () {
    await this.inputAddBlock.click()
    await this.inputAddByName.click()
  }

  async addContent () {
    await this.inputRfiFormType.selectOption(this.rfiVariant)
    await this.inputTestMode.check()
  }

  async save () {
    await this.inputSaveBlock.click()
    await this.page.waitForTimeout(3000)
    await this.inputSaveLayout.click()
  }

  async verify () {
    await this.page.waitForLoadState()
    const props = await this.page.evaluate(() => {
      return window.drupalSettings.asu_degree_rfi.props
    })

    await expect(props).toBeDefined()
    expect(typeof props).toBe('object')
    await expect(props.test).toBe(1)

    // Complete the form
    await this.page.waitForTimeout(3000)
    await this.inputCampusType.selectOption(this.campusType)
    await this.page.waitForTimeout(1000)
    await this.inputStudentStatus.selectOption(this.studentStatus)
    await this.inputAreaOfInterest.selectOption(this.areaOfInterest)
    await this.inputProgramOfInterest.selectOption(this.programOfInterest)
    await this.inputEmailAddress.fill(this.emailAddress)
    await this.inputFirstName.fill(this.firstName)
    await this.inputLastName.fill(this.lastName)
    await this.inputPhoneNumber.fill(this.phoneNumber)
    await this.inputConsent.click()

    // Intercept the form and verify data
    const textContent = await this.elFormData.textContent()
    const data = JSON.parse(textContent)
    expect(data.values.Campus).toBe(this.campusType)
    expect(data.values.CareerAndStudentType).toBe(this.studentStatus)
    expect(data.values.Interest1).toBe(this.areaOfInterest)
    expect(data.values.Interest2).toBe(this.programOfInterest)
    expect(data.values.EmailAddress).toBe(this.emailAddress)
    expect(data.values.FirstName).toBe(this.firstName)
    expect(data.values.LastName).toBe(this.lastName)
    expect(data.values.ZipCode).toBe('__NA__')
    expect(data.values.EntryTerm).toBe('__NA__')
    expect(data.values.GdprConsent).toBe(true)
    expect(data.values.MilitaryStatus).toBe('None')
  }
}
