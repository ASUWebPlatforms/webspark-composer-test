import React, {useEffect, useState} from 'react';
import { useFormContext, Controller, useController } from 'react-hook-form';
import { PhoneInput } from 'react-international-phone';
import axios from 'axios';
import Select from 'react-select';
import './index.css';
import { useData } from './DrupalSettings';

const RfiForm = ({queryParams,localWebUrl}) => {
    const data = useData(); 
    const { register, formState: { errors },watch,setValue } = useFormContext();
    const [termOptions, setTermOptions] = useState([]);
    const [phoneNumber, setPhoneNumber] = useState('');
    const [queryDegree, setQueryDegree] = useState();
    const [queryCollege, setQueryCollege] = useState();
    const [queryMajor, setQueryMajor] = useState();
    const [queryInst, setQueryInst] = useState();
    const [error, setError] = useState(null);
    const [lastSemPart, setLastSemPart] = useState(null);
    const { control,getValues, trigger } = useFormContext();
    const values = getValues();
    const [selectedOption, setSelectedOption] = useState(null);
    let semKeyVal =  '';
    //console.log(values);
    const highSchoolValue = values.hsValueField;
    const currentYear = new Date().getFullYear();
    const [termDefault, setTermDefault] = useState(null);
    const consent = "<div class='mypath-consent-wording'><p>By submitting my information, I consent to ASU contacting me about education services using email, direct mail, SMS/texting and digital platforms. Message and data rates may apply. Consent is not required to receive services, and I can withdraw consent by contacting ASU at <a href='mailto:UnsubFutureStudentComm@asuedu'>UnsubFutureStudentComm@asu.edu</a> or as described in communications I receive. I consent to ASU's <a href='https://asuonline.asu.edu/text-terms/'>mobile terms and conditions</a> and <a href='https://asuonline.asu.edu/web-analytics-privacy-2/'>Privacy Statements</a>, including the European Supplement.</p></div>";
    // Watch form fields to trigger validations on input change
    const firstNameValue = watch('first_name');
    const lastNameValue = watch('last_name');
    const emailValue = watch('email');
    const phoneNumValue = watch('phone');
    const zipCodeValue = watch('zip_code');
    const termValue = watch('termData');
   

    const interetsOptions = [
      { value: "Architecture and Construction", label: "Architecture and Construction" },
      { value: "Arts", label: "Arts" },
      { value: "Business", label: "Business" },
      { value: "Communication and Media", label: "Communication and Media" },
      { value: "Computing and Mathematics", label: "Computing and Mathematics" },
      { value: "Education and Teaching", label: "Education and Teaching" },
      { value: "Engineering and Technology", label: "Engineering and Technology" },
      { value: "Entrepreneurship", label: "Entrepreneurship" },
      { value: "Health and Wellness", label: "Health and Wellness" },
      { value: "Humanities", label: "Humanities" },
      { value: "Interdisciplinary Studies", label: "Interdisciplinary Studies" },
      { value: "Law, Justice and Public Service", label: "Law, Justice and Public Service" },
      { value: "Science", label: "Science" },
      { value: "Social and Behavioral Sciences", label: "Social and Behavioral Sciences"},
      { value: "Sustainability", label: "Sustainability"}

  ];

    const rules = { 
      required: 'Text is required',
      plainText: {
        message: 'Text must contain only alphanumeric characters, spaces, and punctuation marks',
        validator: (value) => {
          return /^[a-zA-Z0-9\s\.,!?]*$/.test(value);
        },
      },
    };

   /*  const {
        field: { onChange: onTermChange, value: termvalue },
         fieldState: { invalid },
       } = useController({
         name: 'termData',
         rules: { required: 'Term is required'}
       }); */
    
   /*  const {
        field: { onNameChange, value },
        fieldState: { nameinvalid },
      } = useController({
        name: 'first_name',
        rules: { required: 'First nameee is required'},
    });

    const {
        field: { onlnameChange, lvalue },
        fieldState: { linvalid },
      } = useController({
        name: 'last_name',
        rules: { required: 'Last name is required' },
      });

    const {
        field: { onemailChange, emailvalue },
        fieldState: { emailinvalid },
      } = useController({
        name: 'email',
        rules: { required: 'Email is required' },
        defaultValue: ''
    });
 */
   
    const {
         field: { onChange: onPhoneChange, value: phoneValue },
         fieldState: { phinvalid },
       } = useController({
         name: 'phone',
         rules: { required: 'Phone is required' },
    });

    const {
       field: { onChange: onInterestChange, value: interestvalue },
       fieldState: { intInvalid },
     } = useController({
       name: 'interestData',
       rules: { required: 'Academic interest is required'},
       defaultValue: values.interestOptionsField?values.interestOptionsField:''
    });

   /*  const {
      field: { onzipcodeChange, zipvalue },
      fieldState: { zipinvalid },
    } = useController({
      name: 'zip_code',
      rules: { required: 'Zip code is required' },
    });
 */
   
    
    const handlePhoneChange = (value, country) => {
        //console.log(value);
        setPhoneNumber(value);
        setValue('phone', value);
        onPhoneChange(value);
        //onPhoneChange(value);
        // Validate the phone number here
        if (value === '+1' || value.length < 10) {
        setError('Phone number is required and must be at least 10 digits.');
        } else {
        setError('');
        }
    };

   
    useEffect(() => {
        const termUrl = `${localWebUrl}/asu_digital_book/json/term`;
            axios.get(termUrl, { timeout: 5000 }) //set timer so the call to the url is limited
                .then(response => {
                    const gettermOptions = Object.entries(response.data).map(([key, termLabel]) => ({
                        //value: key,
                        value: key+':'+termLabel,
                        label: termLabel
                    }));
                    setTermOptions(gettermOptions);
                    
                    //set defualt value for term if student type was selected from learning module
                    if(highSchoolValue){
                      const currentYear = new Date().getFullYear();
                      const currentMonth = new Date().getMonth();
                      let formYear = '';
                        
                        if(highSchoolValue == "High school senior"){
                          formYear = currentYear + 1;
                        }
                        if(highSchoolValue == "High school junior"){
                          formYear = currentYear + 2;
                        }
                        if(highSchoolValue == "High school sophomore"){
                          formYear = currentYear + 3;
                        }
                        if(highSchoolValue == "High school freshman"){
                          formYear = currentYear + 4;
                        }
                       
                       const currentSemesterYear = formYear.toString(); 
                       const threeDigitYear = currentSemesterYear.charAt(0) + currentSemesterYear.slice(2);
                       
                       let sem = '';
                       let lastSem = '';
                      
                       if(currentMonth <= 5){
                        
                        sem = "Spring";
                        lastSem = 1;
                        setLastSemPart(lastSem);
                       }
                       else{
                       
                        sem = "Fall";
                        lastSem = 7;
                        setLastSemPart(lastSem);
                       }
                      const semesterKey = `${threeDigitYear}${lastSem}`;
                      const semesterLabel = `${currentSemesterYear} ${sem}`
                      semKeyVal = semesterKey+':'+semesterLabel;
                    
                      }
                      else{
                        semKeyVal = '';
                      }
                      const defaultTermvalue = semKeyVal ? gettermOptions.find((option) => option.value === semKeyVal):''; // Adjust this condition based on your default value logic
                    
                       if (defaultTermvalue) {  
                        setTermDefault(defaultTermvalue.value);
                        
                      }
                       

                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                });
                
        
    }, []);
    
    useEffect(() => {
      // Update the form field value when termDefault is set
      if (termDefault) {
       
        setTermDefault(termDefault);
        onTermChange(termDefault);
      }
    }, [termDefault]);

    const {
      field: { onChange: onTermChange, value: termvalue },
      fieldState: { invalid },
    } = useController({
      name: 'termData',
      control,
      rules: { required: 'Term is required'},
      defaultValue: termDefault
      //defaultValue: '',
    });

    useEffect(() => {
      // Whenever termOptions or termDefault changes, set selectedOption
      if (termOptions.length && termDefault) {
        const defaultSelected = termOptions.find(option => option.value === termDefault);
        setSelectedOption(defaultSelected);
      }
    }, [termOptions, termDefault]);

    //const selectedOption = termOptions.length && termDefault ? termOptions.find(option => option.value === termDefault) : null;

    const onTermChangeValue = (event) => {
      
      setSelectedOption(event);
      onTermChange(event);
    }

  return (
    <div className="RfiForm">
     
      
      <div className="rfiHero" dangerouslySetInnerHTML={{ __html: data.rfi_hero || '' }} />
      <div className="container">
      <div className="row">
      <div className="layout__region layout__region--first col-md-6">

        <div className="rfiIntro" dangerouslySetInnerHTML={{ __html: data.rfi_intro || '' }} /> 
       
       {/* First Name */}
       <div className="form-group js-form-item react-form-item form-item form-item-first-name">
                    <label className="form-label"><span title="Required" className="fa fa-icon fa-circle uds-field-required"></span> First Name</label>
                    <input 
                        id="first_name_field" 
                        type="text"
                        {...register('first_name', { 
                            required: 'First name is required',
                            pattern: { value: /^[a-zA-Z0-9\s\.,!?]*$/, message: 'Only plain text allowed' } 
                        })} 
                        value={firstNameValue}
                        className={errors.first_name ? 'error-field' : ''}
                        autoComplete="first-name-field"
                    />
                    {errors.first_name && <span className="error">{errors.first_name.message}</span>}
                </div>

                {/* Last Name */}
                <div className="form-group js-form-item js-form-item-last-name react-form-item form-item">
                    <label className="form-label"><span title="Required" className="fa fa-icon fa-circle uds-field-required"></span> Last Name</label>
                    <input 
                        id="last_name_field" 
                        type="text"
                        {...register('last_name', { 
                            required: 'Last name is required',
                            pattern: { value: /^[a-zA-Z0-9\s\.,!?]*$/, message: 'Only plain text allowed' }
                        })} 
                        value={lastNameValue}
                        className={errors.last_name ? 'error-field' : ''}
                        autoComplete="last-name-field"
                    />
                    {errors.last_name && <span className="error">{errors.last_name.message}</span>}
        </div>

        {/* Email */}
        <div className="form-group js-form-item react-form-item form-item">
                <label className="form-label"><span title="Required" className="fa fa-icon fa-circle uds-field-required"></span> Email</label>
                <input 
                    id="email_field" 
                    type="email"
                    {...register('email', { 
                        required: 'Email is required',
                        pattern: { value: /^\S+@\S+\.\S+$/, message: 'Invalid email address' }
                    })} 
                    value={emailValue}
                    className={errors.email ? 'error-field' : ''}
                    autoComplete="email-field"
                />
                {errors.email && <span className="error">{errors.email.message}</span>}
            </div>


         {/* Phone Number */}
          <div className="form-group js-form-item react-form-item form-item form-item-phone">
                    <label className="form-label"><span title="Required" className="fa fa-icon fa-circle uds-field-required"></span> Phone Number</label>
                    {/* <PhoneInput
                        id="phone-field"
                        defaultCountry="us"
                        value={phoneNumValue}
                        onChange={handlePhoneChange}
                        className={errors.phone ? 'error-field' : ''}
                        autoComplete="phone-field"
                        {...register('phone', { 
                          required: 'Phone is required',
                        })} 
                    /> */}
                    <Controller
                      name="phone"
                      control={control} // This comes from useForm()
                      rules={{ 
                        required: 'Phone is required', 
                        validate: (value) => value.length >= 10 || 'Phone number must be at least 10 digits long',
                      }}
                      render={({ field }) => (
                        <PhoneInput
                          id="phone-field"
                          defaultCountry="us"
                          value={field.value}
                          onChange={field.onChange}
                          className={errors.phone ? 'error-field' : ''}
                          autoComplete="phone-field"
                        />
                      )}
                    />
                    <span className="react-text-muted">Please enter your phone number in the format +1 (123) 456-7890.</span>
                    {errors.phone && <span className="error"><br />{errors.phone.message}</span>}
                </div>

                {/* Zip Code */}
                <div className="form-group js-form-item react-form-item form-item">
                    <label className="form-label"><span title="Required" className="fa fa-icon fa-circle uds-field-required"></span> Zip Code</label>
                    <input 
                        id="zipcode_field"
                        type="text"
                        {...register('zip_code', { 
                            required: 'Zip code is required',
                            pattern: { value: /^[0-9]{5}$/, message: 'Invalid Zip Code' }
                        })} 
                        value={zipCodeValue}
                        className={errors.zip_code ? 'error-field' : ''}
                        autoComplete="zipcode-field"
                    />
                    {errors.zip_code && <span className="error">{errors.zip_code.message}</span>}
        </div>

       

      {/* Term Selection */}
      <div className="form-group js-form-item react-form-item form-item">
                <label className="form-label"> <span title="Required" className="fa fa-icon fa-circle uds-field-required"></span>When do you anticipate starting?</label>
                {/* <Select
                    name="termData"
                    id="termDataField"
                    //value={termValue}
                    value={selectedOption ? selectedOption : termvalue}
                    placeholder="Select..."
                    //onChange={onTermChange}
                    options={termOptions}
                    onChange={(option) => setValue('termData', option)}
                    className={errors.termData ? 'error-field' : ''}
                    autoComplete="termDataeVal"
                    defaultValue={termDefault}
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
                      value={termOptions.find(option => option.value === field.value)} // Ensures correct value is displayed
                      onChange={(selectedOption) => {
                        field.onChange(selectedOption.value);  // Update the form state
                        trigger('termData');  // Manually trigger validation
                      }}
                      className={errors.termData ? 'error-field' : ''}
                      defaultValue={termDefault}
                      autoComplete="termDataeVal"
                    />
                  )}
                />
                
                {errors.termData && <span className="error">{errors.termData.message}</span>}
        </div>

        <div className="js-form-item form-item js-form-type-text-field form-item-interest js-form-item-interest form-group">
                <label htmlFor="interestDataField"><span title="Required" className="fa fa-icon fa-circle uds-field-required"></span> Academic interest</label>
                {/* <Select
                    name="interestData"
                    id="interestDataField"
                    value={interestvalue}
                    onChange={(option) => setValue('interestData', option)}
                    options={interetsOptions}
                    className={errors.interestData ? 'error-field' : ''}
                    autoComplete="interestData"
                /> */}
                <Controller
                  name="interestData"
                  control={control}
                  rules={{ required: 'Please select a interest' }}
                  render={({ field }) => (
                    <Select
                      {...field}
                      id="interestDataField"
                      options={interetsOptions}
                      placeholder="Select..."
                      value={interetsOptions.find(option => option.value === field.value)} // Ensures correct value is displayed
                      onChange={(selectedOption) => {
                        field.onChange(selectedOption.value);  // Update the form state
                        trigger('interestData');  // Manually trigger validation
                      }}
                      className={errors.interestData ? 'error-field' : ''}
                      defaultValue={values.interestOptionsField ? values.interestOptionsField : ''}  // Fixed syntax for defaultValue
                      autoComplete="interestData"
                      
                    />
                  )}
                />
                {errors.interestData && <span className="error">{errors.interestData.message}</span>} 
        </div>
        

        <div className='consent' dangerouslySetInnerHTML={{ __html: consent }} />
      </div></div>
    </div>
    </div>
  );
};

export default RfiForm;