<?php
	
	/**
	 *@file
	 *contains \Drupal\asu_quiz\Form\PersonaQuizSettingsForm
	 **/
	
	namespace Drupal\asu_opt_calculator\Form;
	
	use Drupal\Core\Form\FormStateInterface;
	use Drupal\Core\Form\FormBase;
	use Drupal\Core\Ajax\AjaxResponse;
	use Drupal\Core\Ajax\ReplaceCommand;
	use Drupal\Core\Datetime\DrupalDateTime;
	
	/**
	 *Defines a form to configure Persoan Quiz confirmation page content settings
	 */
	
	class OptForm extends FormBase
	{
		/**
		 *{ @inheritdoc}
		 */
		public function getFormID()
		{
			return 'asu_opt_calculator_opt_form';
		}
		
		public function buildForm(array $form, FormStateInterface $form_state)
		{
			$config = \Drupal::config('asu_opt_calculator.admin_settings');
			$year = date("Y");
			$dates_variable = array();
			$year_values = array();
			$last_year = $year + 4;
			$month = date('m');
			if ($month < 2) {
				$year = $year - 1;
			} else {
				$year = $year;
			}
			
			for ($i = date("Y"); $i <= $last_year; $i++) {
				$j = intval($i);
				$year_values[$j] = $j;
				
				$dates_variable["springdate$j"] = date('Y-m-d', strtotime($config->get("springdate$j")));
				$dates_variable["falldate$j"] = date('Y-m-d', strtotime($config->get("falldate$j")));
				$dates_variable["summerdate$j"] = date('Y-m-d', strtotime($config->get("summerdate$j")));
				$dates_variable["springsessiondate$j"] = date('Y-m-d', strtotime($config->get("springsessiondate$j")));
				$dates_variable["fallsessiondate$j"] = date('Y-m-d', strtotime($config->get("fallsessiondate$j")));
				$dates_variable["summersessiondate$j"] = date('Y-m-d', strtotime($config->get("summersessiondate$j")));
			}
			
			//$search = "12/31/1969";
			$search = date('m/d/Y');
			$replace = '';
			array_walk($dates_variable,
				function (&$v) use ($search, $replace) {
					$v = str_replace($search, $replace, $v);
				}
			);
			
			
			
			$form['#attached']['drupalSettings']['optModule'] = $dates_variable;
			
			$terms_array = array("Spring" => "Spring", "Summer" => "Summer", "Fall" => "Fall");
			
			$form['radio_markup'] = array(
				'#type' => 'item',
				'#markup' => '<div id="opt_calculator_page style="text-align:center;"><h1>F-1 Student OPT Timeline Calculator</h1><p>&nbsp;</p><p><strong>Have you participated in Pre-OPT previously?</strong></p>
<p><span id="pre_opt_yes"  class="opt_button">Yes</span> &nbsp;&nbsp;<span  id="pre_opt_no" class="opt_button">No</span> </p>
</div>',
				'#prefix' => '<br /><div class="container">',
			);
			
			$form['opt_type'] = array(
				'#type' => 'radios',
				'#title' => t('<strong>Have you participated in Pre-OPT previously?</strong><br />'),
				'#options' => array("yes"=>"Yes", "no"=>"No"),
				'#required' => TRUE,
				'#suffix' => '</div>'
			);
			
			
			$form['opt_id_markup'] = array(
				'#type' => 'markup',
				'#markup' => '',
				'#prefix' => '<div id="complete_opt_form" class="container">',
			);
			
			$form['opt_month'] = array(
				'#type' => 'hidden',
				'#default_value' => $month,
			
			);
			
			$form['opt_degree_markup'] = array(
				'#type' => 'markup',
				'#markup' => '<h3>Degree</h3>',
			
			);
			
			$form['opt_degree_type'] = array(
				'#type' => 'select',
				'#title' => t('<strong>Which type of degree will you earn?</strong>'),
				'#options' => array("Bachelor's" => "Bachelor's", "Master's" => "Master's", "Doctoral" => "PHD"),
				'#required' => TRUE,
				'#attributes' => array('class' => array('opt_degree_type questions')),
			);
			$form['opt_degree_question'] = array(
				'#type' => 'item',
				'#markup' => t('<span class="calculator_markup">When will you finish your degree?</span>'),
				'#attributes' => array(
					'class' => array(
						'opt_degree_question questions'
					),
				),
				'#prefix' => '<div class="terms">'
			);
			
			
			$form['opt_year'] = array(
				'#type' => 'select',
				'#title' => t('<strong>Year</strong>'),
				'#options' => $year_values,
				'#required' => TRUE,
				'#attributes' => array('class' => array('questions')),
			
			
			);
			
			$form['opt_term'] = array(
				'#type' => 'select',
				'#title' => t('<strong>Term</strong>'),
				'#options' => array("Spring" => "Spring", "Summer" => "Summer", "Fall" => "Fall"),
				//'#options' => asort($terms_array),
				'#required' => TRUE,
				'#attributes' => array('class' => array('questions')),
				'#suffix' => '</div>',
			);
			
			
			$form['opt_info_text'] = array(
				'#type' => 'item',
				'#markup' => '<p>To determine your completion date, please choose from the circumstances that most closely fit
	    your final academic requirement and then enter your end date below. <a href="https://issc.asu.edu/graduate-completion-dates" target="_blank">https://issc.asu.edu/graduate-completion-dates</a></p>
	    <p>If your completion date is after the graduate college graduation deadline, please do not use this form and contact <a href="mailto:ISSC@asu.edu">ISSC@asu.edu</a></p>',
				'#states' => array(
					'visible' => array(
						':input[name="opt_degree_type"]' => array(
							array('value' => "Master's"),
							array('value' => "Doctoral")
						),
					),
				
				),
			);
			
			
			$form['opt_session_question'] = array(
				'#type' => 'select',
				'#title' => t('<strong>In your final semester, are you enrolled in session A only?</strong>'),
				'#options' => array("Yes" => "Yes", "No" => "No"),
				
				'#states' => array(
					'visible' => array(
						':input[name="opt_degree_type"]' => array('value' => "Bachelor's"),
					),
					//'#required' => TRUE,
					'required' => array(
						':input[name="opt_degree_type"]' => array('value' => "Bachelor's"),
					),
				),
			);
			
			$form['opt_program_markup'] = array(
				'#type' => 'item',
				'#markup' => '<h3>Program End Date</h3>',
			
			);
			
			$form['opt_doctoral_degree_text'] = array(
				'#type' => 'item',
				'#markup' => '<span>Any student employment, including assistantships (TA/RA), ends upon completion of a student\'s degree program.<br />The program completion date for most students is ASU\'s Degree Conferral date. Please review the <a href="https://students.asu.edu/academic-calendar">ASU Academic Calendar</a>.</span>',
				
				'#states' => array(
					'visible' => array(
						':input[name="opt_degree_type"]' => array('value' => "Doctoral"),
					),
				),
			);
			
			$form['opt_bach_degree_text'] = array(
				'#type' => 'item',
				'#markup' => '<span class="calculator_markup">Your completion date is the commencement date of the final semester or last date of session A (if only enrolled in session A).</span>',
				
				'#states' => array(
					'visible' => array(
						':input[name="opt_degree_type"]' => array('value' => "Bachelor's"),
					),
				),
			);
			
			$form['datetime'] = array(
				'#title' => t('<strong>Your program end date is (mm/dd/yy):</strong>'),
				'#type' => 'date',
				'#date_date_format' => 'm/d/Y',
				'#datepicker_options' => array(
					'minDate' => 0,
				),
				'#date_year_range' => '0:+7',
				'#date_label_position' => 'within',
				'#attributes' => array('readonly' => 'readonly'),
				'#prefix' => '<div class="terms_container" id="end_period">',
			
			);
			
			$form['submit'] = array(
				'#type' => 'submit',
				'#attributes' => array('class' => array('rfi_button')),
				//'#submit' => array('asu_preopt_calculator_period_submit'),
				'#submit' => [[$this, 'asu_preopt_calculator_period_submit']],
				'#value' => t('Calculate'),
				'#suffix' => '</div>',
				'#ajax' => [
					'callback' => '::ajax_calculate_callback',
					'wrapper' => 'grace_period',
					'event' => 'click',
				],
				'#id' => 'myuniqueid',
			);
			
			$form['opt_grace_markup'] = array(
				'#type' => 'markup',
				'#markup' => '<h3>Grace Period</h3>',
				'#prefix' => '<div id="grace_period">',
				'#suffix' => '</div>'
			);
			
			$form['opt_other_grace_markup'] = array(
				'#type' => 'item',
				'#markup' => '',
				'#prefix' => '<div id="grace_other_period">',
				'#suffix' => '</div>'
			);
			
			$form['opt_fullpart_markup'] = array(
				'#type' => 'item',
				'#markup' => '<h3>OPT Dates</h3>',
			
			);
			
			
			//Full time opt fields
			
			$form['opt_fieldset'] = array(
				'#type' => 'fieldset',
				'#title' => t('For full time Pre-OPT: enter the dates that appear on your EAD card'),
				'#collapsible' => TRUE,
				'#collapsed' => FALSE,
				'#prefix' => '<div class ="pre_dates_div">',
				'#states' => array(
					'visible' => array(
						':input[name="opt_type"]' => array('value' => "yes"),
						':input[value=yes]' => array('checked' => TRUE),
					),
				),
			);
			
			$form['opt_fieldset']['full_start_d1'] = array(
				'#type' => 'date',
				'#title' => t('<strong>Start Date:</strong>'),
				'#date_date_format' => 'm/d/Y',
				'#datepicker_options' => array(
					'maxDate' => 150,
				),
				'#date_year_range' => '-5:+1',
				'#date_label_position' => 'within',
				'#prefix' => '<div id ="full_first_div"><div class="field_odd date_divs">',
				'#suffix' => '</div>',
				'#attributes' => array(
					'data' => array('full'),
					'class' => array('1'),
				),
			
			);
			
			$form['opt_fieldset']['full_end_d1'] = array(
				'#type' => 'date',
				'#title' => t('<strong>End Date:</strong>'),
				'#date_date_format' => 'm/d/Y',
				'#datepicker_options' => array(
					'maxDate' => 150,
				),
				'#date_year_range' => '-5:+1',
				'#date_label_position' => 'within',
				'#prefix' => '<div class="field_odd date_divs">',
				'#suffix' => '</div>',
				
				'#attributes' => array(
					'data' => array('full'),
					'class' => array('1'),
				),
			
			);
			
			$form['opt_fieldset']['full_text_d1'] = array(
				'#type' => 'textfield',
				'#title' => t('<strong>Number of days used for OPT period</strong>'),
				'#size' => 30,
				'#maxlength' => 15,
				'#default_value' => 0,
				'#disabled' => true,
				'#prefix' => '<div id="textd1value" class="field_odd date_divs nofdays"><div class="container-inline-date container-text-date field_one">',
				'#suffix' => '</div></div></div>',
			
			);
			
			
			//second
			
			$form['opt_fieldset']['full_start_d2'] = array(
				'#type' => 'date',
				'#title' => t('<strong>Start Date:</strong>'),
				'#date_date_format' => 'm/d/Y',
				'#datepicker_options' => array(
					'maxDate' => 150,
				),
				'#date_year_range' => '-5:+1',
				'#date_label_position' => 'within',
				'#prefix' => '<div id ="full_second_div"><div class="field_even  second_date_divs">',
				'#suffix' => '</div>',
				
				'#attributes' => array(
					'data' => array('full'),
					'class' => array('2'),
				),
			
			);
			
			$form['opt_fieldset']['full_end_d2'] = array(
				'#type' => 'date',
				'#title' => t('<strong>End Date:</strong>'),
				'#datedate__format' => 'm/d/Y',
				'#datepicker_options' => array(
					'maxDate' => 150,
				),
				'#date_year_range' => '-5:+1',
				'#date_label_position' => 'within',
				'#prefix' => '<div class="field_even second_date_divs">',
				
				'#suffix' => '</div>',
				'#attributes' => array(
					'data' => array('full'),
					'class' => array('2'),
				),
			
			);
			
			
			$form['opt_fieldset']['full_text_d2'] = array(
				'#type' => 'textfield',
				'#title' => t('<strong>Number of days used for OPT period</strong>'),
				'#size' => 30,
				'#maxlength' => 15,
				'#disabled' => true,
				'#prefix' => '<div id="textd2value" class="field_even  second_date_divs nofdays"><div class="container-inline-date date-padding container-text-date field_two">',
				'#default_value' => 0,
				'#suffix' => '</div></div></div>',
			
			);
			
			//third
			$form['opt_fieldset']['full_start_d3'] = array(
				'#type' => 'date',
				'#title' => t('<strong>Start Date:</strong>'),
				'#date_date_format' => 'm/d/Y',
				'#datepicker_options' => array(
					'maxDate' => 150,
				),
				'#date_year_range' => '-5:+1',
				'#date_label_position' => 'within',
				'#prefix' => '<div id ="full_third_div"><div class="field_odd  third_date_divs">',
				
				'#suffix' => '</div>',
				'#attributes' => array(
					'data' => array('full'),
					'class' => array('3'),
				),
				'#attributes' => ['type' => 'date'],
			);
			
			
			$form['opt_fieldset']['full_end_d3'] = array(
				'#type' => 'date',
				'#title' => t('<strong>End Date:</strong>'),
				'#date_date_format' => 'm/d/Y',
				'#datepicker_options' => array(
					'maxDate' => 150,
				),
				'#date_year_range' => '-5:+1',
				'#date_label_position' => 'within',
				'#prefix' => '<div class="field_odd third_date_divs">',
				'#suffix' => '</div>',
				'#attributes' => array(
					'data' => array('full'),
					'class' => array('3'),
				),
				'#attributes' => ['type' => 'date'],
			);
			
			
			$form['opt_fieldset']['full_text_d3'] = array(
				'#type' => 'textfield',
				'#title' => t('<strong>Number of days used for OPT period</strong>'),
				'#size' => 30,
				'#maxlength' => 15,
				'#disabled' => true,
				'#prefix' => '<div id="textd3value" class="field_odd third_date_divs nofdays"><div class="container-inline-date date-padding container-text-date field_three">',
				'#suffix' => '</div></div></div></div>',
				'#default_value' => 0,
			
			);
			
			
			//Full time opt fields
			
			$form['opt_part_fieldset1'] = array(
				'#type' => 'fieldset',
				'#title' => t('For part time Pre-OPT: enter the dates that appear on your EAD card'),
				'#collapsible' => TRUE,
				'#collapsed' => FALSE,
				'#prefix' => '<div class ="pre_dates_div">',
				'#states' => array(
					'visible' => array(
						':input[name="opt_type"]' => array('value' => "yes"),
						':input[value=yes]' => array('checked' => TRUE),
					),
				),
			);
			
			$form['opt_part_fieldset1']['part_start_d1'] = array(
				'#type' => 'date',
				'#title' => t('<strong>Start Date:</strong>'),
				'#date_date_format' => 'm/d/Y',
				'#datepicker_options' => array(
					'maxDate' => 150,
				),
				'#date_year_range' => '-5:+1',
				'#date_label_position' => 'within',
				'#prefix' => '<div id ="part_first_div"><div class="field_odd date_divs">',
				'#suffix' => '</div>',
				'#attributes' => ['type' => 'date'],
			);
			
			$form['opt_part_fieldset1']['part_end_d1'] = array(
				'#type' => 'date',
				'#title' => t('<strong>End Date:</strong>'),
				'#date_date_format' => 'm/d/Y',
				'#datepicker_options' => array(
					'maxDate' => 150,
				),
				'#date_year_range' => '-5:+1',
				'#date_label_position' => 'within',
				'#prefix' => '<div class="field_odd date_divs">',
				'#suffix' => '</div>',
				'#attributes' => ['type' => 'date'],
			);
			
			$form['opt_part_fieldset1']['part_text_d1'] = array(
				'#type' => 'textfield',
				'#title' => t('<strong>Number of days used for OPT period</strong>'),
				'#size' => 30,
				'#maxlength' => 15,
				'#disabled' => true,
				'#prefix' => '<div class="field_odd date_divs"><div class="container-inline-date date-padding container-text-date field_one">',
				'#suffix' => '</div></div></div>',
				'#default_value' => 0,
			
			);
			
			//second
			
			$form['opt_part_fieldset1']['part_start_d2'] = array(
				'#type' => 'date',
				'#title' => t('<strong>Start Date:</strong>'),
				'#date_date_format' => 'm/d/Y',
				'#datepicker_options' => array(
					'maxDate' => 150,
				),
				'#date_year_range' => '-5:+1',
				'#date_label_position' => 'within',
				'#prefix' => '<div id ="part_second_div"><div class="field_even  second_date_divs">',
				'#suffix' => '</div>',
				'#attributes' => ['type' => 'date'],
			);
			
			$form['opt_part_fieldset1']['part_end_d2'] = array(
				'#type' => 'date',
				'#title' => t('<strong>End Date:</strong>'),
				'#date_date_format' => 'm/d/Y',
				'#datepicker_options' => array(
					'maxDate' => 150,
				),
				'#date_year_range' => '-5:+1',
				'#date_label_position' => 'within',
				'#prefix' => '<div class="field_even second_date_divs" >',
				'#suffix' => '</div>',
				'#attributes' => ['type' => 'date'],
			);
			
			
			$form['opt_part_fieldset1']['part_text_d2'] = array(
				'#type' => 'textfield',
				'#title' => t('<strong>Number of days used for OPT period</strong>'),
				'#size' => 30,
				'#maxlength' => 15,
				'#disabled' => true,
				'#prefix' => '<div class="field_even  second_date_divs"><div class="container-inline-date container-text-date field_two">',
				'#suffix' => '</div></div></div>',
				'#default_value' => 0,
			
			);
			
			//third
			$form['opt_part_fieldset1']['part_start_d3'] = array(
				'#type' => 'date',
				'#title' => t('<strong>Start Date:</strong>'),
				'#date_date_format' => 'm/d/Y',
				'#datepicker_options' => array(
					'maxDate' => 150,
				),
				'#date_year_range' => '-5:+1',
				'#date_label_position' => 'within',
				'#prefix' => '<div id ="part_third_div"><div class="field_odd  third_date_divs">',
				'#suffix' => '</div>',
				'#attributes' => ['type' => 'date'],
			);
			
			$form['opt_part_fieldset1']['part_end_d3'] = array(
				'#type' => 'date',
				'#title' => t('<strong>End Date:</strong>'),
				'#date_date_format' => 'm/d/Y',
				'#datepicker_options' => array(
					'maxDate' => 150,
				),
				'#date_year_range' => '-5:+1',
				'#date_label_position' => 'within',
				'#prefix' => '<div class="field_odd third_date_divs">',
				'#suffix' => '</div>',
				'#attributes' => ['type' => 'date'],
			);
			
			
			$form['opt_part_fieldset1']['part_text_d3'] = array(
				'#type' => 'textfield',
				'#title' => t('<strong>Number of days used for OPT period</strong>'),
				'#size' => 30,
				'#maxlength' => 15,
				'#disabled' => true,
				'#prefix' => '<div class="field_odd third_date_divs"><div class="container-inline-date container-text-date field_three">',
				'#suffix' => '</div></div></div></div>',
				'#default_value' => 0,
			
			);
			
			$form['opt_graceperiod_auth_markup'] = array(
				'#type' => 'markup',
				'#markup' => '<h3>Employment Authorization</h3>',
			
			);
			
			
			$form['opt_degree_text1'] = array(
				'#type' => 'markup',
				'#markup' => 'You must select a start date within your 60 day grace period. This will be the recommended start date on your EAD card.',
				'#attributes' => array('class' => array('opt_degree_question questions'))
			
			);
			
			$form['opt_remaining_markup'] = array(
				'#type' => 'markup',
				'#markup' => "",
				'#prefix' => '<div id="remaining_days">',
				'#suffix' => '</div>',
			
			);
			
			
			$form['begin_full_employment'] = array(
				'#title' => t('<strong>When would you like your employment authorization to begin?<br />This will be the recommended start date on your EAD card.</strong>'),
				'#type' => 'date',
				'#date_date_format' => 'm/d/Y',
				'#datepicker_options' => array(
					'minDate' => '-12M',
				),
				'#date_year_range' => '0:+7',
				'#date_label_position' => 'within',
				'#prefix' => '<div class="terms_container bottom_terms_container">',
				'#attributes' => ['type' => 'date'],
			);
			
			
			$form['opt_total'] = array(
				'#type' => 'hidden',
				'#size' => 30,
				'#maxlength' => 15,
			
			);
			
			$form['total_days_submit'] = array(
				'#type' => 'submit',
				'#attributes' => array('class' => array('rfi_button')),
				'#value' => t('Validate'),
				'#suffix' => '</div>',
				'#submit' => [[$this, 'asu_preopt_calculator_period_submit']],
				'#ajax' => [
//					'callback' => '::ajax_full_opt_calculate_callback',
					'callback' => [$this, 'ajax_full_opt_calculate_callback'],
					'wrapper' => 'total_period',
					'event' => 'click',
				],
			
			);
			
			$form['opt_total_markup'] = array(
				'#type' => 'markup',
				'#markup' => "",
				'#prefix' => '<div id="total_period">',
				'#suffix' => '</div>',
			
			);
			
			
			$form['opt_disclaimer'] = array(
			    '#type' => 'markup',
				'#markup' => "<p>The dates recommended on this calculator are intended to help determine an OPT start date. However, there are some situations that may not be available on this calculator. If you still have questions regarding your OPT start dates or traveling while on OPT, please go to <a href='https://issc.asu.edu/employment/students-fj/f-1-opt' target='_blank'>ISSC.asu.edu</a> or call the ISSC to make an appointment to see an advisor.<p>",
				'#suffix' => '</div>',
			);
			
			
			$form['#theme'] = \Drupal::service('theme.manager')->getActiveTheme()->getName();
			$form['#attached']['library'] = 'asu_opt_calculator/optCalculatorjs';
			//ksm($form);
			//ksm(\Drupal::service('theme.manager')->getActiveTheme()->getName());
			return $form;
		}
		
		public function submitForm(array &$form, FormStateInterface $form_state)
		{
			parent::submitForm($form, $form_state);
		}
		
		public function ajax_calculate_callback(array &$form, FormStateInterface $form_state)
		{
			//parent::submitForm($form, $form_state);
			$form_state->setRebuild(true);
			$entered_date = $form_state->getValue('datetime');
			
			$begin_date = date('m-d-Y', strtotime('+1 day', strtotime($entered_date)));
			$end_date = date('m-d-Y', strtotime('+60 day', strtotime($entered_date)));
			
			$doc_date = date('m-d-Y', strtotime('-100 day', strtotime($entered_date)));
			$doc_message = "<span>Submit your documents to ISSC by $doc_date</span>";
			if (!empty($entered_date)) {
				$mark_message = "<span class='opt_message'><strong><h3>Grace Period</h3>Your 60 day Grace period starts from: &nbsp;&nbsp;</strong></span><span class='grace-text'> $begin_date</span> <span style='font-size: 18px;'>through</span> <span class='grace-text'>$end_date</span>";
			} else {
				$mark_message = "<span class='alert alert-block alert-error'><strong>Please enter valid date</strong></span>";
			}
			$form['opt_grace_markup'] = array(
				'#type' => 'markup',
				'#markup' => $mark_message,
				'#prefix' => '<div id="grace_period" class="grace_markup">',
				'#suffix' => '</div></div>',
			
			);
			
			
			return $form['opt_grace_markup'];
			
		}
		
		
		function ajax_other_calculate_callback(array &$form, FormStateInterface $form_state)
		{
			//parent::submitForm($form, $form_state);
			$form_state->setRebuild(true);
			$entered_date = $form_state->getValue('otherdatetime');
			
			$begin_date = date('m-d-Y', strtotime('+1 day', strtotime($entered_date)));
			$end_date = date('m-d-Y', strtotime('+61 day', strtotime($entered_date)));
			
			$other_date = $form_state->getValue('otherdatetime');
			
			
			if (!empty($entered_date)) {
				$mark_message = "<span class='opt_message'><strong><h3>Grace Period</h3>Your 60 day Grace period starts from: &nbsp;&nbsp;</strong></span><span class='grace-text'> $begin_date</span> <span style='font-size: 18px;'>through</span> <span class='grace-text'>$end_date</span>";
			} else {
				$mark_message = "<span class='alert alert-block alert-error'><strong>Please enter valid date</strong></span>";
			}
			$form['opt_grace_other_markup'] = array(
				'#type' => 'markup',
				'#markup' => $mark_message,
				'#prefix' => '<div id="grace_other_period" class="grace_markup">',
				'#suffix' => '</div>',
			
			);
			
			
			return $form['opt_grace_other_markup'];
			
		}
		
		function asu_preopt_calculator_period_submit(array &$form, FormStateInterface $form_state)
		{
			//parent::submitForm($form, $form_state);
			//\Drupal::logger('test')->notice(print_r('clicked', TRUE));
			$form_state->setRebuild(true);
			$full_s_d1 = $form_state->getValue('full_start_d1');
			if (isset($full_s_d1)) {
				$s1date = $form_state->getValue('full_start_d1');
				$e1date = $form_state->getValue('full_end_d1');
				$start1_new_date = new DrupalDateTime($form_state->getValue('full_start_d1'));
				$end1_new_date = new DrupalDateTime($form_state->getValue('full_end_d1'));
				//$interval = date_diff($start1_new_date, $end1_new_date);
				$interval = $start1_new_date->diff($end1_new_date);
				$form_state->setValue('full_text_d1', $interval->days);
				$text1 = $interval->days;
			} else {
				$text1 = '';
			}
			$full_s_d2 = $form_state->getValue('full_start_d2');
			if (isset($full_s_d2)) {
				$s2date = $form_state->getValue('full_start_d2');
				$e2date = $form_state->getValue('full_end_d2');
				$start2_new_date = new DrupalDateTime($form_state->getValue('full_start_d2'));
				$end2_new_date = new DrupalDateTime($form_state->getValue('full_end_d2'));
				$interval2 = $start2_new_date->diff($end2_new_date);
				
				$form_state->setValue('full_text_d2', $interval2->days);
				$text2 = $interval2->days;
			} else {
				$text2 = '';
			}
			
			$full_s_d3 = $form_state->getValue('full_start_d3');
			if (isset($full_s_d3)) {
				$s3date = $form_state->getValue('full_start_d3');
				$e3date = $form_state->getValue('full_end_d3');
				$start3_new_date = new DrupalDateTime($form_state->getValue('full_start_d3'));
				$end3_new_date = new DrupalDateTime($form_state->getValue('full_end_d3'));
				$interval3 = $start3_new_date->diff($end3_new_date);
				
				$form_state->setValue('full_text_d3',$interval3->days);
				$text3 = $interval3->days;
			} else {
				$text3 = '';
			}
			
			$part_s_d1 = $form_state->getValue('part_start_d1');
			if (isset($part_s_d1)) {
				$ps1date = $form_state->getValue('part_start_d1');
				$pe1date = $form_state->getValue('part_end_d1');
				$pstart1_new_date = new DrupalDateTime($form_state->getValue('part_start_d1'));
				$pend1_new_date = new DrupalDateTime($form_state->getValue('part_end_d1'));
				
				//$interval4 = $pstart1_new_date->diff($pend1_new_date);
				$interval4 = $pstart1_new_date->diff($pend1_new_date);
				$form_state->setValue('part_text_d1',$interval4->days);
				$text4 = round(($interval4->days) / 2);
			} else {
				$text4 = '';
			}
			
			$part_s_d2 = $form_state->getValue('part_start_d2');
			if (isset($part_s_d2)) {
				$ps2date = $form_state->getValue('part_start_d2');
				$pe2date = $form_state->getValue('part_end_d2');
				$pstart2_new_date = new DrupalDateTime($form_state->getValue('part_start_d2'));
				$pend2_new_date = new DrupalDateTime($form_state->getValue('part_end_d2'));
				//$interval5 = $pstart2_new_date->diff($pend2_new_date);
				$interval5 = $pstart2_new_date->diff($pend2_new_date);
				$form_state->setValue('part_text_d2',$interval5->days);
				$text5 = round(($interval5->days) / 2);
			} else {
				$text5 = '';
			}
			
			$part_s_d3 = $form_state->getValue('part_start_d3');
			if (isset($part_s_d3)) {
				$ps3date = $form_state->getValue('part_start_d3');
				$pe3date = $form_state->getValue('part_end_d3');
				$pstart3_new_date = new DrupalDateTime($form_state->getValue('part_start_d3'));
				$pend3_new_date = new DrupalDateTime($form_state->getValue('part_end_d3'));
				//$interval6 = $pstart3_new_date->diff($pend3_new_date);
				$interval6 = $pstart3_new_date->diff($pend3_new_date);
				$form_state->setValue('part_text_d3', $interval6->days);
				$text6 = round(($interval6->days) / 2);
			} else {
				$text6 = '';
			}
			//ksm($text1, $text2, $text3);
			$total_value = $text1 + $text2 + $text3 + $text4 + $text5 + $text6;
			//\Drupal::logger('total')->notice(print_r($total_value, TRUE));
			$form_state->setValue('opt_total', $total_value);
			
		}
		
		
		function ajax_full_opt_calculate_callback(array &$form, FormStateInterface $form_state)
		{
			//parent::submitForm($form, $form_state);
			$form_state->setRebuild(true);
			$entered_date = $form_state->getValue('datetime');
			$year = date('Y', strtotime($entered_date));
			
			$type = $form_state->getValue('opt_type');
			$entered_date = $form_state->getValue('datetime');
			$authorize_date = $form_state->getValue('begin_full_employment');
			
			$format_entered_date = date('m-d-Y', strtotime($entered_date));
			
			$author_date = date('Y-m-d', strtotime($authorize_date));
			$begin_date = date('Y-m-d', strtotime('+1 day', strtotime($entered_date)));
			$end_date = date('Y-m-d', strtotime('+61 day', strtotime($entered_date)));
			$year_author_date_full = date('m-d-Y', strtotime('+365 day', strtotime($authorize_date)));
			$year_author_date = date('m-d-Y', strtotime('-1 day', strtotime($authorize_date)));
			if ((($year % 4) == 0) && ((($year % 100) != 0) || (($year % 400) == 0))) {
				//$year_author_date_no = date('m-d-Y',strtotime('+366 day',strtotime($authorize_date)));
				$year_author_date_no = date('m-d-Y', strtotime('+364 day', strtotime($authorize_date)));
			} else {
				//$year_author_date_no = date('m-d-Y',strtotime('+365 day',strtotime($authorize_date)));
				$year_author_date_no = date('m-d-Y', strtotime('+364 day', strtotime($authorize_date)));
			}
			if ($type == "yes") {
				if ((strtotime($author_date) >= strtotime($begin_date)) && (strtotime($author_date) <= strtotime($end_date))) {
					$opt_date = $form_state->getValue('opt_total');
					//ksm($opt_date,'opt date');
					if (isset($opt_date)) {
						//dpm('opt_total',$form_state['values']['opt_total']);
						$days = $form_state->getValue('opt_total');
						//ksm($days,'days');
						if ((($year % 4) == 0) && ((($year % 100) != 0) || (($year % 400) == 0))) {
							$year_days = 366;
							//$year_days = 365;
							$lessDay = 2;
							
						} else {
							$year_days = 365;
							$lessDay = 2;
							//$year_days = 364;
						}
						$total_days = $year_days - $days;
						$one_less_day = $total_days - $lessDay;
						//ksm($authorize_date);
						//ksm(strtotime($authorize_date));
						$new_date = date('m-d-Y', strtotime("+$one_less_day day", strtotime($authorize_date)));
						
						$markup = "<h3>EAD Card End Date</h3><p style='color:red;'><strong>The EAD Card end date will be: <span class='valueFromJquery'>" . $new_date . "</span></strong></p>";
					} else {
						$markup = "<p class='alert alert-block alert-error'>Please select start and end dates above</p>";
						
					}
				} else {
					$markup = "<p class='alert alert-block alert-error' style='color:red;'>Error: Please select a date between your 60 day grace period shown above</p>";
				}
				
			}
			
			if ($type == "no") {
				if ((strtotime($author_date) >= strtotime($begin_date)) && (strtotime($author_date) <= strtotime($end_date))) {
					//ksm($year_author_date_no);
					$markup = "<h3>EAD Card End Date</h3><p style='color:red;'><strong>The EAD Card end date will be: " . $year_author_date_no . "</strong></p>";
				} else {
					$markup = "<p class='alert alert-block alert-error'>Please select a date between your 60 day grace period shown above</p>";
				}
			}
			
			
			$form['opt_total_markup'] = array(
				'#type' => 'markup',
				'#markup' => "<strong>$markup</strong>",
				'#prefix' => '<div id="total_period">',
				'#suffix' => '</div>',
			
			);
			
			
			return $form['opt_total_markup'];
			
		}
		
		
		/*function ajax_authorize_calculate_callback(array &$form, FormStateInterface $form_state)
		{
			//parent::submitForm($form, $form_state);
			$entered_date = $form_state->getValue('datetime');
			$format_entered_date = date('m-d-Y', strtotime($entered_date));
			$authorize_date = $form_state->getValue('begin_employment');
			$full_authorize_date = $form_state->getValue('begin_full_employment');
			$author_date = date('Y-m-d', strtotime($authorize_date));
			$begin_date = date('Y-m-d', strtotime('+1 day', strtotime($entered_date)));
			$end_date = date('Y-m-d', strtotime('+60 day', strtotime($entered_date)));
			$year_author_date_full = date('m-d-Y', strtotime('+365 day', strtotime($authorize_date)));
			$year_author_date = date('m-d-Y', strtotime('-1 day', strtotime($authorize_date)));
			$year = date('Y', strtotime($entered_date));
			
			if ((strtotime($author_date) > strtotime($begin_date)) && (strtotime($author_date) < strtotime($end_date))) {
				$markup = "<p style='color:red;'><strong>The EAD Card end date will be 3: " . $year_author_date . "</strong></p><p>&nbsp;</p>";
			} else {
				$markup = "<p class='alert alert-block alert-error' style='color:red;'>Error: Please select a date between your 60 day grace period shown above</p><p>&nbsp;</p>";
			}
			
			
			$form['opt_authorize_markup'] = array(
				'#type' => 'markup',
				'#markup' => "<strong>$markup</strong>",
				'#prefix' => '<div id="authorize_period">',
				'#suffix' => '</div>',
			
			);
			
			
			return $form['opt_authorize_markup'];
			
			
		}*/
	}