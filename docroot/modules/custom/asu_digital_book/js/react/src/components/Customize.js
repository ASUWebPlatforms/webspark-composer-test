import React, { useState, useEffect } from 'react';
import { useFormContext, useController } from 'react-hook-form';
import axios from 'axios';
import Select from 'react-select';
import { useData } from './DrupalSettings';
import VideoPlayer from './VideoPlayer';

const Customize = () => {
    const { formState: { errors }, register } = useFormContext();
    const [institutionOptions, setInstitutionOptions] = useState([]);
    const [isLoading, setIsLoading] = useState(false);
    const data = useData();
    //console.log(data);
    const campusOptions = [
        { value: "GROUND", label: "Primarily in person with the option of online classes." },
        { value: "ONLNE", label: " Fully online." },
        { value: "NotSure", label: "I\'m not sure." },
    ];

 
    const {
        field: { onChange: onChangeCampus, value: valueCampus},
        //fieldState: { invalid }
    } = useController({
        name: 'campusData',
       // rules: { required: 'Please select an option' }
    });

    

    return (
        <div className='customize'>

             <div className="cusHero" dangerouslySetInnerHTML={{ __html: data.cus_hero || '' }} />

            {/* <div className="customizeVideo" dangerouslySetInnerHTML={{ __html: data.customize_video || '' }} /> */}

            <div className="customizeVideo row"> 
                 <div className='customizeVideoReact customVideo'><VideoPlayer videoSrc={data.customize_video} poster={data.customize_poster}/></div>
             </div>

            <div className="whereYouWantToStudy" dangerouslySetInnerHTML={{ __html: data.where_you_want_to_study || '' }} />

            <div className="findLearningEnvironment" dangerouslySetInnerHTML={{ __html: data.find_learning_environment || '' }} />

            <div className="asuCampusesVideo" dangerouslySetInnerHTML={{ __html: data.asu_campuses_video || '' }} /> 

            {/*  <div className="row"> 
            <div className='asuCampusesVideo'><VideoPlayer videoSrc={data.asu_campuses_video} /></div>
            </div> */}

            <div className="optionsToLearnOnline" dangerouslySetInnerHTML={{ __html: data.options_to_learn_onine || '' }} />

            <div className="bg gray-1-bg bg-top pb-10 pt-10 bg-percent-100 max-size-container center-container">
            <div className="container ">
            <div className="row">
            <div className="js-form-item form-item js-form-type-radios  form-group">
            <label htmlFor="campusField">Where do you want to study?</label>
               {campusOptions.map((option) => {
                const opkey = option.value; // Get the key of the object
                const label = option.label; // Get the label corresponding to the key
               
                return (
                    <div className="js-form-item form-item js-form-type-radio form-check campusOptionCheck" key={opkey}>
                       <input
                            type="radio"
                            id={`campusOption_${opkey}`}
                            value={opkey}
                            checked={valueCampus === opkey}
                            onChange={onChangeCampus}
                            name="campusData"
                           // className={`form-radio form-check-input ${invalid ?  "error-field" : ''} ` }
                           className={`form-radio form-check-input`}
                           
                        /> 
                        <label className='form-check-label' htmlFor={`campusOption_${opkey}`}> 
                            {label}
                        </label>
                    </div>
                    );
             
                })}
                 {/* {errors.campusData && <span className="error">{errors.campusData.message}</span>} */}
            </div>

            
           </div>
           </div>
           </div>
        </div>
    );
};

export default Customize;