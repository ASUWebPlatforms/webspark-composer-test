// Step2.jsx
import React, { useState, useEffect, useRef } from 'react';
import { useForm, FormProvider, createContext, useFormContext, useController, useWatch } from 'react-hook-form';
import Select from 'react-select';
import { useData } from './DrupalSettings';
import YoutubePlayer from './YoutubePlayer';
import VideoPlayer from './VideoPlayer';

const LearningData = ( { onChangeField, nextStep, prevStep, localWebUrl }) => {
    const data = useData();
   // console.log(data);
    const initialProgText = "<h5>Program of interest</h5><div className='programModalButton' >Select program</div>";
    const { register,formState: { errors } } = useFormContext();
    const [HsSelectedOption, setHsSelectedOption] = useState('');
    const [showPopup, setShowPopup] = useState(false);
    const [popupStyle, setPopupStyle] = useState({});
    const [currentLearning, setCurrentLearning] = useState([]);
    const [highschoolOptions, setHighschoolOptions] = useState([]);
    const [knowStudy, setKnowStudy] = useState([]);
    const [interestOpt, setInterestOpt] = useState([]);
    const [yourMind, setYourMind] = useState([]);
    const optionRefs = useRef([]);
    const [isOpen, setIsOpen] = useState(false);
    const { getValues } = useFormContext();
    const values = getValues();
    //console.log(values);
    const whyOptions = values.whatsWhyField;
    const videoValue = whyOptions?`learning_${whyOptions}_video`:'learning_focused_futurist_video';
    //console.log(videoValue);
    //console.log(data[videoValue]);
    
    const learningOptions = [
        { "hs" : "I'm in high school." },
        { "Transfer" : "I'm in a community college or other college." },
        { "other" : "I'm not currently in high school."}, 
    ];

    const nextOptions = [
        {"Associate" : "I plan to earn my associate degree."},
        {"UGRAD" : "I plan to earn my bachelor's degree."},
        {"GRAD" : "I plan to earn an advanced degree such as a master\'s or PhD."}
    ]

    const popupOptions = [
        { value: 'High school senior', label: "12th grade", message: 'This is the time to finish your college applications, apply for financial aid and maintain your grades.' },
        { value: 'High school junior', label: "11th grade", message: 'This is the time to start researching and visiting colleges, and take the ACT or SAT.' },
        { value: 'High school sophomore', label: "10th grade", message: 'This is the time to start preparing for the ACT or SAT, and become involved in extracurriculars or volunteer opportunities.' },
        { value: 'High school freshman', label: '9th grade', message: 'This is the time to consider taking AP courses, and begin exploring careers you may be interested in.'}
      ];

    const hsOptions = [
        { "High school senior" : "12th grade." },
        { "High school junior" : "11th grade." },
        { "High school sophomore" : "10th grade." },
        { "High school freshman" : "9th grade." }
    ]; 

    const whatToStudy = [
        { value: "yes", label: "Yes." },
        { value: "no", label: "No." }
    ];

    const militaryOptions = [
        { value: "Veteran", label: "Yes." },
        { value: "no", label: "No." }
    ];

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
        {value: "Sustainability", label: "Sustainability"}

    ];

    const {
        field: {  onChange: onLearningChangeOption, value: LearningOptionValue },
        fieldState: { invalid:linvalid },
      } = useController({
        name: 'CurrentLearningField',
        defaultValue: '',
        rules: { required: 'Please select where you are currently learning' },
      });

    const {
        field: {  onChange: onNextChangeOption, value: NextOptionValue },
       // fieldState: { invalid:nextInvalid },
      } = useController({
        name: 'NextStepsField',
        defaultValue: '',
       // rules: { required: 'Please select what\'s next' },
      });

      const {
        field: {  onChange: onMilitaryChangeOption, value: MilitaryOptionValue },
       // fieldState: { invalid:militaryInvalid },
      } = useController({
        name: 'MilitaryField',
        defaultValue: '',
       // rules: { required: 'Please select what\'s next' },
      });  

    // Use useController to manage the selected option
    const {
        field: { onChange: ChangesetHsValue, value: hsValue },
       // fieldState: { invalid:hsInvalid }
    } = useController({
        name: 'hsValueField',
        defaultValue: '',
       // rules: { required: 'Please select grade' }
    });

/*     const {
        field: { onChange: onChangeInterestStudy, value: interestStudyValue },
       // fieldState: { invalid: invalidInterestValue }
    } = useController({
        name: 'knowStudyField',
       // rules: { required: 'Please select what about you option' }
    }); */


    const {
        field: { onChange: onChangeInterestOption,  value: valueInterest},
        fieldState: { invalid: invalidinterestOptions, error: errorInterestOptions }
    } = useController({
        name: 'interestOptionsField',
        defaultValue:'',
        //rules: { required: 'Please select an option' }
    });

    const handleLearningChangeOption = (selectOption) => {
        setCurrentLearning(selectOption.target.value);
        onLearningChangeOption(selectOption.target.value);
        if(selectOption.target.value !== "hs"){
            ChangesetHsValue('')
        }
        setHighschoolOptions('');
        
        if(selectOption.target.value === "Transfer"){
            setShowPopup(true);
            setPopupStyle({
                display: 'block',
                top: `${95}px`,
            });
        }
    }

    const handleNextChangeOption = (event) => {
        onNextChangeOption(event.target.value);
    }

    const handleHsChange = (event, index) => {
        const hsSelected = event.target.value;
        let ht = 0;
        if(hsSelected === "High school senior"){
            ht = 50;
        }
        if(hsSelected === "High school junior"){
            ht = 80;
        }
        if(hsSelected === "High school sophomore"){
            ht = 115;
        }
        if(hsSelected === "High school freshman"){
            ht = 140;
        }
        ChangesetHsValue(event.target.value);
        setHsSelectedOption(event.target.value);
        setShowPopup(true);
        const rect = optionRefs.current[index].getBoundingClientRect();
        setPopupStyle({
          display: 'block',
          //top: `${rect.top + window.scrollY + rect.height / 2}px`,
          //top: `${window.scrollY + rect.top-255}px`,
          top: `${60+ht}px`,
          //left: `${rect.left + window.scrollX }px`
        });
    }

    const closePopup = () => {
        setShowPopup(false);
    };

    const toggleTextContent = () => {
        setIsOpen(!isOpen);

    }

    return (
        
    <div className='LearningModality col-12'>
        <div className="layout-full-width">
         <div dangerouslySetInnerHTML={{ __html: data.hero|| '' }}></div>
         </div>
         {/*<div className="layout-full-width">
          <div className="row" dangerouslySetInnerHTML={{ __html: data[videoValue] || '' }}></div>
        </div> */}
        <div className="row"> 
             <div className='LearnVideoReact customVideo'><VideoPlayer videoSrc={ data[videoValue]} /></div>
        </div> 
        <div className="bg bg-top  pb-8 pt-10 bg-percent-100 max-size-container center-container">
        <div className="container">
            <div className="row">
            <div className="js-form-item form-item js-form-type-radios form-item-hs-value js-form-item-know-value form-group" data-aos="slide-left"><label className="form-label js-form-required form-required">What's next for you?</label>

                {nextOptions.map((nextoption, index) => {
                        const nextkey = Object.keys(nextoption)[0]; 
                        const nextlabel = nextoption[nextkey]; 
                        return (
                                <div className="js-form-item form-item js-form-type-radio form-check" key={index}>
                                    <input 
                                        type="radio"
                                        id={`nextOptions${nextkey}`}
                                        value={nextkey}
                                        checked={NextOptionValue === nextkey}
                                        onChange={handleNextChangeOption}
                                        name="NextStepsField"
                                        className={'form-radio form-check-input'}
                                        
                                    />    
                                    <label className='form-check-label' htmlFor={`nextOptions${nextkey}`} >
                                        {nextlabel}
                                    </label><br />
                                    
                                </div>
                            );
                        })}
                        
                        
            </div>
                        
            </div>
        </div>
        </div>
        {NextOptionValue === 'Associate' &&  (  
            <div className="question2" dangerouslySetInnerHTML={{ __html: data.associate_degree || '' }} />         
        )}
        {NextOptionValue === 'UGRAD' &&  (  
            <div className="question3" dangerouslySetInnerHTML={{ __html: data.bachelors_degree || '' }} />         
        )}
        {NextOptionValue === 'GRAD' &&  (  
            <div className="question3" dangerouslySetInnerHTML={{ __html: data.advanced_degree || '' }} />         
        )}
        <div className="asuExcellent" dangerouslySetInnerHTML={{ __html: data.asu_is_an_excellent || '' }} />
        
        <div className="ranking" dangerouslySetInnerHTML={{ __html: data.ranking || '' }} />
        {/* Learning radio options */}
        <div className="bg gray-2-bg bg-top  pb-8 pt-10 bg-percent-100 max-size-container center-container">
        <div className="container">
            <div className="row pb-1 pt-1">
            <div className="js-form-item form-item js-form-type-radios form-item-current-learning js-form-item-current-learning form-group">
                <label className="form-label js-form-required form-required">What are you currently doing now?</label>
               {learningOptions.map((option, index) => {
                const key = Object.keys(option)[0]; // Get the key of the object
                const label = option[key]; // Get the label corresponding to the key
                return (
                    <div className="js-form-item form-item js-form-type-radio form-check" key={index}>
                        <input
                            type="radio"
                            id={`learningOption_${key}`}
                            value={key}
                            checked={LearningOptionValue === key}
                            onChange={handleLearningChangeOption}
                            name="CurrentLearningField"
                            className={'form-radio form-check-input'}
                           
                        /> 
                    <label className='form-check-label' htmlFor={`learningOption_${key}`}> 
                        {label}
                    </label>
                   
                    {LearningOptionValue === key && key === 'hs' && (
                    <div className="js-form-item form-item js-form-type-radios form-item-hs-value js-form-item-hs-value form-group" style={{ marginLeft: '20px' }}>
                        <div>What grade are you currently in?</div>
                        {hsOptions.map((hsoption, hsindex) => {
                        const hskey = Object.keys(hsoption)[0]; 
                        const hslabel = hsoption[hskey]; 
                            return (
                                <div className="js-form-item form-item js-form-type-radio form-check" key={hsindex} >
                                <input
                                    type="radio"
                                    id={`hsOptions${hskey}`}
                                    value={hskey}
                                    checked={hsValue === hskey}
                                    onChange={(event) => handleHsChange(event, hsindex)}
                                    name="hsValueField"
                                    className={'form-radio form-check-input' }
                                    ref={(el) => optionRefs.current[hsindex] = el}
                                />    
                                <label className='form-check-label' htmlFor={`hsOptions${hskey}`} >{hslabel}
                                </label> 
                                </div>
                            
                            
                            );
                       
                        })}
                    </div>
                    )}
                </div>
               
                );
             
                })}
                {showPopup && (
                    <div className="popup" style={popupStyle}>
                            <div className="hsPopup">
                            <div className="popup-content">
                                
                                {HsSelectedOption === 'High school senior' && LearningOptionValue !== "Transfer" && (
                                    <div className="gradePopupDiv">
                                        <span>This is the time to finish your college applications, apply for financial aid and maintain your grades.</span>
                                    </div>
                                 )}
                                 {HsSelectedOption === 'High school junior' && LearningOptionValue !== "Transfer" && (
                                    <div className="gradePopupDiv">
                                        <span>This is the time to start researching and visiting colleges, and take the ACT or SAT.</span>
                                    </div>
                                 )}
                                 {HsSelectedOption === 'High school sophomore' && LearningOptionValue !== "Transfer" && (
                                    <div className="gradePopupDiv">
                                        <span>This is the time to start preparing for the ACT or SAT, and become involved in extracurriculars or volunteer opportunities.</span>
                                    </div>
                                 )}
                                 {HsSelectedOption === 'High school freshman' && LearningOptionValue !== "Transfer" && (
                                    <div className="gradePopupDiv">
                                        <span>This is the time to consider taking AP courses, and begin exploring careers you may be interested in.</span>
                                    </div>
                                 )}
                                 {LearningOptionValue === "Transfer" && (
                                    
                                    <div className="gradePopupDiv">
                                        <span>ASU has pathway programs to easily know how your credits will transfer.</span>
                                    </div>
                                 )}
                            </div>  
                            <div className='popupButton'><button onClick={closePopup}><i className="fa fa-times-circle"></i></button></div>
                            </div>
                        </div>
                    )} 
        
                    {linvalid && (
                        <div className="invalid-feedback">Please select a high school option.</div>
                    )}
      
            </div>  
            </div>   
        </div> {/* end of learning div */}
        
        {/* Military question */}
        <div className="container militaryOptions">
        <div className="row">
            <div className="js-form-item form-item js-form-type-radios form-item-military-value js-form-item-know-value form-group" ><label className="form-label js-form-required form-required">Are you or a family member affiliated with the military?</label>

                {militaryOptions.map((miloption, index) => {
                        //console.log(miloption);
                        //const milkey = Object.keys(miloption.value)[0]; 
                        const millabel = miloption.label; 
                        const milkey = miloption.value; 
                        return (
                                <div className="js-form-item form-item js-form-type-radio form-check" key={index}>
                                    <input 
                                        type="radio"
                                        id={`militaryOptions${milkey}`}
                                        value={milkey}
                                        checked={MilitaryOptionValue === milkey}
                                        onChange={onMilitaryChangeOption}
                                        name="MilitaryField"
                                        className={'form-radio form-check-input'}
                                    />    
                                    <label className='form-check-label' htmlFor={`militaryOptions${milkey}`} >
                                        {millabel}
                                    </label><br />
                                    
                                </div>
                            );
                        })}
                      
                </div>
                        
            </div>
            </div> {/* End of military field */}
            </div>
            {MilitaryOptionValue === "Veteran" && (  
                <div className="questionMili" dangerouslySetInnerHTML={{ __html: data.military_affiliate || '' }} />         
             )}

            {/* excellent teaching */}
            <div className="questionexcellent" dangerouslySetInnerHTML={{ __html: data.excellent_teaching || '' }} />

            {/* student ratio */}
            <div className="questionRatio" dangerouslySetInnerHTML={{ __html: data.student_faculty_ratio || '' }} />

            {/* what do you wnat to study */}
            <div className="bg gray-2-bg bg-top  pb-8 pt-10 bg-percent-100 max-size-container center-container"> 
                <div className="container custom-container">
                    <div className="row">
                        <div className="questionRatio" dangerouslySetInnerHTML={{ __html: data.what_do_you_want_to_study || '' }} />
                    </div>

                    <div className="row">
                    <div className="js-form-item form-item js-form-type-select form-item-interest js-form-item-interest form-group col-sm-4">
                        <label htmlFor="interestOptionsField">Check them out<span className='error'>*</span></label>
                        <Select
                            name="interestOptionsField"
                            id="interestOptionsField"
                            value={valueInterest}
                            onChange={onChangeInterestOption}
                            placeholder="Select..."
                            options={interetsOptions}
                            className={invalidinterestOptions ? 'error-field' : ''}
                            
                        />
                        {errors.interestOptionsField && <span className="error">{errors.interestOptionsField.message}</span>} 
                    </div>
                    
                    </div>
                    {valueInterest && (
                    <>
                     { valueInterest.value === "Architecture and Construction" && (
                        <div className='intDesc col-sm-4'>Beautiful buildings are art! ASU can train you to design or build them. <a href="https://degrees.apps.asu.edu/bachelors/major-list/interest-area/01" className="degreeLink">See degrees</a>
                        </div>
                     )}
                     { valueInterest.value === "Arts" && (
                        <div className='intDesc col-sm-4'>ASU has a robust arts program offering more than 60 degree to choose from. <a href="https://degrees.apps.asu.edu/bachelors/major-list/interest-area/02" className="degreeLink">See degrees</a>
                        </div>
                     )}
                     { valueInterest.value === "Business" && (
                        <div className='intDesc col-sm-4'>ASU's business school, W. P. Carey, is one of the top business schools in the U.S. <a href="https://degrees.apps.asu.edu/bachelors/major-list/interest-area/04" className="degreeLink">See degrees</a>
                        </div>
                     )}
                     { valueInterest.value === "Communication and Media" && (
                        <div className='intDesc col-sm-4'>Communication, media and journalism are what brings the world closer and helps us to better understand each other. ASU offers more than 40 programs. <a href="https://degrees.apps.asu.edu/bachelors/major-list/interest-area/05" className="degreeLink">See degrees</a>
                        </div>
                     )}
                     { valueInterest.value === "Computing and Mathematics" && (
                        <div className='intDesc col-sm-4'>If you're a numbers person, we have some programs for you. More than 30 of them, in fact. <a href="https://degrees.apps.asu.edu/bachelors/major-list/interest-area/06" className="degreeLink">See degrees</a>
                        </div>
                     )}
                     { valueInterest.value === "Education and Teaching" && (
                        <div className='intDesc col-sm-4'>Teaching is one of the noblest of the professions. ASU can train you to open whole new worlds to your students, and make a difference in their lives. <a href="https://degrees.apps.asu.edu/bachelors/major-list/interest-area/07" className="degreeLink">See degrees</a>
                        </div>
                     )}
                      
                    { valueInterest.value === "Engineering and Technology" && (
                        <div className='intDesc col-sm-4'>The high-tech world keeps getting techier. ASU can train you to keep up with and lead the next advancements in technology. <a href="https://degrees.apps.asu.edu/bachelors/major-list/interest-area/08" className="degreeLink">See degrees</a>
                        </div>
                     )}
                    { valueInterest.value === "Entrepreneurship" && (
                        <div className='intDesc col-sm-4'>As more and more people opt to start their own business and be their own boss, ASU can prepare you to also become an entrepreneur. <a href="https://degrees.apps.asu.edu/bachelors/major-list/interest-area/21" className="degreeLink">See degrees</a>
                        </div>
                     )}
                      { valueInterest.value === "Exploratory / Undecided" && (
                        <div className='intDesc col-sm-4'>It’s OK if you don't know what to study. You're not alone. ASU offers exploratory programs to help you figure it all out. <a href="https://degrees.apps.asu.edu/bachelors/major-list/interest-area/14" className="degreeLink">See degrees</a>
                        </div>
                     )}
                     { valueInterest.value === "Health and Wellness" && (
                        <div className='intDesc col-sm-4'>Healthier people means a healthier world. ASU can train you to support peoples' health and wellness through nearly 50 health-related degrees. <a href="https://degrees.apps.asu.edu/bachelors/major-list/interest-area/03" className="degreeLink">See degrees</a>
                        </div>
                     )}
                     { valueInterest.value === "Humanities" && (
                        <div className='intDesc col-sm-4'>Learn to think critically and explore the human experience throughout time and location. ASU offers 65 degrees in the humanities. <a href="https://degrees.apps.asu.edu/bachelors/major-list/interest-area/11" className="degreeLink">See degrees</a>
                        </div>
                     )}
                     { valueInterest.value === "Interdisciplinary Studies" && (
                        <div className='intDesc col-sm-4'>Coming at challenges with a range of perspectives means more and better solutions. ASU offers 50 degree programs in interdisciplinary studies. <a href="https://degrees.apps.asu.edu/bachelors/major-list/interest-area/10" className="degreeLink">See degrees</a>
                        </div>
                     )}
                     { valueInterest.value === "Law, Justice and Public Service" && (
                        <div className='intDesc col-sm-4'>Become prepared to enter the fields of politics, criminal justice, public service and more, and help to make it a better world. <a href="https://degrees.apps.asu.edu/bachelors/major-list/interest-area/12" className="degreeLink">See degrees</a>
                        </div>
                     )}
                     { valueInterest.value === "Science" && (
                        <div className='intDesc col-sm-4'>Put on your lab coat! Chemistry, biology, earth and space, forensics, physics. These are just a few of the science areas at ASU. <a href="https://degrees.apps.asu.edu/bachelors/major-list/interest-area/18" className="degreeLink">See degrees</a>
                        </div>
                     )}
                     { valueInterest.value === "Social and Behavioral Sciences" && (
                        <div className='intDesc col-sm-4'>Better understand humans and how they relate through all stages of their lives with one of ASU's many social and behavioral science degrees. <a href="https://degrees.apps.asu.edu/bachelors/major-list/interest-area/13" className="degreeLink">See degrees</a>
                        </div>
                     )}
                     { valueInterest.value === "STEM" && (
                        <div className='intDesc col-sm-4'>ASU has many degree options  related to science, technology, engineering and mathematics. <a href="https://degrees.apps.asu.edu/bachelors/major-list/interest-area/20" className="degreeLink">See degrees</a>
                        </div>
                     )}
                     { valueInterest.value === "Sustainability" && (
                        <div className='intDesc col-sm-4'>Our planet needs assistance, and an ASU degree in the area of sustainability can help you help it. <a href="https://degrees.apps.asu.edu/bachelors/major-list/interest-area/15" className="degreeLink">See degrees</a>
                        </div>
                     )}

                     </>
                    )}
                </div>

            </div>
            
            <div className="noMatter bg gray-1-bg bg-top bg-percent-100 max-size-container center-container">
                {isOpen ? (
                    <div className='container'><div className='row'><div className="questionNoMatter" dangerouslySetInnerHTML={{ __html: data.no_matter_text_content || '' }} /></div></div>
                ) : (
                <div className='container'><div className='row'>
                <div className="questionNoMatter" dangerouslySetInnerHTML={{ __html: data.no_matter_what_you_choose || '' }} />
                </div></div>
               )} 
                   
            </div>
            <div className='container'><div className='row'>
            <div className="TextMode">
               
                <div className="TextContentLink" onClick={toggleTextContent}>
                    {isOpen ? (
                    <div dangerouslySetInnerHTML={{ __html: '<button class="btn btn-gold back-to-module-btn">Back to module</div>' || '' }} /> 
                      )  : (
                    <div dangerouslySetInnerHTML={{ __html: '<span class="noMattertextLink">View text version</span>' || '' }} />
                     )}
                </div>
            </div>
            </div></div>
    </div>
     
    );
  };

export default LearningData;
