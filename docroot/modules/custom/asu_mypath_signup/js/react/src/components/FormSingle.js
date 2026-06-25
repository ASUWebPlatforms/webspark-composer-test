import React, { useState, useEffect, useRef } from 'react';
import { useForm, FormProvider, useFormContext } from 'react-hook-form';
import CryptoJS from 'crypto-js';
import { pushToDataLayer } from '../utils/dataLayer';
// import Step1 from "./Step1";
import Step2 from './Step2';
import Step3 from './Step3';

const Step1 = React.lazy(() => import('./Step1'));
//const Step2 = React.lazy(() => import('./Step2'));
//const Step3 = React.lazy(() => import('./Step3'));

//code for mobile buttons
const useIsMobile = () => {
  const [isMobile, setIsMobile] = useState(window.innerWidth <= 992);

  useEffect(() => {
    const handleResize = () => {
      setIsMobile(window.innerWidth <= 768); // Mobile if width is <= 768px
    };

    window.addEventListener('resize', handleResize);

    return () => {
      window.removeEventListener('resize', handleResize);
    };
  }, []);

  return isMobile;
};

const MultiStepForm = () => {
  const methods = useForm();
  const isMobile = useIsMobile();
  const {
    handleSubmit,
    register,
    setValue,
    formState: { errors },
    watch,
  } = methods;
  const [step, setStep] = useState(1);
  const maxSteps = 3;
  const [step2FieldValue, setStep2FieldValue] = useState('');
  const [isStep2ValueChanged, setIsStep2ValueChanged] = useState(false);
  const [isStep1Valid, setIsStep1Valid] = useState(false); // Track Step 1 form field validity
  const [queryParams, setQueryParams] = useState(null); //get url parameters if any
  const [urlParamsExist, seturlParamsExist] = useState(false);
  const baseUrl = window.location.origin;
  const [redirectEnv, setRedirectEnv] = useState(null);
  const [isButtonDisabled, setIsButtonDisabled] = useState(false);
  const [isNextButtonDisabled, setNextIsButtonDisabled] = useState(false);
  const [midStatus, setMidStatus] = useState({
    isRequired: false,
    isValid: true,
    isChecking: false,
    isMaricopa: false,
  });
  const topRef = useRef(null);

  const [isFormLoading, setIsFormLoading] = useState(true);

  useEffect(() => {
    // Simulate loading
    const timer = setTimeout(() => {
      setIsFormLoading(false);
    }, 200); // Adjust the time as necessary
    return () => clearTimeout(timer);
  }, []);

  let localWebUrl = '';
  localWebUrl = baseUrl;

  useEffect(() => {
    const searchParams = new URLSearchParams(window.location.search);
    const params = {};
    for (const [key, value] of searchParams.entries()) {
      params[key] = value;
    }

    setQueryParams(params);
    if (Object.keys(params).length > 0) {
      if (params.major) {
        seturlParamsExist(true);
      }
    } else {
      seturlParamsExist(false);
    }
  }, []);

  // Watch Step 1 form field values and update validity state
  const step1Values = watch(['campusData', 'instituteData']);

  useEffect(() => {
    //console.log('Step 1 values changed:', step1Values);
    const isValid = Object.values(step1Values).every((value) => !!value);
    setIsStep1Valid(isValid);
  }, [step1Values]);

  const nextStep = () => {
    setStep((prevStep) => Math.min(prevStep + 1, maxSteps));
    // Push event to data layer
    const nextSectionName = 'mypath2asu' + step;
    pushToDataLayer({
      action: 'click',
      event: 'link',
      name: 'onclick',
      region: 'main content',
      section: nextSectionName,
      text: 'next',
      type: 'internal link',
    });
    if ('scrollBehavior' in document.documentElement.style) {
      topRef.current.scrollIntoView({ behavior: 'smooth' });
    } else {
      topRef.current.scrollIntoView(); // Fallback for Safari/iOS
    }
  };

  const prevStep = () => {
    setStep((prevStep) => Math.max(prevStep - 1, 1));
    // Push event to data layer
    const prevSectionName = 'mypath2asu' + step;
    pushToDataLayer({
      action: 'click',
      event: 'link',
      name: 'onclick',
      region: 'main content',
      section: prevSectionName,
      text: 'previous',
      type: 'internal link',
    });
    if ('scrollBehavior' in document.documentElement.style) {
      topRef.current.scrollIntoView({ behavior: 'smooth' });
    } else {
      topRef.current.scrollIntoView(); // Fallback for Safari/iOS
    }
    setIsButtonDisabled(false);
  };

  // Watch for changes in Step 2 program details hidden field and make Next button availble only if program data is updated
  // Watch for changes in Step 2 fields
  const fieldValue = watch('hiddenTextProgField');
  useEffect(() => {
    const isProgramData = !!fieldValue;

    setStep2FieldValue(isProgramData);
  }, [fieldValue]);

  const handleStep2FieldChange = (value) => {
    setStep2FieldValue(value);
    setIsStep2ValueChanged(true); // Set flag to true when value changes
  };

  // AES encryption parameters
  //const secretKey = 'this is a secret key';

  const onSubmit = async (data) => {
    //console.log(data);

    //for online programs, send program code + plan code
    let PlanCode = '';
    if (data.selectedProgram && data.selectedProgram.includes('-')) {
      // Split the string by the hyphen
      const splitArray = data.selectedProgram.split('-');
      PlanCode = splitArray[1];
    } else {
      PlanCode = data.selectedProgram;
    }

    //check if there are any submission errors, if not disable submit button
    if (Object.keys(errors).length === 0) {
      setIsButtonDisabled(true);
      // Submit data or perform any other actions
    }

    const urlsearchParams = new URLSearchParams(window.location.search);
    const urlparams = {};
    for (const [urlkey, urlvalue] of urlsearchParams.entries()) {
      urlparams[urlkey] = urlvalue;
    }
    let instValue = '';
    const onlineMajor = data.onlineProgData ? data.onlineProgData : '';
    const asuLocal = data.campusData === 'LOCAL' ? 'Y' : 'N';
    const onlinecampus = data.campusData !== 'ONLNE' ? 'N' : 'Y';
    const local =
      urlparams.local !== undefined && urlparams.local !== null
        ? urlparams.local
        : asuLocal;
    //const onlyMajor = onlineMajor?onlineMajor:urlparams.major !== undefined && urlparams.major !== null ? urlparams.major.replace('~null', '') : data.selectedProgram;
    const onlyMajor = onlineMajor
      ? onlineMajor
      : urlparams.major !== undefined && urlparams.major !== null
        ? urlparams.major.replace('~null', '')
        : data.selectedProgram;

    //const major = urlparams.major !== undefined && urlparams.major !== null ? urlparams.major : PlanCode;
    const major =
      urlparams.major !== undefined && urlparams.major !== null
        ? urlparams.major.replace('~null', '')
        : PlanCode; // Remove '~' from urlparams.major if it exists
    const campusOri =
      urlparams.campus !== undefined && urlparams.campus !== null
        ? urlparams.campus
        : data.campusData;
    const campus = campusOri == 'ONLNE' ? '' : campusOri;
    const degree =
      urlparams.degree !== undefined && urlparams.degree !== null
        ? urlparams.degree
        : data.degreeCodeData;
    const college =
      urlparams.college !== undefined && urlparams.college !== null
        ? urlparams.college
        : data.CollegeCodeData;
    const OriginalInstitution =
      urlparams.institution !== undefined && urlparams.institution !== null
        ? urlparams.institution
        : data.instituteData.value;
    if (OriginalInstitution === '000111') {
      instValue = '107115';
    } else if (OriginalInstitution === '000008') {
      instValue = '001338';
    } else if (OriginalInstitution === '000004') {
      instValue = '001284';
    } else {
      instValue = OriginalInstitution;
    }
    const institution = instValue;
    //const institution = OriginalInstitution !== '000111' ? OriginalInstitution : '107115';
    const online =
      urlparams.online !== undefined && urlparams.online !== null
        ? urlparams.online
        : onlinecampus;
    const firstName = data.first_name;
    const lastName = data.last_name;
    const zipCode = data.zip_code;
    const email = data.email;
    const phone = data.phone.substring(1);
    const majorValue = major + '~null';
    const termData = data.termData ? data.termData : '';
    //console.log(termData);
    const encodedMajor = encodeURIComponent(
      JSON.stringify({ majorVal: majorValue }),
    );
    const mid = data.midValue ? data.midValue : '';
    const programName = data.programName ? data.programName : '';
    const collegeName = data.colleNameData ? data.colleNameData : '';
    const countryVal = data.country ? data.country.toUpperCase() : '';

    // Extract the GA client ID from the _ga cookie (format: GA1.2.<id1>.<id2>)
    const gaCookieMatch = document.cookie.match(/_ga=([^;]+)/);
    const gaUserId = gaCookieMatch
      ? gaCookieMatch[1].split('.').slice(-2).join('.')
      : '';

    const ds =
      typeof drupalSettings !== 'undefined' && drupalSettings.asu_mypath_signup
        ? drupalSettings.asu_mypath_signup
        : {};
    const degugMode = ds.debugMode || false;

    // Create an object with variable values

    const submissionData = {
      ['major']: major,
      ['degree']: degree,
      ['college']: college,
      ['campus']: campus,
      ['institution']: institution,
      ['online']: online,
      ['firstName']: firstName,
      ['lastName']: lastName,
      ['email']: email,
      ['phone']: phone,
      ['local']: local,
      ['sfMajor']: onlyMajor,
      ['entryTerm']: termData,
      ['zipCode']: zipCode,
      ['sfCampus']: campusOri,
      ['mid']: mid,
      ['programName']: programName,
      ['collegeName']: collegeName,
      ['Country']: countryVal,
      ['enterpriseclientid']: gaUserId,
    };
    //console.log(submissionData, 'submissionData');
    // If MID exists, also post to Maricopa API
    if (mid) {
      try {
        await fetch(`${localWebUrl}/asu_mypath_signup/post-to-maricopa-api`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(submissionData),
        });
      } catch (error) {
        console.error('Maricopa API error:', error);
      }
    }

    // Always post data to SF and redirect
    const localFetchUrl = `${localWebUrl}/asu_mypath_signup/mypath-redirect`;
    try {
      const response = await fetch(`${localFetchUrl}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(submissionData),
      });
      if (!response.ok) {
        throw new Error('Failed to post data');
      }
    } catch (error) {
      console.error('SF post error:', error.message);
    }

    const redirectResponse = await fetch(
      '/asu_mypath_signup/mypath-transfer-redirect',
      {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(submissionData),
      },
    );

    const result = await redirectResponse.json();
    if (result.redirect) {
      window.location.href = result.redirect;
    }
  };

  const leftSideFirstMethodText =
    '<h2>MyPath2ASU<sup>®</sup> signup</h2>Get started with your transfer planning process.<h3>Here\'s what to expect:</h3><div><ul class="uds-list mypath-uds-list"><li><strong>Step 1:</strong> Select your preferred way to study and current institution so we can customize your pathway.</li><li><strong>Step 2:</strong> Choose a program you\'re most interested in.</li><li><strong>Step 3:</strong> Fill in your personal information and submit the form. Once submitted, complete your Transfer Guide account and access your MyPath2ASU<sup>®</sup> transfer map.</li></ul></div>';

  const leftSideSecondMethodText =
    '<h2>MyPath2ASU<sup>®</sup> signup</h2>Sign up for ASU\'s Transfer Admission Guarantee to secure your pathway, track your degree progress, and connect your transfer planning process to ASU.<h3>Here\'s what to expect:</h3><div><ul class="uds-list mypath-uds-list"><li>Fill in your personal information and submit the form.</li><li>Once submitted, complete your Transfer Guide account.</li></ul></div><div class="transfer-guide-benefits-url"><strong>Transfer Guide benefits:</strong><ul class="uds-list"><li>Track degree progress</li><li>Submit course evaluations</li><li>Plan your coursework and stay on track</li></ul></div>';

  // Function to reset dependent fields when a field changes
  const handleFieldChange = (field, value) => {
    if (field === 'campusData') {
      // Reset field2 when field1 changes
      setValue('hiddenTextProgField', '');
    }
  };

  const isNextDisabled =
    (step === 2 && !step2FieldValue) ||
    (step === 1 &&
      (!isStep1Valid ||
        (midStatus.isRequired &&
          (!midStatus.isValid || midStatus.isChecking))));

  const hideNextButton = step === 1 && midStatus.isMaricopa;

  return (
    <div>
      {isFormLoading ? (
        <div>
          <p>Loading, please wait...</p>
        </div>
      ) : (
        <div className="MyPathAsuForm" ref={topRef}>
          <div className="layout__region layout__region--first col-md-5 left-form">
            {urlParamsExist ? (
              <div
                className="firstMethod"
                dangerouslySetInnerHTML={{ __html: leftSideSecondMethodText }}
              />
            ) : (
              <div
                className="firstMethod"
                dangerouslySetInnerHTML={{ __html: leftSideFirstMethodText }}
              />
            )}
          </div>
          <div className="layout__region layout__region--second col-md-7 right-form">
            <FormProvider {...methods}>
              <form
                className="uds-form"
                onSubmit={methods.handleSubmit(onSubmit)}
                autoComplete="on"
              >
                {urlParamsExist ? (
                  <div>
                    <Step3
                      localWebUrl={localWebUrl}
                      queryParams={queryParams}
                      register={methods.register}
                    />
                    {/* <button className="btn btn-maroon" type="submit">
            Consent and submit agreement
          </button> */}
                    <button
                      disabled={isButtonDisabled}
                      className="btn btn-gold custom-rfi-button consent-submit-button"
                      type="submit"
                      onClick={() => {
                        if (!isButtonDisabled) {
                          // Ensure the button is not disabled
                          //pass data to dataLayer
                          pushToDataLayer({
                            action: 'click',
                            event: 'link',
                            name: 'onclick',
                            region: 'main content',
                            section: 'mypath2asu3',
                            text: 'Consent and submit agreement',
                            type: 'internal link',
                          });
                        }
                      }}
                    >
                      {isButtonDisabled ? (
                        <div className="spinner"></div>
                      ) : (
                        'Consent and submit agreement'
                      )}
                    </button>
                  </div>
                ) : (
                  <div className="innerForm">
                    {step === 1 && (
                      <Step1
                        errors={errors}
                        localWebUrl={localWebUrl}
                        register={methods.register}
                        handleFieldChange={handleFieldChange}
                        onMidStatusChange={setMidStatus}
                      />
                    )}
                    {step === 2 && (
                      <Step2
                        localWebUrl={localWebUrl}
                        onChangeField={handleStep2FieldChange}
                        register={methods.register}
                      />
                    )}
                    {step === 3 && (
                      <Step3
                        localWebUrl={localWebUrl}
                        register={methods.register}
                      />
                    )}
                    {isMobile ? (
                      <div className="mobileMypathButtons">
                        {step === maxSteps && (
                          <>
                            <button
                              disabled={isButtonDisabled}
                              className="btn btn-gold custom-rfi-button consent-submit-button"
                              type="submit"
                              onClick={() => {
                                if (!isButtonDisabled) {
                                  // Ensure the button is not disabled
                                  //pass data to dataLayer
                                  pushToDataLayer({
                                    action: 'click',
                                    event: 'link',
                                    name: 'onclick',
                                    region: 'main content',
                                    section: 'mypath2asu3',
                                    text: 'Consent and submit agreement',
                                    type: 'internal link',
                                  });
                                }
                              }}
                            >
                              {isButtonDisabled ? (
                                <div className="spinner"></div>
                              ) : (
                                'Consent and submit agreement'
                              )}
                            </button>
                          </>
                        )}

                        {step > 1 && (
                          <button
                            className="btn btn-gray custom-rfi-button nxt-button"
                            type="button"
                            onClick={prevStep}
                          >
                            Previous
                          </button>
                        )}
                        {step < maxSteps && !hideNextButton && (
                          <button
                            className="btn btn-maroon custom-rfi-button"
                            type="button"
                            onClick={nextStep}
                            disabled={isNextDisabled}
                          >
                            Next
                          </button>
                        )}
                      </div>
                    ) : (
                      <div className="myPathSubmitBtns">
                        {step > 1 && (
                          <button
                            className="btn btn-gray custom-rfi-button nxt-button"
                            type="button"
                            onClick={prevStep}
                          >
                            Previous
                          </button>
                        )}
                        {step < maxSteps && !hideNextButton && (
                          <button
                            className="btn btn-maroon custom-rfi-button"
                            type="button"
                            onClick={nextStep}
                            disabled={isNextDisabled}
                          >
                            Next
                          </button>
                        )}

                        {step === maxSteps && (
                          <button
                            disabled={isButtonDisabled}
                            className="btn btn-gold custom-rfi-button consent-submit-button"
                            type="submit"
                            onClick={() => {
                              if (!isButtonDisabled) {
                                // Ensure the button is not disabled
                                //pass data to dataLayer
                                pushToDataLayer({
                                  action: 'click',
                                  event: 'link',
                                  name: 'onclick',
                                  region: 'main content',
                                  section: 'mypath2asu3',
                                  text: 'Consent and submit agreement',
                                  type: 'internal link',
                                });
                              }
                            }}
                          >
                            {isButtonDisabled ? (
                              <div className="spinner"></div>
                            ) : (
                              'Consent and submit agreement'
                            )}
                          </button>
                        )}
                      </div>
                    )}
                  </div>
                )}
              </form>
            </FormProvider>
          </div>
        </div>
      )}
    </div>
  );
};

export default MultiStepForm;
