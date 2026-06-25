import React, {useEffect, useState} from 'react';
import { useFormContext, useController, useForm } from 'react-hook-form';
import { PhoneInput } from 'react-international-phone';
import axios from 'axios';
import Select from 'react-select';
import { useData } from './DrupalSettings';
import  statesData  from './usStates';
import VideoPlayer from './VideoPlayer';
import * as XLSX from 'xlsx';

const InvestData = () => {
    const { register, formState: { errors } } = useFormContext();
    const htmlData = '<i class="fa-custom fa-exclamation-triangle fas"></i>';
    const data = useData();
    const [countries, setCountries] = useState([]);
    const [usSates, setUsStates] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const statesList = statesData;
    const [isTextOpen, setIsTextOpen] = useState(false);

    /* const { control } = useForm({
      defaultValues: {
        countryOptionValue: 'USA' // Default value for the entire form
      }
    });
    */
    const {
      field: {  onChange: onCountryChangeOption, value: countryOptionValue },
    //  fieldState: { invalid:countryinvalid },
    } = useController({
      name: 'CountryField',
     // rules: { required: 'Please select country' },
    });

    const {
      field: {  onChange: onStateChangeOption, value: stateOptionValue },
     // fieldState: { invalid:stateinvalid },
    } = useController({
      name: 'StateField',
      defaultValue: '',
     // rules: { required: 'Please select where you are currently learning' },
    });

    const toggleTextContentData = () => {
      setIsTextOpen(!isTextOpen);
    }


    /* useEffect(() => {
      const fetchCountries = async () => {
        try {
          const response = await axios.get('https://restcountries.com/v3.1/all');
          const sortedCountries = response.data.sort((a, b) =>
            a.name.common.localeCompare(b.name.common)
          );

          // Find the US in the list and remove it
          const usCountry = sortedCountries.find(country => country.cca2 === 'US');
          const otherCountries = sortedCountries.filter(country => country.cca2 !== 'US');

          // Prepend US to the sorted list of other countries
          const countryOptions = [
            {
              value: usCountry.cca2,
              label: usCountry.name.common
            },
            ...otherCountries.map(country => ({
              value: country.cca2,
              label: country.name.common
            }))
          ];
          setCountries(countryOptions);

        } catch (err) {
          setError(err);
        } finally {
          setLoading(false);
        }
      };

      fetchCountries();
    }, []); */

    useEffect(() => {
      const fetchCountriesFromExcel = async () => {
        try {
          // Path to your Excel file
          const baseUrl = window.location.origin;
          let filePath = '';
          if(baseUrl.includes('dev')){
            filePath = `${baseUrl}/sites/g/files/litvpz896/files/2024-11/countries.xlsx`
          }
          else if(baseUrl.includes('test')){
            filePath = `${baseUrl}/sites/g/files/litvpz896/files/2025-06/countries.xlsx`
          }
          else{
            filePath = `${baseUrl}/sites/g/files/litvpz896/files/2025-06/countries.xlsx`
          }

          //console.log(filePath);
          // Fetch the file
          const response = await fetch(filePath);
          //console.log(response);
          const arrayBuffer = await response.arrayBuffer();

          // Read and parse the XLS file
          const workbook = XLSX.read(arrayBuffer, { type: 'array' });
          const sheetName = workbook.SheetNames[0];
          const sheet = workbook.Sheets[sheetName];

          // Convert the first sheet to JSON
          const jsonData = XLSX.utils.sheet_to_json(sheet);
          //console.log(jsonData);
          // Sort countries by name
          const sortedCountries = jsonData.sort((a, b) =>
            a.Country.localeCompare(b.Country)
          );
          //console.log(sortedCountries);
          // Separate the US from other countries
          const usCountry = sortedCountries.find((country) => country.Code === 'US');
          const otherCountries = sortedCountries.filter((country) => country.Code !== 'US');

          // Create country options
          const countryOptions = [
            {
              value: usCountry.Code,
              label: usCountry.Country,
            },
            ...otherCountries.map((country) => ({
              value: country.Code,
              label: country.Country,
            })),
          ];
          //console.log(countryOptions);
          setCountries(countryOptions);
        } catch (err) {
          setError('Failed to load countries from the Excel file.');
        } finally {
          setLoading(false);
        }
      };

      fetchCountriesFromExcel();
    }, []);



  return (
    <div className="invest">
      <div className="bg bg-top pb-10 bg-percent-100 max-size-container center-container">
        <div className="inHero" dangerouslySetInnerHTML={{ __html: data.in_hero || '' }} />

        <div className="investInYourself" dangerouslySetInnerHTML={{ __html: data.invest_in_yourself || '' }} />

        {/* <div className="inVideo" dangerouslySetInnerHTML={{ __html: data.in_video || '' }} /> */}

        <div className="row">
                 <div className='investVideoReact'><VideoPlayer videoSrc={data.in_video} poster={data.invent_poster}/></div>
        </div>

       {/*  <div className="fiskeRank container" dangerouslySetInnerHTML={{ __html: data.fiske_rank || '' }} /> */}

       <div className="fiskeRank">
                {!isTextOpen ? (
                    <div className='container'><div className='row'><div className="fiskeRank" dangerouslySetInnerHTML={{ __html: data.fiske_rank || '' }} /></div></div>
                ) : (
                  <div className='container'><div className='row'><div className="fiskeRank" dangerouslySetInnerHTML={{ __html: data.fiske_rank_text_content || '' }} /></div></div>

               )}

        </div>
        <div className='container'><div className='row'>
            <div className="TextMode">

                <div className="TextContentLink" onClick={toggleTextContentData}>
                    {isTextOpen ? (
                    <div dangerouslySetInnerHTML={{ __html: '<button class="btn btn-gold back-to-module-btn">Back to module</div>' || '' }} />
                      )  : (
                    <div dangerouslySetInnerHTML={{ __html: '<span class="noMattertextLink">View text version</span>' || '' }} />
                     )}
                </div>
            </div>
         </div>
      </div>


      </div>

        <div className="bg gray-2-bg bg-top bg-percent-100 max-size-container center-container">
          <div className="container custom-container">
            <div className="row">
              <div className="tuitionPrograms" dangerouslySetInnerHTML={{ __html: data.tuition_programs || '' }} />

              <div className="personalizedInformation" dangerouslySetInnerHTML={{ __html: data.personalized_information || '' }} />

             <div className="js-form-item form-item js-form-type-select form-item-interest js-form-item-interest form-group col-sm-4">
                <label htmlFor="country">What country do you live in?</label>
                <Select
                  name="CountryField"
                  id="CountryField"
                  value={countryOptionValue}
                  onChange={onCountryChangeOption}
                  placeholder="Select..."
                  options={countries}
                 // className={`digitlCustomSelect ${countryinvalid ? 'error-field' : ''}`}
                 className={'digitlCustomSelect'}
                  isClearable
                  isSearchable
                />
              </div>
        </div>


        {countryOptionValue && countryOptionValue.value === 'US' && (
        <div className="container custom-container">
        <div className="row">
        <div className="js-form-item form-item js-form-type-select form-item-interest js-form-item-interest form-group col-sm-4">
          <label htmlFor="state">What state do you live in?</label>
          <Select
            name="stateField"
            id="stateField"
            value={stateOptionValue}
            onChange={onStateChangeOption}
            placeholder="Select..."
            options={statesData}
            //className={`digitlCustomSelect ${stateinvalid ? 'error-field' : ''}`}
            className={'digitlCustomSelect'}
            isClearable
            isSearchable
          />
          </div> </div>
          </div>
        )}

        {/* AZ custom content */}
        {stateOptionValue && stateOptionValue.value === 'AZ' && (
          <div className="container">
            <div className="row">
              <div className="AzContent" dangerouslySetInnerHTML={{ __html: data.arizona_content || '' }} />
              </div>
            </div>
        )}

        {/* CA custom content */}
        {stateOptionValue && stateOptionValue.value === 'CA' && (
          <div className="container">
            <div className="row">
              <div className="CAContent" dangerouslySetInnerHTML={{ __html: data.california_content || '' }} />
            </div>
          </div>
        )}

        {/* WUE custom content */}
        {stateOptionValue && (stateOptionValue.value === 'AK' || stateOptionValue.value === 'CO' || stateOptionValue.value === 'HI' || stateOptionValue.value === 'ID' || stateOptionValue.value === 'MT' || stateOptionValue.value === 'NV' || stateOptionValue.value === 'ND' || stateOptionValue.value === 'OR' || stateOptionValue.value === 'SD' || stateOptionValue.value === 'UT' || stateOptionValue.value === 'WA' || stateOptionValue.value === 'WY') && (
            <div className="container">
              <div className="row">
                <div className="WueContent" dangerouslySetInnerHTML={{ __html: data.oos_wue_states_content || '' }} />
              </div>
            </div>
        )}

        {/* Other OOS custom content */}
        {stateOptionValue && (stateOptionValue.value !== 'AZ' && stateOptionValue.value !== 'CA' && stateOptionValue.value !== 'AK' && stateOptionValue.value !== 'CO' && stateOptionValue.value !== 'HI' && stateOptionValue.value !== 'ID' && stateOptionValue.value !== 'MT' && stateOptionValue.value !== 'NV' && stateOptionValue.value !== 'ND' && stateOptionValue.value !== 'OR' && stateOptionValue.value !== 'SD' && stateOptionValue.value !== 'UT' && stateOptionValue.value !== 'WA' && stateOptionValue.value !== 'WY') && (
                  <div className="container">
                    <div className="row">
                      <div className="otherContent" dangerouslySetInnerHTML={{ __html: data.oos_other_states_content || '' }} />
                    </div>
                  </div>
        )}

        {/* International custom content */}
        {countryOptionValue && countryOptionValue.value !== 'US' && (
            <div className="container">
              <div className="row">
                  <div className="intContent" dangerouslySetInnerHTML={{ __html: data.international_content || '' }} />
              </div>
            </div>
        )}
      </div>
      </div>

      {/* so what's next content */}
      <div className="container">
          <div className="row">
              <div className="WhatNext" dangerouslySetInnerHTML={{ __html: data.what_next || '' }} />
          </div>
      </div>

      {/* {(countryOptionValue && countryOptionValue.value === 'US' && stateOptionValue && stateOptionValue.value) && (
         <div className="container">
            <div className="row">
                <div className="WhatNext" dangerouslySetInnerHTML={{ __html: data.what_next || '' }} />
            </div>
          </div>
      )||(countryOptionValue &&  countryOptionValue.value !== 'US' && (
          <div className="container">
          <div className="row">
              <div className="WhatNext" dangerouslySetInnerHTML={{ __html: data.what_next || '' }} />
          </div>
        </div>
      )
      )} */}
    </div>
  );
};

export default InvestData;
