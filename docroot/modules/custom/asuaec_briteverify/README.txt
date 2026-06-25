asuaec_briteverify
==================

Usage:

1. Enable module "ASU AEC BriteVerify Webform Validation".

2. /admin/config/asuaec-briteverify -- BriteVerify Settings: Enter BriteVerify API key.

3. Find out machine names for email address/phone number Webform components. go to Webform Build area. Keys are the machine names.

4. To a Webform, add Webform handler called "BriteVerify Email & Phone Validation"
    Webform -> Settings -> Email/Handlers -> + Add handler -> Add handler for "BriteVerify Email & Phone Validation"

5. For email validation, in handler configuration, add the machine names for the email addresses under "Email field machine names". We can add up to 4 components.

6. For phone number validation, please use Telephone type field because Brite Verify only works for US/Canada "1" numbers. In handler configuration, enter up to 4 phone field keys in the format "phone_key|service_type_key", one per line. "mobile" or "land" will be added to service_type field based on BriteVerify's response.

For convenience, CSS is the following:
/* Fix Phone number field */
.iti--allow-dropdown input, .iti--allow-dropdown input[type=tel] {
  padding-left: 52px !important;
}

7. Test the form. - In "Recent log messages"(/admin/reports/dblog) area, it will print what was validated by Brite Verify.


