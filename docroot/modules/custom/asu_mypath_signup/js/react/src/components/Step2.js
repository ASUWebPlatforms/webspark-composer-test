// Step2.jsx
import React, { useState, useEffect } from 'react';
import {
  useForm,
  FormProvider,
  createContext,
  useFormContext,
  useController,
  useWatch,
} from 'react-hook-form';
import axios from 'axios';
import Select from 'react-select';
import { pushToDataLayer } from '../utils/dataLayer';

const Step2 = ({
  onChangeField,
  nextStep,
  prevStep,
  localWebUrl,
  campusChange,
}) => {
  const initialProgText =
    "<h5>Program of interest</h5><div className='programModalButton' >Select program</div>";
  const {
    register,
    setValue,
    formState: { errors },
  } = useFormContext();
  const { campusData } = useFormContext().watch();

  const campsVal = campusData;
  //const campusSelected = campsVal.value;
  const campusSelected = campsVal;

  const svgDataURL =
    "data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3E%3C/svg%3E";

  //const campusSelected = 'ONLNE';
  const [isOpen, setIsOpen] = useState(false);
  const [isProgLoaded, setIsProgLoaded] = useState(false);
  const [isInstLoading, setIsInstLoading] = useState(false);
  const [isCollegeLoading, setIsCollegeLoading] = useState(false);
  const [programOptions, setProgramOptions] = useState([]);
  const [interestOptions, setInterestOptions] = useState([]);
  const [collegeOptions, setCollegeOptions] = useState([]);
  const [selectedInterestValue, setSelectedInterest] = useState();
  const [selectedInterestData, setSelectedInterestData] = useState(null);
  const [selectedCollege, setSelectedCollege] = useState();
  const [selectedCollegeCode, setSelectedCollegeCode] = useState();
  const [selectedDegreeCode, setSelectedDegreeCode] = useState();
  const [selectedCollegeData, setSelectedCollegeData] = useState(null);
  const [selectedProgram, setSelectedProgram] = useState();
  const [selectedProgramData, setSelectedProgramData] = useState(null);
  const [selectedProgramHtml, setSelectedProgramHtml] = useState();
  const [htmlContent, setHtmlContent] = useState(initialProgText);
  const [buttonContent, setButtonContent] = useState();
  const [resetbuttonContent, setResetButtonContent] = useState('no');
  const [inputValue, setInputValue] = useState('');
  const [degreeSelected, setdegreeSelected] = useState('no');
  const [isElementVisible, setIsElementVisible] = useState(true);
  const [isActive, setIsActive] = useState(false);
  const [hiddenFieldValue, setHiddenFieldValue] = useState('');
  const [progTextArea, setProgTextArea] = useState();
  const [inputTextValue, setInputTextValue] = useState('');
  const [loading, setLoading] = useState(true);
  const [dataLoading, setDataLoading] = useState('');
  const [selectedProgramName, setSelectedProgramName] = useState('');

  const classNames = [
    'modal',
    'programModal',
    'myPathModal',
    isActive ? 'active' : 'inactive',
  ];

  const agreementInfo =
    "<p class='mypath-consent-wording'>As part of the transfer admission guarantee, by partnering community college and ASU may share information about my admissions and transcript information with each other. I am subject to all policies, rules and conditions of each institution (the partnering community college and ASU).</p>";

  const noResultsText =
    "<p>&nbsp;</p><p><span class='fa-circle-exclamation fa-solid'></span> Sorry, no results were found based on your current search criteria. Please try again.</p><p>Search suggestions:<ul class='noResultsList'><li>- Check your spelling.</li><li>- Try different words that mean the same thing.</li><li>- Try more general words.</li><li>- Clear search.</li></ul></p>";

  //function load change seletion button only after programs data loads
  useEffect(() => {
    // Simulating loading delay with setTimeout
    const timeout = setTimeout(() => {
      setIsProgLoaded(true); // Set isLoaded to true after a delay
    }, 5000); // Adjust the delay as needed (in milliseconds)

    // Clear the timeout to prevent memory leaks
    return () => clearTimeout(timeout);
  }, []);

  // Use useController to manage the selected option
  const {
    field: { onChange: onChangeOption, value: optionValue },
    fieldState: { invalid: invalidOption, error: errorOption },
  } = useController({
    name: 'selectedProgram',
    defaultValue: '',
    rules: { required: 'Please select an option' },
  });

  // Use useController to manage the selected option
  const {
    field: { onChange: ChangesetStep2Value, value: step2Value },
    fieldState: { invalid: invalidstep2Option },
  } = useController({
    name: 'step2ValueField',
    defaultValue: '',
    rules: { required: 'Please select an option' },
  });

  const {
    field: { onChange: onChangeStep2Option, value: hiddenText },
    fieldState: { invalid: invalidstepOption },
  } = useController({
    name: 'hiddenTextProgField',
    defaultValue: '',
    rules: { required: 'Please select an option' },
  });

  const {
    field: { onChange: onChangeCollegeCode, value: valueCollegeCode },
    fieldState: { invalid: invalidCollegeCode, error: errorCollegeCode },
  } = useController({
    name: 'CollegeCodeData',
    defaultValue: '',
    //rules: { required: 'Please select an option' }
  });

  const {
    field: { onChange: onChangeDegreeCode, value: valueDegreeCode },
    fieldState: { invalid: invalidDegreeCode, error: errorDegreeCode },
  } = useController({
    name: 'degreeCodeData',
    defaultValue: '',
    //rules: { required: 'Please select an option' }
  });

  const toggleModal = (className) => {
    setIsOpen(!isOpen);
    setHtmlContent('');
    if (!isOpen) {
      // Add overflow: hidden to body when modal is open
      document.body.style.overflow = 'hidden';
    } else {
      // Reset overflow when modal is closed
      document.body.style.overflow = '';
    }

    // Set the visibility of Programs dropdown
    setIsElementVisible(!isOpen);
    setIsActive(!isActive);
    //push to data layer
    if (className) {
      /* if (className.includes("programModalButton")) {
                pushToDataLayer({
                    action: "click",
                    event: "select",
                    name: "onclick",
                    region: "main content",
                    section: "mypath2asu2",
                    text: "value of the dropdown",
                    type: "choose a program"
                }); 
            }  */
      if (className.includes('programModalResetButton')) {
        pushToDataLayer({
          action: 'click',
          event: 'link',
          name: 'onclick',
          region: 'main content',
          section: 'mypath2asu2',
          text: 'change selection',
          type: 'internal link',
        });
      }
    }
  };

  const handleDegreeSelectChange = (selectedOption) => {
    ///console.log('selectedOption', selectedOption);
    setSelectedProgram(selectedOption);
    toggleModal();
    onChangeOption(selectedOption.value);
    setSelectedProgramData(selectedOption.value);
    setdegreeSelected('yes');
    updateProgramData(selectedOption.value);
    //setStep2Value(selectedOption.value);
    ChangesetStep2Value(selectedOption.value);
    onChangeField(selectedOption.value);
    //Push to data layer
    pushToDataLayer({
      action: 'click',
      event: 'select',
      name: 'onclick',
      region: 'main content',
      section: 'mypath2asu2',
      text: 'choose program',
      type: 'internal link',
    });

    pushToDataLayer({
      action: 'click',
      event: 'select',
      name: 'onclick',
      region: 'main content',
      section: 'mypath2asu2',
      text: selectedOption.value,
      type: 'choose a program',
    });
  };

  const setResetButton = () => {
    const resetButtonText = (
      <button
        className="programModalResetButton"
        onClick={(e) => toggleModal(e.currentTarget.className)}
      >
        Change selection
      </button>
    );
    setButtonContent(resetButtonText);
  };

  const handleHiddenInputChange = (event) => {
    const value = event.target.value;

    //setStep2Value(value);
    ChangesetStep2Value(selectedOption.value);
    onChangeField(value); // Pass the value to the parent component
    //onChangeStep2Option(value);
  };

  const handleInputTextAreaChange = (event) => {
    const value = event.target.value;

    hiddenText = campusChange == true ? '' : value;
    setProgTextArea(value);
    //onTextAreaChange(value);
    //onChangeStep2Option(value);
  };

  const handleInputChange = (e) => {
    const inputValue = e.target.value;
    setInputValue(inputValue);
  };

  const handleKeywordBlur = (event) => {
    //Push to data layer
    const value = event.target.value;
    if (value) {
      pushToDataLayer({
        action: 'click',
        event: 'select',
        name: 'onclick',
        region: 'main content',
        section: 'mypath2asu2',
        text: value,
        type: 'search for keywords',
      });
    }
  };

  const updateProgramData = (programSelected) => {
    let program = '';
    if (programSelected.includes('-')) {
      // Split the string by the hyphen
      const splitArray = programSelected.split('-');
      program = splitArray[1];
      //setProgramCode(splitArray[0]);
    } else {
      program = programSelected;
    }
    //console.log(program);
    const programDataUrl = `${localWebUrl}/asu_mypath_signup/json/programs/${campusSelected}-${program}`;
    //console.log(programDataUrl);
    setDataLoading('yes');
    axios
      .get(programDataUrl)
      .then((programDataresponse) => {
        const programData = Object.entries(programDataresponse.data).map(
          ([key, value]) => ({
            value: key,
            label: value,
          }),
        );

        const collegeCode = programData.find(
          (item) => item.value === 'collegeCode',
        )?.label;
        const degreeCode = programData.find(
          (item) => item.value === 'degreeCode',
        )?.label;
        const Proghtml = programData.find(
          (item) => item.value === 'progDetails',
        )?.label;
        const html = `<div>${Proghtml || ''}</div>`;
        const programName = programData.find(
          (item) => item.value === 'programName',
        );
        const collageName = programData.find(
          (item) => item.value === 'collegeName',
        )?.label;

        //const html = programData.map(data => `<div>${data.label}</div>`).join('');
        setHtmlContent(html);
        setSelectedProgramHtml(html);
        setSelectedCollegeCode(collegeCode);
        setSelectedDegreeCode(degreeCode);
        setSelectedProgramName(programName);
        setValue('programName', programName?.label || '');
        setValue('colleNameData', collageName || '');
        onChangeCollegeCode(collegeCode);
        onChangeDegreeCode(degreeCode);
        onChangeStep2Option(html);
      })
      .catch((error) => {
        console.error('Error fetching data:', error);
      })
      .finally(() => {
        setDataLoading('no'); // This will execute after the promise settles, success or error
      });
    // Set resetButtonContent
    setResetButtonContent('yes');
  };

  //get programs list from campus
  useEffect(() => {
    if (campusSelected) {
      const degreeapiUrl = `${localWebUrl}/asu_mypath_signup/json/programs/${campusSelected}`;
      const fetchOptions = async () => {
        setLoading(true); // Set loading to true while fetching
        try {
          const degreeresponse = await axios.get(degreeapiUrl);
          ///console.log('Degree API response:', degreeresponse.data); // Log the API response
          const programOptionData = Object.entries(degreeresponse.data).map(
            ([key, value]) => ({
              value: key,
              label: value,
            }),
          );
          setProgramOptions(programOptionData || []);
        } catch (error) {
          console.error('Error fetching options:', error);
          setProgramOptions([]); // Set empty array on error or failed fetch
        } finally {
          setLoading(false); // Set loading to false once the request is done
        }
      };

      fetchOptions();
    }
  }, [campusSelected]);

  //get college list
  useEffect(() => {
    if (campusSelected) {
      const collegeUrl = `${localWebUrl}/asu_mypath_signup/json/college/${campusSelected}`;
      axios
        .get(collegeUrl)
        .then((collegeresponse) => {
          const collegeOptionData = Object.entries(collegeresponse.data).map(
            ([key, value]) => ({
              value: key,
              label: value,
            }),
          );
          setCollegeOptions(collegeOptionData);
        })
        .catch((error) => {
          console.error('Error fetching data:', error);
        });
    }
  }, [campusSelected]);

  //get interest area list
  useEffect(() => {
    if (campusSelected) {
      const interestUrl = `${localWebUrl}/asu_mypath_signup/json/interest/${campusSelected}/Transfer`;
      axios
        .get(interestUrl)
        .then((interestresponse) => {
          const interestOptionData = Object.entries(interestresponse.data).map(
            ([key, value]) => ({
              value: key,
              label: value,
            }),
          );
          setInterestOptions(interestOptionData);
        })
        .catch((error) => {
          console.error('Error fetching data:', error);
        });
    }
  }, [campusSelected]);

  const handleInterestSelectChange = (selectedOption) => {
    setSelectedInterest(selectedOption);
    //setCollegeOptions[collegeOptions[0]];
    const intVal = selectedOption ? selectedOption.value : '';
    const intLabel = selectedOption ? selectedOption.label : '';
    setSelectedInterestData(intVal);
    setInputTextValue(intLabel);
    setSelectedCollege(null);
    //Push to data layer
    pushToDataLayer({
      action: 'click',
      event: 'select',
      name: 'onclick',
      region: 'main content',
      section: 'mypath2asu2',
      text: selectedOption.value,
      type: 'area of interest',
    });
  };

  const handleCollegeSelectChange = (selectedOption) => {
    setSelectedCollege(selectedOption);
    const colVal = selectedOption ? selectedOption.value : '';
    setSelectedCollegeData(colVal);
    setSelectedInterest(null);
    //Push to data layer
    pushToDataLayer({
      action: 'click',
      event: 'select',
      name: 'onclick',
      region: 'main content',
      section: 'mypath2asu2',
      text: selectedOption.value,
      type: 'search by college',
    });
  };

  //get programs list from interest area selection
  useEffect(() => {
    setIsInstLoading(true);
    const interestValue = selectedInterestData;
    if (campusSelected && selectedInterestData) {
      const intdegreeapiUrl = `${localWebUrl}/asu_mypath_signup/json/degree/${campusSelected}/${encodeURIComponent(selectedInterestData)}/Transfer`;
      axios
        .get(intdegreeapiUrl, { timeout: 5000 })
        .then((intdegreeresponse) => {
          const intprogramOption = Object.entries(intdegreeresponse.data).map(
            ([key, value]) => ({
              value: key,
              label: value,
            }),
          );
          setProgramOptions(intprogramOption);
          setIsInstLoading(false);
        })
        .catch((error) => {
          console.error('Error fetching data:', error);
          setIsInstLoading(false);
        });
    } else {
      if (campusSelected) {
        const degreeapiUrl = `${localWebUrl}/asu_mypath_signup/json/programs/${campusSelected}`;
        //console.log(degreeapiUrl);
        axios
          .get(degreeapiUrl)
          .then((degreeresponse) => {
            const programOptionData = Object.entries(degreeresponse.data).map(
              ([key, value]) => ({
                value: key,
                label: value,
              }),
            );
            setProgramOptions(programOptionData);
          })
          .catch((error) => {
            console.error('Error fetching data:', error);
          });
      }
    }
  }, [campusSelected, selectedInterestData]);

  //get programs list from colleges selection
  useEffect(() => {
    setIsCollegeLoading(true);
    console.log(selectedCollegeData, 'selectedCollegeData');
    console.log(campusSelected, 'campusSelected');
    if (campusSelected && selectedCollegeData) {
      const coldegreeapiUrl = `${localWebUrl}/asu_mypath_signup/json/degreeCollege/${campusSelected}/${encodeURIComponent(selectedCollegeData)}/Transfer`;
      axios
        .get(coldegreeapiUrl, { timeout: 5000 })
        .then((coldegreeresponse) => {
          const colprogramOption = Object.entries(coldegreeresponse.data).map(
            ([key, value]) => ({
              value: key,
              label: value,
            }),
          );
          setProgramOptions(colprogramOption);
          setIsCollegeLoading(false);
        })
        .catch((error) => {
          console.error('Error fetching data:', error);
          setIsCollegeLoading(false);
        });
    } else {
      if (campusSelected) {
        const degreeapiUrl = `${localWebUrl}/asu_mypath_signup/json/programs/${campusSelected}`;
        //console.log(degreeapiUrl);
        axios
          .get(degreeapiUrl)
          .then((degreeresponse) => {
            const programOptionData = Object.entries(degreeresponse.data).map(
              ([key, value]) => ({
                value: key,
                label: value,
              }),
            );
            setProgramOptions(programOptionData);
          })
          .catch((error) => {
            console.error('Error fetching data:', error);
          });
      }
    }
  }, [campusSelected, selectedCollegeData]);

  // Filter options based on the input value
  const filteredOptions = programOptions.filter((option) =>
    option.label.toLowerCase().includes(inputValue.toLowerCase()),
  );

  // Further filter out the "Select..." option
  const optionsToRender = filteredOptions.filter(
    (option) => option.label !== 'Select...',
  );

  return (
    <div className="step2">
      <h4 className="highlight-gold">Step 2 of 3</h4>
      <div className="form-group">
        {isOpen && (
          //<div className="modal" tabIndex="-1" style={{ display: 'block', position: 'relative' }}>
          <div className={classNames.join(' ')}>
            <div className="innerModalDiv">
              <div className="modal-content programModal">
                {/* Modal content */}
                <div className="modal-custom-header">
                  <div className="header-left">
                    <h2>Search for a program</h2>
                  </div>
                  <div
                    className="Modalclose header-right"
                    onClick={(e) => toggleModal(e.currentTarget.className)}
                    dangerouslySetInnerHTML={{
                      __html: '<i class="fa fa-times-circle"></i>',
                    }}
                  />
                </div>
                <span>
                  You can search by a combination of keywords and filters.
                </span>

                <div className="prog_all_option">
                  <div className="form-group filterOptions">
                    <label htmlFor="textBox">Search for keywords</label>
                    <input
                      className="form-textfield modal-custom-field form-control"
                      type="text"
                      value={inputValue}
                      onChange={handleInputChange}
                      onBlur={handleKeywordBlur}
                      placeholder="Search..."
                    />
                  </div>
                  <span className="Ordata">OR</span>
                  <div className="form-group filterOptions">
                    <label htmlFor="selectInput">Area of interest</label>

                    <Select
                      id="selectInput"
                      onChange={handleInterestSelectChange}
                      value={selectedInterestValue}
                      placeholder="Select..."
                      name="InterestData"
                      isClearable
                      options={interestOptions}
                      inputRef={register} // Register select input
                      className="modal-custom-select-field"
                    />
                  </div>
                  <span className="Ordata">OR</span>
                  <div className="form-group filterOptions">
                    <label htmlFor="selectCollege">Search by college</label>
                    <Select
                      id="selectCollege"
                      name="collegeData"
                      options={collegeOptions}
                      value={selectedCollege}
                      isClearable
                      onChange={handleCollegeSelectChange}
                      className="modal-custom-select-field"
                    />
                  </div>
                </div>
                <span className="highlight-gold">
                  {filteredOptions.length} programs available
                </span>
                {/* if it returns no results, add no results text */}
                {loading ? (
                  <div>
                    <p>Loading options...</p>
                  </div> // Show a loading message or spinner
                ) : optionsToRender.length === 0 ? (
                  <div dangerouslySetInnerHTML={{ __html: noResultsText }} />
                ) : (
                  <div className="prog_options_list">
                    {optionsToRender.map((option, index) => (
                      <div className="porgOptionsDiv" key={index}>
                        <div className="progSelectLabbel">
                          <span
                            dangerouslySetInnerHTML={{ __html: option.label }}
                          />
                        </div>
                        <div className="progSelectButton">
                          <button
                            className="react-btn btn btn-dark"
                            onClick={() => handleDegreeSelectChange(option)}
                          >
                            Choose program
                          </button>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
                <span dangerouslySetInnerHTML={{ __html: agreementInfo }} />
              </div>
            </div>
          </div>
        )}
      </div>
      <div className="form-group">
        {' '}
        {/*This field is used to keep track if program is selected and enabled or disable the next button*/}
        <input
          type="hidden"
          value={step2Value || ''}
          onChange={handleHiddenInputChange}
          placeholder="Enter value"
          name="step2valueField"
        />
      </div>

      <div className="form-group">
        <input
          type="hidden"
          name="hiddenTextProgField"
          value={hiddenText || ''}
          onChange={handleInputTextAreaChange}
        />
        {dataLoading === 'yes' ? (
          <div className="loading">Loading, please wait...</div>
        ) : hiddenText ? (
          <div>
            <div
              className="programInformationAfter"
              dangerouslySetInnerHTML={{ __html: hiddenText }}
            />
            <div>
              <button
                type="button"
                className="programModalResetButton react-btn btn btn-dark"
                onClick={(e) => toggleModal(e.currentTarget.className)}
              >
                Change selection
              </button>
            </div>
          </div>
        ) : (
          !isOpen && (
            <div className="programInitialField">
              <div>
                <label className="form-label js-form-required form-required">
                  <span
                    title="Required"
                    class="fa fa-icon fa-circle uds-field-required"
                  />
                  Choose a program
                </label>
              </div>
              <div
                className="programModalButton"
                onClick={(e) => toggleModal(e.currentTarget.className)}
              >
                <span className="custom-text-muted">Search...</span>
              </div>
              {/*  <p className="description">Please choose a program from the available options</p> */}
            </div>
          )
        )}
      </div>
    </div>
  );
};

export default Step2;
