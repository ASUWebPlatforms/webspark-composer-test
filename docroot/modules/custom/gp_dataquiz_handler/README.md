## CONTENTS OF THIS FILE

- Introduction
- Requirements
- Installation
- Configuration


## INTRODUCTION
The Dataquiz handler module provides custom submission messages based on tagging of radio elements in a webform. Radio elements can be tagged with End States. When the user submits the form, the end state that corresponds to their answers will appear in the confirmation message.

## REQUIREMENTS

This module requires Webform and its submodule, Webform UI. 


## INSTALLATION

1. Place this module directory in web/modules. Suggest placing it in a "custom" subdirectory, i.e. `web/modules/custom/gp_dataquiz_handler`.

2. Enable the module in 'Extend' (`/admin/modules`), or using drush `drush en gp_dataquiz_handler`.


## CONFIGURATION

### 1. Creating and populating the data taxonomies

When you install this module, two taxonomies are created: end_states and transaction_type. They are also prepopulated with some default values. Add descriptions to these taxonomy terms and add to or edit them as you like. 

### 2. Configure the Dataquiz Handler module to use your taxonomies:

You must configure the module to use the taxonomy terms that exist on your site. Visit `/admin/config/system/gp_dataquiz_handler` and select the two taxonomies created in step 1.

### 3. Creating and configuring the form

- Create a webform by visiting `/admin/structure/webform` and clicking "Add webform."
- Enable the dataquiz handler by visiting `/admin/structure/webform/manage/MACHINE NAME OF YOUR FORM HERE/handlers`  Click “Add handler.” If this module is enabled there should be a handler called Dataquiz you can select.
- Visit /settings/confirmation for your form (ex: `/admin/structure/webform/manage/datatree_form/settings/confirmation`) and set the "Confirmation type" for the form to "Inline." You can also add a general confirmation message here. It will appear before the end states information in the confirmation message.


### 4. Populate the Webform with questions.
Only Radio elements can be tagged with End States. All other element types should behave as normal, and will not have an impact on the custom confirmation message provided by this module.

When you click “Add element” on a page, select the Radios type. Configure the question as follows:
- Options: General> Yes/No
- Enter the question itself in the “Description” field.
- Add help (optional) to the “Help text” field.
- Under “Form display” chose:
  - Title display: None
  - Description display: Before element
  - Help display: After element
  - Under “Form Validation” choose REQUIRED. All questions on the datatree form are/should be required.


#### Tagging radio elements

Only Radios elements can be tagged with end states. All of these custom settings appear in the “Dataquiz Properties” fieldset when you are creating/editing a radio element if this data handler is active on the form.

- End State: The “End State” field is the classification that will be applied if the user answers YES to the question. Select one from the dropdown.
- End state negative: This field is the classification that will be applied if the user answers NO to the question. Select one from the dropdown.

### 4. Configure the form to conditionally show questions
This functionality is provided by Webform and not by this module, but a basic setup is documented below. 

#### Add a wizard page:
Each question on the datatree form is on its own page. This is how we get questions to show only one at a time and also how we show and hide questions based on previous answers.

At the top of the “build” tab of your webform, click “Add page.” Give the page a unique title and hit “Save and add element” to move on to adding a question to the page.

The appearance of questions is controlled by rules put on the wizard page that encloses it.

Open the page element you wish to edit and go to the “Conditions” tag. Here you can add rules for when the page (and its question) is shown.

#### Rules of thumb:
- All questions are set as “required”, but will only actually be shown/required if the enclosing page “state” is “visible.” In this way we use visibility to control which questions are shown/required.
- Question order on the “build” page (ex: `/admin/structure/webform/manage/dataquiz_test`) does matter. Visibility rules only work if the element that triggers visibility is BEFORE it in the form.


