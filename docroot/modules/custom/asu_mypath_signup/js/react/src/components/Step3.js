import React, { useEffect, useState, useRef } from 'react';
import { useFormContext, Controller, useController } from 'react-hook-form';
import { PhoneInput } from 'react-international-phone';
import axios from 'axios';
import Select from 'react-select';
import './index.css';
import CryptoJS from 'crypto-js';
import { pushToDataLayer } from '../utils/dataLayer';

const Step3 = ({ queryParams, localWebUrl, redirectEnv }) => {
  const {
    register,
    formState: { errors },
    watch,
    setValue,
    setError: setFieldError,
    clearErrors,
  } = useFormContext();
  const [termOptions, setTermOptions] = useState([]);
  const [phoneNumber, setPhoneNumber] = useState('');
  const [queryDegree, setQueryDegree] = useState();
  const [queryCollege, setQueryCollege] = useState();
  const [queryMajor, setQueryMajor] = useState();
  const [queryInst, setQueryInst] = useState();
  const { control, trigger } = useFormContext();

  //get consent text from admin form
  const ds =
    typeof drupalSettings !== 'undefined' && drupalSettings.asu_mypath_signup
      ? drupalSettings.asu_mypath_signup
      : {};
  const desktopConsentText = ds.desktopConsentText || '';
  const mobileConsentText = ds.mobileConsentText || '';
  const consent = desktopConsentText || '';
  //console.log(consent);

  /* const consent =
    "<div class='mypath-consent-wording'><p>By submitting my information, I consent to ASU contacting me about educational services using automated calls, prerecorded voice messages, SMS/text messages or email at the information provided above. Message and data rates may apply. Consent is not required to receive services, and I may call ASU directly at <a href='tel:8443537953'>844-353-7953</a>. I consent to ASU\'s <a href='https://www.asu.edu/about/text-message-terms'>mobile terms and conditions</a>, and <a href='https://www.asu.edu/about/web-analytics-privacy'>Privacy Statements</a>, including the European Supplement and the MyPath2ASU@Admissions Guarantee.</p><p><strong>FERPA Statement:</strong><br />The federal Family Educational Rights and Privacy Act (FERPA) of 1974 protects the privacy of student educational records, including transcripts, by placing certain restrictions on the disclosure of that information. As a result, your written authorization is required in order for the partnering community college and Arizona State University to release your educational records to each other for the purpose of providing admission, credit evaluation, academic counseling, financial aid, and other services. All educational records are released subject to the confidentiality provisions of appropriate state and federal laws and regulation. All information may be retained in the records of both systems.</p><p><strong>Authorization:</strong><br />I authorize the release of my educational records between the partnering community college and Arizona State University without prior notice. This authorization includes my consent for Arizona State University and the partnering community college to disclose and share my education records protected by FERPA between one another for all purposes related to carrying out activities necessary in connection with my status as a prospective, current or former student at each educational institution. I understand that I have the right to revoke this authorization at any time by notifying both institutions in writing of my decision to revoke this authorization. I understand that such revocation will not affect any disclosures previously made before receipt of any such written revocation. If it is appropriate to award an associate degree, my signature below gives permission to the partnering community college to award the degree and notify me of the results without further intervention on my part.</p></div>"; */
  // Watch form fields to trigger validations on input change
  const firstNameValue = watch('first_name');
  const lastNameValue = watch('last_name');
  const emailValue = watch('email');
  const phoneNumValue = watch('phone');
  const zipCodeValue = watch('zip_code');
  const termvalue = watch('termData');

  const rules = {
    required: 'Text is required',
    plainText: {
      message:
        'Text must contain only alphanumeric characters, spaces, and punctuation marks',
      validator: (value) => {
        return /^[a-zA-Z0-9\s\.,!?]*$/.test(value);
      },
    },
  };

  const {
    field: { onChange: onPhoneChange, value: phoneValue },
    fieldState: { phinvalid },
  } = useController({
    name: 'phone',
    rules: { required: 'Phone is required' },
  });

  const {
    field: { onChange: onChangeProgTransferOption, value: progText },
    fieldState: { invalid: invalidstepOption },
  } = useController({
    name: 'progTextProgField',
    //rules: { required: 'Please select an option' }
  });

  const handleFirstNameChange = (event) => {
    // Call register's onChange method to update the form state
    register('first_name').onChange(event);

    // Push data to the data layer
    pushToDataLayer({
      action: 'click',
      event: 'select',
      name: 'onclick',
      region: 'main content',
      section: 'mypath2asu3',
      first_name: event.target.value,
    });
  };

  const handleLastNameChange = (event) => {
    // Call register's onChange method to update the form state
    register('last_name').onChange(event);

    // Push data to the data layer
    pushToDataLayer({
      action: 'click',
      event: 'select',
      name: 'onclick',
      region: 'main content',
      section: 'mypath2asu3',
      last_name: event.target.value,
    });
  };

  // Cache the last BriteVerify result per field so the async `validate`
  // rule (run again by handleSubmit) can reuse a fresh check instead of
  // re-hitting the API for a value that was just verified on change/blur.
  const emailVerifyCacheRef = useRef({ value: null, status: null });
  const phoneVerifyCacheRef = useRef({ value: null, status: null });

  const verifyEmailStatus = async (value) => {
    if (
      emailVerifyCacheRef.current.value === value &&
      emailVerifyCacheRef.current.status !== null
    ) {
      return emailVerifyCacheRef.current.status;
    }
    const url = `${localWebUrl}/asu_mypath_signup/verify?email=${encodeURIComponent(value)}`;
    const response = await axios.post(
      url,
      {},
      { headers: { 'Content-Type': 'application/json' } },
    );
    const status = response.data.status ? response.data.status : '';
    emailVerifyCacheRef.current = { value, status };
    return status;
  };

  const verifyPhoneStatus = async (value) => {
    if (
      phoneVerifyCacheRef.current.value === value &&
      phoneVerifyCacheRef.current.status !== null
    ) {
      return phoneVerifyCacheRef.current.status;
    }
    const phoneurl = `${localWebUrl}/asu_mypath_signup/verify?phone=${encodeURIComponent(value)}`;
    const response = await axios.post(
      phoneurl,
      {},
      { headers: { 'Content-Type': 'application/json' } },
    );
    const status = response.data.status ? response.data.status : '';
    phoneVerifyCacheRef.current = { value, status };
    return status;
  };

  const handleEmailChange = (event) => {
    // Call register's onChange method to update the form state
    register('email').onChange(event);
    //Validate email using BriteVerify API
    const emailValue = event.target.value;
    if (emailValue) {
      verifyEmailStatus(emailValue)
        .then((status) => {
          if (status === 'INVALID') {
            setFieldError('email', {
              type: 'manual',
              message: 'Invalid email address',
            });
          } else {
            clearErrors('email');
          }
        })
        .catch((error) => {
          console.error('Error validating email:', error);
          setFieldError('email', {
            type: 'manual',
            message: 'Error validating email',
          });
        });
    }

    // Push data to the data layer
    pushToDataLayer({
      action: 'click',
      event: 'select',
      name: 'onclick',
      region: 'main content',
      section: 'mypath2asu3',
      email: event.target.value,
    });
  };

  const handleZipCodeChange = (event) => {
    // Call register's onChange method to update the form state
    register('zip_code').onChange(event);

    // Push data to the data layer
    pushToDataLayer({
      action: 'click',
      event: 'select',
      name: 'onclick',
      region: 'main content',
      section: 'mypath2asu3',
      zipcode: event.target.value,
    });
  };

  const handlePhoneBlur = (event) => {
    //Validate phone number on blur using BriteVerify API
    if (phoneValue) {
      verifyPhoneStatus(phoneValue)
        .then((status) => {
          if (status === 'INVALID') {
            setFieldError('phone', {
              type: 'manual',
              message: 'Invalid phone number',
            });
          } else {
            clearErrors('phone');
          }
        })
        .catch((error) => {
          console.error('Error validating phone number:', error);
          setFieldError('phone', {
            type: 'manual',
            message: 'Error validating phone number',
          });
        });
    }

    // Push data to data layer
    const value = event.target.value;
    if (value) {
      pushToDataLayer({
        action: 'click',
        event: 'select',
        name: 'onclick',
        region: 'main content',
        section: 'mypath2asu3',
        phone: event.target.value,
      });
    }
  };

  const {
    field: { onChange: onOnlineProgData, value: onlineProgVal },
  } = useController({
    name: 'onlineProgData',
    //rules: { required: 'Please select an option' }
  });

  const {
    field: { onChange: onCountryChange, value: countryVal },
  } = useController({
    name: 'country',
  });

  //if URl paramters exist which means coming from transfer guide site, then show selected program
  useEffect(() => {
    if (queryParams) {
      //console.log(queryParams);
      const majorData = queryParams.major ? queryParams.major.split('~') : '';
      const online = queryParams.online;
      const urlMajor = majorData[0];
      let queryArray = {};
      queryArray['major'] = queryParams.major;
      queryArray['degree'] = queryParams.degree;
      queryArray['college'] = queryParams.college;
      queryArray['campus'] = queryParams.campus;
      queryArray['institution'] = queryParams.institution;
      queryArray['online'] = queryParams.online;
      queryArray['local'] = queryParams.local;
      // console.log(urlMajor);
      //console.log(online);
      let campusSelected = '';
      if (online === 'N') {
        campusSelected = 'GROUND';
      } else {
        campusSelected = 'ONLNE';
      }
      //console.log(campusSelected);
      //console.log(majorData[0]);

      if (urlMajor) {
        const programDataUrl = `${localWebUrl}/asu_mypath_signup/json/programs/${campusSelected}-${urlMajor}`;

        const domainVal = window.location.hostname;
        let changeSelectionUrl = '';
        //console.log(domainVal);
        if (
          domainVal === 'admission.asu.edu' ||
          domainVal === 'live-admission-asu.ws.asu.edu'
        ) {
          changeSelectionUrl =
            'https://transferguide.apps.asu.edu/app/transfermap';
        } else {
          changeSelectionUrl =
            'https://transferguide-qa.apps.asu.edu/app/transfermap';
        }
        //console.log(changeSelectionUrl);

        axios
          .get(programDataUrl, { timeout: 5000 })
          .then((programDataresponse) => {
            const programData = Object.entries(programDataresponse.data).map(
              ([key, value]) => ({
                value: key,
                label: value,
              }),
            );
            const newqueryParams = new URLSearchParams(queryParams).toString();

            //const external_url = `${redirectEnv}?data=${encrypted}`;
            //const external_url = `https://transferguide-qa.apps.asu.edu/app/transfermap?${newqueryParams}`;
            const external_url = `${changeSelectionUrl}?${newqueryParams}`;
            //console.log(external_url);
            const Proghtml = programData.find(
              (item) => item.value === 'progDetails',
            ).label;
            const html = `<div>${Proghtml}<a href=${external_url}>Change selection</a></div>`;
            if (campusSelected === 'ONLNE') {
              const onlineProg = programData.find(
                (item) => item.value === 'programCode',
              ).label;
              onOnlineProgData(onlineProg);
            } else {
              onOnlineProgData('');
            }
            //const html = programData.map(data => `<div>${data.label}</div>`).join('');
            onChangeProgTransferOption(html);
          })
          .catch((error) => {
            console.error('Error fetching data:', error);
          });
      }
    }
  }, [queryParams]);

  useEffect(() => {
    const termUrl = `${localWebUrl}/asu_mypath_signup/json/term`;
    axios
      .get(termUrl, { timeout: 5000 }) //set timer so the call to the url is limited
      .then((response) => {
        const gettermOptions = Object.entries(response.data).map(
          ([key, value]) => ({
            value: key,
            label: value,
          }),
        );
        setTermOptions(gettermOptions);
      })
      .catch((error) => {
        console.error('Error fetching data:', error);
      });
  }, []);

  return (
    <div className="step3">
      {queryParams ? (
        <h4 className="highlight-gold"> About me</h4>
      ) : (
        <h4 className="highlight-gold">Step 3 of 3</h4>
      )}
      <div className="flexFields">
        {/* First Name */}
        <div className="form-group js-form-item react-form-item form-item form-item-first-name">
          <label className="form-label">
            <span
              title="Required"
              className="fa fa-icon fa-circle uds-field-required"
            />{' '}
            First Name
          </label>
          <input
            id="first_name_field"
            type="text"
            {...register('first_name', {
              required: 'First name is required',
              pattern: {
                value: /^[a-zA-Z0-9\s\.,!?]*$/,
                message: 'Only plain text allowed',
              },
            })}
            value={firstNameValue || ''}
            onChange={handleFirstNameChange}
            className={errors.first_name ? 'error-field' : ''}
            autoComplete="given-name"
          />
          {errors.first_name && (
            <span className="error">{errors.first_name.message}</span>
          )}
        </div>

        {/* Last Name */}
        <div className="form-group js-form-item js-form-item-last-name react-form-item form-item">
          <label className="form-label">
            <span
              title="Required"
              className="fa fa-icon fa-circle uds-field-required"
            ></span>{' '}
            Last Name
          </label>
          <input
            id="last_name_field"
            type="text"
            {...register('last_name', {
              required: 'Last name is required',
              pattern: {
                value: /^[a-zA-Z0-9\s\.,!?]*$/,
                message: 'Only plain text allowed',
              },
            })}
            value={lastNameValue || ''}
            onChange={handleLastNameChange}
            className={errors.last_name ? 'error-field' : ''}
            autoComplete="family-name"
          />
          {errors.last_name && (
            <span className="error">{errors.last_name.message}</span>
          )}
        </div>
      </div>
      {/* Email */}
      <div className="form-group js-form-item react-form-item form-item">
        <label className="form-label">
          <span
            title="Required"
            className="fa fa-icon fa-circle uds-field-required"
          ></span>{' '}
          Email
        </label>
        <input
          id="email_field"
          type="email"
          {...register('email', {
            required: 'Email is required',
            pattern: {
              value: /^\S+@\S+\.\S+$/,
              message: 'Invalid email address',
            },
            validate: async (value) => {
              if (!value) {
                return true;
              }
              try {
                const status = await verifyEmailStatus(value);
                return status !== 'INVALID' || 'Invalid email address';
              } catch (error) {
                return 'Error validating email';
              }
            },
          })}
          value={emailValue || ''}
          onChange={handleEmailChange}
          className={errors.email ? 'error-field' : ''}
          autoComplete="email"
        />
        {errors.email && <span className="error">{errors.email.message}</span>}
      </div>

      <div className="flexFields">
        {/* Phone Number */}
        <div className="form-group js-form-item react-form-item form-item form-item-phone">
          <label className="form-label">
            <span
              title="Required"
              className="fa fa-icon fa-circle uds-field-required"
            ></span>{' '}
            Phone Number
          </label>
          {/* <PhoneInput
                        id="phoneField"
                        defaultCountry="us"
                        value={phoneValue}
                        onChange={handlePhoneChange}
                        className={errors.phone ? 'error-field' : ''}
                        autoComplete="pho-val"
                    /> */}
          <Controller
            name="phone"
            control={control} // This comes from useForm()
            rules={{
              required: 'Phone is required',
              validate: {
                minLength: (value) =>
                  value.length >= 10 ||
                  'Phone number must be at least 10 digits long',
                briteVerify: async (value) => {
                  if (!value) {
                    return true;
                  }
                  try {
                    const status = await verifyPhoneStatus(value);
                    return status !== 'INVALID' || 'Invalid phone number';
                  } catch (error) {
                    return 'Error validating phone number';
                  }
                },
              },
            }}
            render={({ field }) => (
              <PhoneInput
                id="phone-field"
                ref={field.ref}
                defaultCountry="us"
                value={field.value}
                //onChange={field.onChange}
                onChange={(value, meta) => {
                  field.onChange(value); // Update the form state
                  onCountryChange(meta.country.iso2); // Capture detected country
                }}
                onBlur={handlePhoneBlur}
                className={errors.phone ? 'error-field' : ''}
                autoComplete="tel"
              />
            )}
          />
          <span className="react-text-muted">
            Please enter your phone number in the format +1 (123) 456-7890.
          </span>
          {errors.phone && (
            <span className="error">
              <br />
              {errors.phone.message}
            </span>
          )}
        </div>

        {/* Zip Code */}
        <div className="form-group js-form-item react-form-item form-item">
          <label className="form-label">
            <span
              title="Required"
              className="fa fa-icon fa-circle uds-field-required"
            ></span>{' '}
            Zip Code
          </label>
          <input
            id="zipcode_field"
            type="text"
            {...register('zip_code', {
              required: 'Zip code is required',
              pattern: {
                value: /^[A-Za-z0-9][A-Za-z0-9\s-]{2,9}$/,
                message: 'Invalid postal code',
              },
            })}
            value={zipCodeValue || ''}
            onChange={handleZipCodeChange}
            className={errors.zip_code ? 'error-field' : ''}
            autoComplete="postal-code"
          />
          {errors.zip_code && (
            <span className="error">{errors.zip_code.message}</span>
          )}
        </div>
      </div>

      {/* Term Selection */}
      <div className="form-group js-form-item react-form-item form-item">
        <label className="form-label">
          {' '}
          <span
            title="Required"
            className="fa fa-icon fa-circle uds-field-required"
          ></span>{' '}
          When do you anticipate starting?
        </label>
        {/* <Select
                    name="termData"
                    id="termDataField"
                    value={termvalue}
                    placeholder="Select..."
                    //onChange={onTermChange}
                    options={termOptions}
                    //onChange={(option) => setValue('termData', option)}
                    onChange={(option) => {
                      setValue('termData', option);  // Updates the form state with the selected value
                      trigger('termData');           // Manually trigger validation for this field after selection
                    }}
                    className={errors.termData ? 'error-field' : ''}
                    autoComplete="termDataeVal"
                    // Spread the register to make it part of the form validation
                    {...register('termData', { required: 'Please select a term' })}
                /> */}
        <Controller
          name="termData"
          control={control}
          rules={{ required: 'Please select a term' }}
          render={({ field }) => (
            <Select
              {...field}
              id="termDataField"
              options={termOptions}
              placeholder="Select..."
              value={termOptions.find((option) => option.value === field.value)} // Ensures correct value is displayed
              onChange={(selectedOption) => {
                field.onChange(selectedOption.value); // Update the form state
                trigger('termData'); // Manually trigger validation
                pushToDataLayer({
                  action: 'click',
                  event: 'select',
                  name: 'onclick',
                  region: 'main content',
                  section: 'mypath2asu3',
                  start_term: selectedOption.value,
                });
              }}
              className={errors.termData ? 'error-field' : ''}
            />
          )}
        />
        {errors.termData && (
          <span className="error">{errors.termData.message}</span>
        )}
      </div>
      {/* Hidden program data */}
      <input type="hidden" name="progTextProgField" value={progText || ''} />
      {progText ? (
        <div className="programDataFromTransferGuideSite">
          <div
            className="innerProgData"
            dangerouslySetInnerHTML={{ __html: progText }}
          />
        </div>
      ) : (
        <div>
          <div dangerouslySetInnerHTML={{ __html: progText }} />
        </div>
      )}

      <input type="hidden" name="onlineProgData" value={onlineProgVal || ''} />
      <input type="hidden" name="country" value={countryVal || ''} />

      <div
        className="consent form-group"
        dangerouslySetInnerHTML={{ __html: consent }}
      />
    </div>
  );
};

export default Step3;
