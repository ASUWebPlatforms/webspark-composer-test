import React, { useState, useEffect, useRef } from 'react';
import { useForm, FormProvider, useFormContext } from 'react-hook-form';

import InfoPassion from "./InfoPassion";
import InfoBelong from "./InfoBelong";
import InfoUnkownFuture from './InfoUnknownFuture';
import LearningData from "./Learning";
import InvestData from "./Invest";
import RfiForm from "./Rfi";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCheck } from '@fortawesome/free-solid-svg-icons';
import Customize from "./Customize";

const MainForm = () => {
  const methods = useForm();
  const { handleSubmit, register, formState: { errors }, watch, control } = methods;
  const [step, setStep] = useState(1);
  const [stepVal, setStepVal] = useState(step);
  const stepName = ['Learn','Customize','Invest','More info'];
  const maxSteps = 5;
  const [knowFieldValue, setKnowFieldValue] = useState(false);
  const [countryChangeValue, setCountryChangeValueBool] = useState(false); 
  const [countryValue, setCountryValue] = useState(''); 
  const [stateValue, setStateValue] = useState('');
  const [isStep2ValueChanged, setIsStep2ValueChanged] = useState(false); 
  const [utmCampaign, setUtmCampaign] = useState('');
  const [utmContent, setUtmContent] = useState('');
  const [utmMedium, setUtmMedium] = useState('');
  const [utmSource, setUtmSource] = useState('');
  const [utmTerm, setUtmTerm] = useState('');
  const [isButtonDisabled, setIsButtonDisabled] = useState(false);
  const [queryParams, setQueryParams] = useState(null); //get url parameters if any
  const [urlParamsExist, seturlParamsExist] = useState(false);
  const [progress, setProgress] = useState(0);
  const [completed, setCompleted] = useState(false);
  const baseUrl = window.location.origin;
  const topRef = useRef(null);
  
  //console.log(baseUrl);
  let localWebUrl = '';
  if(baseUrl == "http://localhost:8080"){
    localWebUrl = 'http://localhost:8080/archanavtest/web';
  }
  else{
    localWebUrl = baseUrl
  }

  //keep watch on do you know what to do option on infoPassion file
  const whatToDoValue = watch(['knowValueField']); 
  useEffect(() => {
    const isValid = Object.values(whatToDoValue).every(value => !!value);
    //console.log('campval',isValid);
    setKnowFieldValue(isValid);
  }, [whatToDoValue]);

  //Keep a watch on country field on invest.js file
  const countryFieldValue = watch(['CountryField']);
  
  useEffect(() => {
    const isCountry = Object.values(countryFieldValue).every(value => !!value);
    if(isCountry){
      Object.values(countryFieldValue).forEach(value => {
        console.log('Country Value:', value.value);
        setCountryValue(value.value);
         
         if(value.value === 'USA'){
          setCountryChangeValueBool(false);
         }
         else{
          setCountryChangeValueBool(true);
         }
      });
      
    }
    else{
      setCountryChangeValueBool(false);
    }
   //console.log(countryChangeValue);
  }, [countryFieldValue]);
  

  //Keep a watch on state field on invest.js file
  const stateFieldValue = watch(['stateField']);
  useEffect(() => {
    const isState = Object.values(stateFieldValue).every(value => !!value);
    if(isState){
      Object.values(stateFieldValue).forEach(value => {
        //console.log('state Value:', value.value);
        setStateValue(value.value);
      });
    }
  }, [stateFieldValue]);

  // When the current step changes, scroll to the top of the page
  useEffect(() => {
    // Scroll to the top after step changes and the component re-renders
    if (topRef.current) {
     // topRef.current.scrollIntoView({ behavior: 'smooth' });
      if ('scrollBehavior' in document.documentElement.style) {
        topRef.current.scrollIntoView({ behavior: 'smooth' });
      } else {
        topRef.current.scrollIntoView(); // Fallback for Safari/iOS
      }
    }
  }, [step]);

  const onNextStep = () => {
    setStep((prevStep) => prevStep + 1);
  };

  const onPreviousStep = () => {
    setStep((prevStep) => prevStep - 1);
  };

  //set submit button disablity to false if aany steps other than step 4
  useEffect(() => {
    if (step !== 5) {
      setIsButtonDisabled(false);
    }
  }, [step]); // Runs when `step` changes

  /* Get URL parameters */
  useEffect(() => {
    const searchParams = new URLSearchParams(window.location.search);
    const params = {};
    for (const [key, value] of searchParams.entries()) {
      params[key] = value;
    }
    setQueryParams(params.type);
    if (Object.keys(params).length > 0) {
      seturlParamsExist(true);
    }
    else{
      seturlParamsExist(false);
    }
    
    //UTM variables
    if(params.utm_campaign){
      setUtmCampaign(params.utm_campaign);
    }
    else{
      setUtmCampaign('');
    }
    if(params.utm_content){
      setUtmContent(params.utm_content);
    }
    else{
      setUtmContent('');
    }
    if(params.utm_medium){
      setUtmMedium(params.utm_medium);
    }
    else{
      setUtmMedium('');
    }
    if(params.utm_source){
      setUtmSource(params.utm_source);
    }
    else{
      setUtmSource('');
    }
    if(params.utm_term){
      setUtmTerm(params.utm_term);
    }
    else{
      setUtmTerm('');
    }
  }, []);
  
  const nextStep  = async () => {
    setStep((prevStep) => Math.min(prevStep + 1, maxSteps));
    window.scrollTo(0, 0); 
    if ('scrollBehavior' in document.documentElement.style) {
      
      topRef.current.scrollIntoView({ behavior: 'smooth' });
    } else {
      topRef.current.scrollIntoView(); // Fallback for Safari/iOS
    }
  };

  const goToStep = (stepNumber) => {
    setStep(stepNumber);
    setProgress(100);
    setTimeout(() => setCompleted(true), 500);
    if ('scrollBehavior' in document.documentElement.style) {
      
      topRef.current.scrollIntoView({ behavior: 'smooth' });
    } else {
      topRef.current.scrollIntoView(); // Fallback for Safari/iOS
    }
  };

  

  const calculateProgress = (stepindex) => {
    let maxpercent = 100/maxSteps;
    return `${maxpercent}%`;
  };


const ProgressBar = ({ progress, totalSteps, currentStep, goToStep }) => {
      const handleProgressClick = (step, event) => {
      event.preventDefault(); 
      if (step === 1) {
        goToStep(2);
      }
      else if (step === 6) {
          goToStep(5);
      } else {
        goToStep(step);
      }
    };

    return (
      <div className="progress-bar"><ul id="progress">
        {Array.from({ length: totalSteps-1 }, (_, index) => (
           

          <button className={`progressStep progressStep-${step} ${stepName[index]}`}  key={`stepButton_${index}`} onClick={(event) => handleProgressClick(index + 2, event)}>
            <li className={`progressDiv ${currentStep === index + 2 && "active"} ${ index+1 < currentStep && "complete"}` } key={`stepButtonMain_${index}`} >

            {index+1 < currentStep ?
              <div className="pageTitle"> <FontAwesomeIcon icon={faCheck} className="check-mark" />  </div>:
              <div className="pageTitle"> { stepName[index] } </div>
            }

            </li>
          </button>

        ))}

          
      </ul>
       
       
      </div>
    );
  }; 

  if(baseUrl == "http://localhost:8080"){
    localWebUrl = 'http://localhost:8080/archanavtest/web';
  }
  else{
    localWebUrl = baseUrl;
  }
 
  const onSubmit = async (data) => {
    //console.log(data.CountryField);
    const firstName = data.first_name;
    const lastName = data.last_name;
    const zipCode = data.zip_code;
    const email = data.email;
    const phone = data.phone.substring(1);
    const termData = data.termData? data.termData:'';
    const militaryStatus = data.MilitaryField && data.MilitaryField == "Veteran"?data.MilitaryField:'';
    const country = data.CountryField ? data.CountryField.value:'';
    const campus = data.campusData && data.campusData === 'ONLNE'?'ONLNE':'GROUND';
    const interest = data.interestData ? data.interestData: '';
    const state = data.StateField ? data.StateField.value:'';
    const knowValueField = data.knowValueField ? data.knowValueField:'';
    //const highSchoolType = data.CurrentLearningField ? data.CurrentLearningField:'First Time Freshman';
    const studentType = data.CurrentLearningField && data.CurrentLearningField === 'Transfer' ? data.CurrentLearningField :'First Time Freshman';
    /* const studentType = data.hsValueField ? data.hsValueField 
    : (highSchoolType && highSchoolType === 'Transfer') ? highSchoolType 
    : ''; */
    //const studentType = highSchoolType && highSchoolType === 'Transfer' ? 'Transfer' : 'First Time Freshman';
    const nextOptionsField = data.NextStepsField ? ((data.NextStepsField === 'Associate') ? 'UGRAD': data.NextStepsField) : '';
    const aspiringProfession = data.aspiringProfession?data.aspiringProfession:'';
    const whatsWhyField = data.whatsWhyField ? data.whatsWhyField:'';
    const knowStudyField = data.knowStudyField ? data.knowStudyField:'';

    const submissionData = {
      ['firstName']: firstName,
      ['lastName']: lastName,
      ['email']: email,
      ['phone']: phone,
      ['entryTerm']: termData,
      ['zipCode'] : zipCode,
      ['militaryStatus']: militaryStatus,
      ['CitizenshipCountry'] : country,
      ['Country'] : country,
      ['Campus'] : campus,
      ['Interest1'] : interest, 
      ['URL'] : baseUrl,
      ['state'] : state,
      ['StudentType'] : studentType,
      ['knowValue'] : knowValueField,
      ['whatsWhyField'] : whatsWhyField,
      ['knowStudyField'] : knowStudyField,
      ['Career'] : nextOptionsField,
      ['utm_campaign']: utmCampaign,
      ['utm_content'] : utmContent,
      ['utm_medium'] : utmMedium,
      ['utm_source'] : utmSource,
      ['utm_term'] : utmTerm
    };
    //console.log('sd',submissionData);

    //check if there are any submission errors, if not disable submit button
    if (Object.keys(errors).length === 0) {
      setIsButtonDisabled(true);
      // Submit data or perform any other actions
     
    }
      //post data to SF
      const localFetchUrl = `${localWebUrl}/asu_digital_book/create_submission`;
     
      try {
        const response = await fetch(`${localFetchUrl}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(submissionData),
          
        });
        //console.log(response);
        if (!response.ok) {
          throw new Error('Failed to post data');
        }

        //const returnData = await response.json();
        //console.log('returnData',returnData);
        const contentType = response.headers.get('content-type');
        
        if (contentType && contentType.includes('application/json')) {
          const responseData = await response.json();
          //console.log(responseData );
          if (responseData.redirect_url) {
            window.location.href = responseData.redirect_url;
          } else {
            console.error('Redirect URL not found in response');
          }
        } else {
          throw new Error('Server response was not JSON');
        }

        // Data was successfully posted
        //console.log('Data posted successfully');
      } catch (error) {
        console.error('Error posting data:', error.message);
      }
    
  };

  
  return (
    <div className='DigitalViewBook' ref={topRef}>
    <div className="mainForm">
   {/*   <div className="layout__fixed-width progressbarDiv">
    
    {step > 1 && (
      <ProgressBar
        progress={calculateProgress()}
        style={{ width: `${calculateProgress()}%` }}
        totalSteps={maxSteps}
        currentStep={step}
        goToStep={goToStep}
      />
  )}
    </div>   */}
    <div className='layout__region layout__region--second col-md-12'></div>
    <FormProvider {...methods}>
      <form className='uds-form' onSubmit={methods.handleSubmit(onSubmit)}>
      {queryParams && 
        (queryParams == 'passion' && (
          <div>
          {step === 1 && <InfoPassion  errors={errors} localWebUrl={localWebUrl} register={methods.register} />}
          </div>
        )) ||
       (queryParams == 'belong' && (
          <div>
          {step === 1 && <InfoBelong  errors={errors} localWebUrl={localWebUrl} register={methods.register} />}
          </div>
        )) || 
        (queryParams == 'unpredictable' && (
          <div>
          {step === 1 && <InfoUnkownFuture  errors={errors} localWebUrl={localWebUrl} register={methods.register} />}
          </div>
        )) || 
        (!queryParams && (
          <div>
          {step === 1 && <InfoPassion  errors={errors} localWebUrl={localWebUrl} register={methods.register} />}
          </div>
        ))
      }
      
      {step === 2 && <LearningData  localWebUrl={localWebUrl} errors={errors} register={methods.register}  ref={topRef} />} 
      {step === 3 && <Customize localWebUrl={localWebUrl} register={methods.register} ref={topRef} />}
      {step === 4 && <InvestData localWebUrl={localWebUrl} register={methods.register} ref={topRef} />} 
      {step === 5 && <RfiForm localWebUrl={localWebUrl} register={methods.register} ref={topRef} />} 
      <div className="container button-row pb-20">
          <div className="row">
         
       
          {(
            (step === 1 && knowFieldValue && (!queryParams || queryParams === 'passion')) ||
            (step === 1 && queryParams === 'belong') ||
            (step === 1 && queryParams === 'unpredictable')
            ) && (
            <button 
              className="btn btn-maroon viewbook-btn" 
              type="button" 
              onClick={nextStep}
            >
            Next
            </button>
          )}
        {step > 1 && step!== 4 && step < maxSteps && 
        (<button 
          className="btn btn-maroon viewbook-btn" 
          type="button" 
          onClick={nextStep}
        >Next</button>
        )}

        {step === 4  && 
        (<button 
          className="btn btn-maroon viewbook-btn" 
          type="button" 
          onClick={nextStep}
        >Let's find out</button>
        )}
        
        {/* {step === 4  && countryChangeValue  && 
        (<button 
          className="btn btn-maroon" 
          type="button" 
          onClick={nextStep}
        >Let's find out</button>
        )}
        
        {step === 4 && countryValue === 'USA' && 
        (<button 
          className="btn btn-maroon" 
          type="button" 
          onClick={nextStep}
        >Let's find out</button>
        )} */}
        
       
        {step === maxSteps  && (
          <button disabled={isButtonDisabled} className="btn btn-gold viewbook-btn" type="submit">
           {isButtonDisabled ? (
             <div className="spinner">Processing</div>
           ) : (
             'Submit'
           )}
         </button>
        )} 
         </div></div>
      
      {/* Dummy progress bar for menu navigation*/}
      <div className='layout__fixed-width progressbarDivDummy'>
          <ProgressBar
            progress={calculateProgress()}
            style={{ width: `${calculateProgress()}%` }}
            totalSteps={maxSteps}
            currentStep={step}
            goToStep={goToStep}
            className={`${stepName}${step}`}
          /> 
      </div>  

      </form>
    </FormProvider>
    
    </div>
    <div className='viewbookFooter'> <div className='layout__fixed-width progressbarDiv'>
    
    {step > 1 && (
      <ProgressBar
        progress={calculateProgress()}
        style={{ width: `${calculateProgress()}%` }}
        totalSteps={maxSteps}
        currentStep={step}
        goToStep={goToStep}
      />
  )}
    </div>  </div>
    </div>
  );
};

export default MainForm;
