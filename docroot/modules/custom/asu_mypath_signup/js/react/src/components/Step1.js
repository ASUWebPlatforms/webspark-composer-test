import React, { useState, useEffect, useCallback, useRef } from 'react';
import { useFormContext, Controller, useController } from 'react-hook-form';
import axios from 'axios';
import Select from 'react-select';
import Step3 from './Step3';
import { pushToDataLayer } from '../utils/dataLayer';
import { parsePipeOptions } from '../utils/stringUtils';

const Step1 = ({ localWebUrl, handleFieldChange, onMidStatusChange }) => {
  const {
    register,
    control,
    setValue,
    getValues,
    clearErrors,
    formState: { errors },
  } = useFormContext();
  const [institutionOptions, setInstitutionOptions] = useState([]);
  const [isLoading, setIsLoading] = useState(false);
  const [CampusselectedOption, setCampusSelectedOption] = useState('');
  const [resetCampsuField, setResetCampusField] = useState(false);
  const [showMaricopaField, setShowMaricopaField] = useState(false);
  const [midValue, setMidValue] = useState(() => getValues('midValue') || '');
  const [sendMaricopaData, setSendMaricopaData] = useState(false);
  const [midValueError, setMidValueError] = useState('');
  const baseUrl = window.location.origin;
  const [institutionSelected, setSelectedInst] = useState(null);
  const midValidationRequestRef = useRef(0);

  const ds =
    typeof drupalSettings !== 'undefined' && drupalSettings.asu_mypath_signup
      ? drupalSettings.asu_mypath_signup
      : {};

  const maricopaInstIdsFromSettings = ds.maricopaInstIds || '';
  const enableMaricopaField = ds.enableMaricopaField || false;
  //const debug_mode = ds.debugMode || false;
  const parsedMaricopaInstIds = parsePipeOptions(maricopaInstIdsFromSettings);

  /* const MARICOPA_INSTITUTION_IDS = [
    '029402',
    '029426',
    '008303',
    '001076',
    '001077',
    '029401',
    '001078',
    '029243',
    '029409',
    '008304',
    '029242',
    '008699',
  ]; */
  /*029402:Chandler-Gilbert Community College
    029426:Estrella Mountain Community College
    008303:Gateway Community College
    001076:Glendale Community College
    001077:Mesa Community College
    029401:Paradise Valley Community College
    001078:Phoenix College
    029243:Rio Salado College
    008304:Scottsdale Community College
    029242:South Mountain Community College
    008699:Maricopa Community Colleges
  */

  const MARICOPA_INSTITUTION_IDS = parsedMaricopaInstIds.map(
    (inst) => inst.value,
  );
  const maricapaNoMatchText =
    ds.maricopaNoMatchText ||
    'No Maricopa ID match found. Please check your MID and try again.';
  //console.log('maricoap no match text from settings', maricapaNoMatchText);
  const campusOptions = [
    {
      value: 'GROUND',
      label: 'I plan to take some/all of my classes on campus',
    },
    { value: 'ONLNE', label: 'I plan to study 100% online through ASU Online' },
    {
      value: 'LOCAL',
      label:
        'I plan to take classes online with in-campus support through ASU Local',
    },
  ];

  const {
    field: { onChange: onChangeCampus, value: valueCampus },
    // field: { onChange, value, name, ref },
    fieldState: { invalid },
  } = useController({
    name: 'campusData',
    rules: { required: 'Please select an option' },
    defaultValue: '',
    //control: register,
  });

  const handleCampusChange = (event) => {
    const newValue = event.target.value;
    const labelText =
      document.querySelector(`label[for="${event.target.id}"]`)?.textContent ||
      'Campus';
    onChangeCampus(newValue); // This updates the react-hook-form state
    pushToDataLayer({
      action: 'click',
      event: 'select',
      name: 'onclick',
      region: 'main content',
      section: 'mypath2asu1',
      text: labelText,
      type: 'checkbox',
    });
    handleFieldChange('campusData', newValue); // Handle any additional logic if needed
    setCampusSelectedOption(newValue);
  };

  /* const {
    field: { onChange: onChangeInstitute, value: selectedInstitution },
    fieldState: { instinvalid },
  } = useController({
    name: "instituteData",
    defaultValue: null,
    rules: { required: "Please select an institution" },
    control,
  }); */

  const {
    field: { onChange: onChangeInstitute, value: selectedInstitution },
    fieldState: { instinvalid },
  } = useController({
    name: 'instituteData',
    control,
    rules: { required: 'Please select an institution' },
    defaultValue: null,
  });

  //if any of the Maricopa institutues are selected, redirect the student to Maricopa website.
  const onInstituteChange = async (event) => {
    const instSelected = event ? event.label : '';
    const instSelectedId = event ? event.value : '';
    // console.log('int selected', instSelectedId);
    /* if((instSelected == "Chandler-Gilbert Community College") || (instSelected == "Estrella Mountain Community College") || (instSelected == "GateWay Community College") || (instSelected == "Glendale Community College") || (instSelected == "Mesa Community College") || (instSelected == "Paradise Valley Community College") || (instSelected == "Phoenix College") || (instSelected == "Rio Salado College") || (instSelected == "Scottsdale Community College") || (instSelected == "South Mountain Community College") || (instSelected == "Maricopa Community Colleges")) */
    const isMaricopaInstitution =
      MARICOPA_INSTITUTION_IDS.includes(instSelectedId);
    if (enableMaricopaField) {
      if (isMaricopaInstitution) {
        //window.location.href = "https://redirect.maricopa.edu/student-center";
        onChangeInstitute(event);
        setShowMaricopaField(true);
        setMidValue('');
        setMidValueError('');
        setSendMaricopaData(false);
        setValue('midValue', '', { shouldDirty: true, shouldValidate: true });
        onMidStatusChange?.({
          isRequired: true,
          isValid: false,
          isChecking: false,
          isMaricopa: true,
        });
      } else {
        onChangeInstitute(event);
        setShowMaricopaField(false);
        setMidValue('');
        setMidValueError('');
        setSendMaricopaData(false);
        setValue('midValue', '', { shouldDirty: true, shouldValidate: false });
        clearErrors('midValue');
        onMidStatusChange?.({
          isRequired: false,
          isValid: true,
          isChecking: false,
          isMaricopa: false,
        });
      }
    } else {
      setShowMaricopaField(false);
      onChangeInstitute(event);
      if (isMaricopaInstitution) {
        onMidStatusChange?.({
          isRequired: false,
          isValid: true,
          isChecking: false,
          isMaricopa: true,
        });
        window.location.href = 'https://redirect.maricopa.edu/student-center';
      } else {
        onMidStatusChange?.({
          isRequired: false,
          isValid: true,
          isChecking: false,
          isMaricopa: false,
        });
      }
    }
    if (event) {
      pushToDataLayer({
        action: 'click',
        event: 'select',
        name: 'onclick',
        region: 'main content',
        section: 'mypath2asu1',
        text: event.label,
        type: 'What institution are you transferring from?',
      });
    }
  };

  const handleMidChange = useCallback(
    async (event) => {
      const nextMidValue = event.target.value;
      const midForValidation = nextMidValue.trim();
      const requestId = midValidationRequestRef.current + 1;

      midValidationRequestRef.current = requestId;
      setMidValue(nextMidValue);
      setValue('midValue', nextMidValue, {
        shouldDirty: true,
        shouldValidate: true,
      });
      setMidValueError('');
      setSendMaricopaData(false);

      if (!midForValidation) {
        onMidStatusChange?.({
          isRequired: true,
          isValid: false,
          isChecking: false,
        });
        return;
      }

      onMidStatusChange?.({
        isRequired: true,
        isValid: false,
        isChecking: true,
      });

      const midUrl = `${baseUrl}/asu_mypath_signup/mid/${encodeURIComponent(
        midForValidation,
      )}`;
      if (event) {
        pushToDataLayer({
          action: 'click',
          event: 'select',
          name: 'onclick',
          region: 'main content',
          section: 'mypath2asu1',
          text: event.label,
          type: 'Enter MEID',
        });
      }

      try {
        const response = await fetch(midUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ mid: midForValidation }),
        });
        const responseText = await response.text();
        //console.log('MiD API response:', responseText);
        if (requestId !== midValidationRequestRef.current) return;

        let data = null;
        try {
          data = JSON.parse(responseText);
        } catch (error) {
          data = null;
        }

        const normalizedText = responseText.trim().toLowerCase();
        const isExplicitlyInvalid =
          data === false ||
          data?.success === false ||
          data?.result === false ||
          data?.valid === false ||
          normalizedText === 'false';
        const isExplicitlyValid =
          data === true ||
          data?.success === true ||
          data?.result === true ||
          data?.valid === true ||
          normalizedText === 'true';
        const responseLooksValid =
          !isExplicitlyInvalid &&
          (isExplicitlyValid ||
            (response.ok &&
              responseText &&
              !/fault|invalid|not found|error/i.test(responseText)));

        setSendMaricopaData(responseLooksValid);
        if (responseLooksValid) {
          clearErrors('midValue');
        }
        onMidStatusChange?.({
          isRequired: true,
          isValid: responseLooksValid,
          isChecking: false,
        });

        if (!responseLooksValid) {
          setMidValueError(maricapaNoMatchText);
        }
        //console.log(midValueError, 'midValueError');
      } catch (error) {
        if (requestId !== midValidationRequestRef.current) return;

        setSendMaricopaData(false);
        setMidValueError('Unable to validate MID. Please try again.');
        onMidStatusChange?.({
          isRequired: true,
          isValid: false,
          isChecking: false,
        });
        console.error('API error:', error);
      }
    },
    [baseUrl, clearErrors, onMidStatusChange, setValue],
  );

  useEffect(() => {
    setShowMaricopaField(
      enableMaricopaField &&
        MARICOPA_INSTITUTION_IDS.includes(selectedInstitution?.value || ''),
    );
  }, [selectedInstitution]);

  useEffect(() => {
    setIsLoading(true);
    axios
      .get(`${localWebUrl}/asu_mypath_signup/json/commList`)
      .then((response) => {
        const instOptions = Object.entries(response.data).map(
          ([key, value]) => ({
            value: key,
            label: key === '001203' ? 'Glendale Community College, CA' : value,
          }),
        );

        setInstitutionOptions(instOptions);
        setIsLoading(false);
      })
      .catch((error) => {
        console.error('Error fetching data:', error);
        setIsLoading(false);
      });
  }, []);

  //css for radio buttons
  const radioStyle = {
    marginBottom: '10rem !important',
  };

  return (
    <div className="step1">
      <h4 className="highlight-gold">Step 1 of 3</h4>

      <div className="js-form-item react-form-item form-item js-form-type-radios  form-group">
        <label className="form-label js-form-required form-required">
          <span
            title="Required"
            className="fa fa-icon fa-circle uds-field-required"
          ></span>{' '}
          Which of these apply to you?
        </label>
        {campusOptions.map((option) => {
          const opkey = option.value; // Get the key of the object
          const label = option.label; // Get the label corresponding to the key

          return (
            <div
              className="js-form-item form-item js-form-type-radio form-check campusOptionCheck"
              key={opkey}
              style={radioStyle}
            >
              <input
                type="radio"
                id={`campusOption_${opkey}`}
                value={opkey}
                checked={valueCampus === opkey}
                onChange={handleCampusChange}
                name="campusData"
                className={`form-radio form-check-input ${invalid ? 'error-field' : ''} `}
              />
              <label
                className="form-check-label"
                htmlFor={`campusOption_${opkey}`}
              >
                {label}
              </label>
            </div>
          );
        })}
        {errors.campusData && (
          <span className="error">{errors.campusData.message}</span>
        )}
      </div>

      <div className="js-form-item form-item js-form-type-react-field form-item-inst js-form-item-inst form-group">
        <label className="form-label js-form-required form-required">
          <span
            title="Required"
            className="fa fa-icon fa-circle uds-field-required"
          ></span>
          What institution are you transferring from?
        </label>
        <Select
          id="institutionField"
          isLoading={isLoading}
          name="instituteData"
          value={selectedInstitution}
          placeholder="Select..."
          onChange={onInstituteChange}
          isClearable
          isSearchable
          options={institutionOptions}
          className={instinvalid ? 'error-field' : ''}
        />
        {errors.instituteData && (
          <span className="error">{errors.instituteData.message}</span>
        )}
      </div>

      {showMaricopaField && (
        <div className="form-group js-form-item js-form-item-last-name react-form-item form-item">
          <label className="form-label">Enter MEID</label>
          <input
            id="mid_field"
            type="text"
            {...register('midValue', {
              required: showMaricopaField ? 'MID is required' : false,
              pattern: {
                value: /^[a-zA-Z0-9\s\.,!?]*$/,
                message: 'Only plain text allowed',
              },
            })}
            value={midValue || ''}
            onChange={handleMidChange}
            className={errors.midValue || midValueError ? 'error-field' : ''}
            autoComplete="username"
          />
          {errors.midValue && (
            <span className="error">{errors.midValue.message}</span>
          )}
          {midValueError && (
            <span
              className="error"
              dangerouslySetInnerHTML={{
                __html: maricapaNoMatchText,
              }}
            />
          )}
        </div>
      )}
    </div>
  );
};

export default Step1;
