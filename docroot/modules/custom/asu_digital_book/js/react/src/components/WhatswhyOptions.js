import React, { useState, useEffect, useRef } from 'react';
import { useFormContext, Controller, useController } from 'react-hook-form';
import AOS from 'aos';
import 'aos/dist/aos.css'; 
import { useData } from './DrupalSettings';

const Whatsyouwhy = () => {
    const data = useData();
    const { control, register, formState: { errors } } = useFormContext();
    const whatsWhyOptions = [
        {'focused_futurist' : 'So I can get a good job and have a successful career.'},
        {'deep_diver' : 'To gain skills and knowledge about something I’m really interested in.'},
        {'trailblazer' : ' To help make an impact in the world.'},
        {'superfan' : 'To have the classic college experience.'},
        {'networker' : 'To meet new people and network.'}
    ]

    const {
        field: { onChange: ChangesetWhatsWhy, value: whatsWhy },
        fieldState: { invalid:whyInvalid }
        } = useController({
        name: 'whatsWhyField',
        control,
        defaultValue: '',
        rules: { required: 'Please select value' }
    });

   /*  const handleWhatsWhyCheckboxChange = (optionValue) => {
        console.log(optionValue);
        const newValue = whatsWhy.includes(optionValue)? whatsWhy.filter(item => item !== optionValue) : [...whatsWhy, optionValue];
        ChangesetWhatsWhy(newValue);
       
      }; */

    //AOS animation
    useEffect(() => {
        AOS.init({
          duration: 1000, // duration of the animation in milliseconds
          once: false, // whether animation should happen only once - while scrolling down
          mirror: true, // whether elements should animate out while scrolling past them
        });
    }, []);

    return (

            <div className="bg bg-top bg-percent-100 max-size-container center-container"> 
                <div className="container">
                   <div className="row">
                        <div className="block"> 
                            <div className="container">
                                <div className="row">
                                <div className="question3intro col-12 pb-2 pt-10" dangerouslySetInnerHTML={{ __html: data.whats_your_why || '' }} /> 
                                </div>
                                </div>
                        </div> 
                        <h5 data-aos="slide-left">Take a minute to consider your "why" below.</h5>
                        <div className="js-form-item form-item js-form-type-radios form-item-hs-value js-form-item-know-value form-group">

                        {whatsWhyOptions.map((whyoption, whyindex) => {
                        
                            const whykey = Object.keys(whyoption)[0]; 
                            const whylabel = whyoption[whykey]; 
                            return (
                                <div className="js-form-item form-item js-form-type-radio form-check"  data-aos="slide-left" key={whyindex}>
                                    <input 
                                        type="radio"
                                        id={`whyOptions${whykey}`}
                                        value={whykey}
                                        checked={whatsWhy === whykey}
                                        onChange={() => ChangesetWhatsWhy(whykey)}
                                        name="whatsWhyField"
                                        className={`form-radio form-check-input ${whyInvalid ?  "error-field" : ''} ` }
                                        
                                    />    
                                    <label className='form-check-label' htmlFor={`whyOptions${whykey}`} >
                                        {whylabel}
                                    </label><br />
                                    
                                </div>
                            );
                        })}
                        {whyInvalid && <span className="error error-message">{errors.whatsWhyField?.message}</span>}
                        </div>
                        
                    </div>
                </div>
            </div>
    );
}

export default Whatsyouwhy;