<?php

namespace Drupal\asu_quiz\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\Core\StringTranslation\TranslatableMarkup;


/**
 * Provides route responses for the Example module.
 */
class QuizConfirmController extends ControllerBase
{

    /**
     * Returns a simple page.
     *
     * @return array
     *   A simple renderable array.
     */
    public function quiz_confirm_page($sid = null)
    {
		
        //Load webform submissions
        $webform = \Drupal\webform\Entity\Webform::load('quiz');
        if ($webform->hasSubmissions()) {
            $query = \Drupal::entityQuery('webform_submission')
                ->condition('webform_id', 'quiz')
                ->condition('sid', $sid)
                ->accessCheck(FALSE);
            $result = $query->execute();
            $submission_data = [];
            foreach ($result as $item) {
                $submission = \Drupal\webform\Entity\WebformSubmission::load($item);
                $submission_data = $submission->getData();
            }
        }
		
        //get questions submission values
        $quiz_sub_data_values = array($submission_data['when_i_have_free_time_you_can_find_me_'], $submission_data['i_think_my_college_happy_place_will_be_'], $submission_data['my_biggest_college_fear_is_'], $submission_data['which_word_best_describes_you_'], $submission_data['in_high_school_i_'], $submission_data['when_i_think_about_my_future_career_'], $submission_data['how_would_you_best_describe_your_relationship_with_your_friends_'], $submission_data['pick_a_quote_that_speaks_to_you_'], $submission_data['i_m_known_for_'], $submission_data['what_s_your_approach_to_studying_']);
        // \Drupal::logger('submitted_values')->notice(print_r($quiz_sub_data_values, TRUE));
        //set persona description values
        $counts = array_count_values($quiz_sub_data_values);
		
        //  \Drupal::logger('count')->notice(print_r($counts, TRUE));
        //Driven Focused futurist variables
        $driven_value = isset($counts[1]) ? ($counts[1] * 100) / 10 : '0';


        //Life long (deep diver) variables
        $lifelong_learner = isset($counts[2]) ? ($counts[2] * 100) / 10 : '0';


        //self actualizer (trailblazer) variables
        $self_actualizer = isset($counts[3]) ? ($counts[3] * 100) / 10 : '0';

        //socially involved (natural networker) variables
        $socially_involved = isset($counts[4]) ? ($counts[4] * 100) / 10 : '0';

        //athletically involved (superfan) variables
        $athletically_involved = isset($counts[5]) ? ($counts[5] * 100) / 10 : '0';


        $personality_array = array('deep_diver' => $lifelong_learner, 'focused_futurist' => $driven_value, 'trailblazer' => $self_actualizer, 'natural_networker' => $socially_involved, 'superfan' => $athletically_involved);
        $personality_copy_array = array('deep_diver' => $lifelong_learner, 'focused_futurist' => $driven_value, 'trailblazer' => $self_actualizer, 'natural_networker' => $socially_involved, 'superfan' => $athletically_involved);

        arsort($personality_array);
        arsort($personality_copy_array); ///save array into a temp array to save original values in the field 24


        $keys = array_keys($personality_array);
        //if first element and second element hvae same values, then add 1 to first element and subtract 1 from second element so that there is no match. 
        if ($personality_array[$keys[0]] == $personality_array[$keys[1]]) {
            $personality_array[$keys[0]] = $personality_array[$keys[0]] + 1;
            $personality_array[$keys[1]] = $personality_array[$keys[1]] - 1;
        }


        //get fisrt persona key
        reset($personality_array);
        $top_persona_saved = key($personality_array);
		//ksm('top persoan',$top_persona_saved);
        //Get percentage values for SVG diagram
        //driven value (focused futurist)
        $svg_focused_futurist_value = number_format($personality_array['focused_futurist'], 2);
        $svg_focused_futurist_title_value = round($personality_array['focused_futurist']);
        $driven_value_offset = 45;
        $remain_driven_value = 100 - $svg_focused_futurist_value;
        //dpm('fo1',$svg_focused_futurist_value);
        //dpm('f2',$remain_driven_value);

        //deep diver (lifelong learner)
        $svg_deep_diver_value = number_format($personality_array['deep_diver'], 2);
        $svg_deep_diver_title_value = round($personality_array['deep_diver']);
        $rem_lifelong_learner = 100 - $svg_deep_diver_value;
        $lifelong_learner_offset = 100 - ($svg_focused_futurist_value) + 25;
        //dpm('d1',$svg_deep_diver_value);
        //dpm('d2',$lifelong_learner_offset);

        //self acutilser (trailblazer)
        $svg_trailblazer_value = number_format($personality_array['trailblazer'], 2);
        $svg_trailblazer_title_value = round($personality_array['trailblazer']);
        $rem_self_actualizer = 100 - $svg_trailblazer_value;
        $self_actualizer_offset = 100 - ($svg_deep_diver_value + $svg_focused_futurist_value) + 25;
        //dpm('t1',$svg_trailblazer_value);
        //dpm('t2',$self_actualizer_offset);

        //natural networker (socially involved)
        $svg_natural_networker_value = number_format($personality_array['natural_networker'], 2);
        $svg_natural_networker_title_value = round($personality_array['natural_networker']);
        $rem_socially_involved = 100 - $svg_natural_networker_value;
        $socially_involved_offset = 100 - ($svg_deep_diver_value + $svg_focused_futurist_value + $svg_trailblazer_value) + 25;
        //dpm('si1',$svg_natural_networker_value);
        //dpm('si2',$socially_involved_offset);


        //superfan (athletically involved)
        $svg_superfan_value = number_format($personality_array['superfan'], 2);
        $svg_superfan_title_value = round($personality_array['superfan']);
        $rem_athletically_involved = 100 - $svg_superfan_value;
        $athletically_involved_offset = 100 - ($svg_deep_diver_value + $svg_focused_futurist_value + $svg_trailblazer_value + $svg_natural_networker_value) + 25;
        //dpm('sf1',$svg_superfan_value);
        //dpm('sf2',$athletically_involved_offset);


        /* right side divs explaining persona content */
        //driven data
        //$driven_data_content = "<p>Focused, goal-oriented, hard-working, practical, independent, and have a clear separation between school and life.</p>";
        $driven_data_content = "<p>My college experience is enhanced by achieving my goals.</p>";
       /* $driven_focused_content = "<div class='persona-parent'><div id='focused_futurist' class='personality' data-toggle='popover' title='$svg_focused_futurist_title_value/%' data-placement='bottom' data-html='true'><span class='percent_value' data-content='$driven_data_content'><strong>$svg_focused_futurist_title_value% &nbsp;&nbsp; <i class='fa fa-info-circle' aria-hidden='true'></i></strong></span><br /><span class='persona_text'>Focused futurist</span></div><div class='child2 child_hide'>$driven_data_content</div></div>";*/

        $driven_focused_content = "<div class='persona-parent uds-tooltip-bg-black'><div id='focused_futurist' class='personality uds-tooltip-container'><button tabindex='0' class='uds-tooltip uds-tooltip-dark' aria-describedby='tooltip-desc-futrist'><span class='fa-stack'><span class='persona-percent'>$svg_focused_futurist_title_value%</span><svg class='svg-inline--fa fa-circle fa-w-16 fa-stack-2x' aria-hidden='true' focusable='false' data-prefix='fas' data-icon='circle' role='img' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512' data-fa-i2svg=''><path fill='currentColor' d='M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z'></path></svg><svg style='width: 0.75rem;' class='svg-inline--fa fa-info fa-w-6 fa-stack-1x persona-svg' aria-hidden='true' focusable='false' data-prefix='fas' data-icon='info' role='img' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 192 512' data-fa-i2svg=''><path fill='currentColor' d='M20 424.229h20V279.771H20c-11.046 0-20-8.954-20-20V212c0-11.046 8.954-20 20-20h112c11.046 0 20 8.954 20 20v212.229h20c11.046 0 20 8.954 20 20V492c0 11.046-8.954 20-20 20H20c-11.046 0-20-8.954-20-20v-47.771c0-11.046 8.954-20 20-20zM96 0C56.235 0 24 32.235 24 72s32.235 72 72 72 72-32.235 72-72S135.764 0 96 0z'></path></svg></span><span class='uds-tooltip-visually-hidden'>Notifications</span><br /><span class='persona_text'>Focused futurist</span></button><div role='tooltip' class='uds-tooltip-description persona-tip-description' id='tooltip-desc-futurist'><span class='uds-tooltip-heading persona-tooltip-heading'>  $svg_focused_futurist_title_value%</span><div class='formatted-text persona-formatted-text'>$driven_data_content</div></div></div></div>";


        //life learner data
        $learner_data_content = "<p>My college experience is enhanced by the knowledge I gain.</p>";
       /* $learner_diver_content = "<div class='persona-parent'><div id='deep_diver' class='personality'  title='$svg_deep_diver_title_value%' data-toggle='popover' data-placement='bottom' data-html='true' data-content='$learner_data_content'  ><span class='percent_value'><strong>$svg_deep_diver_title_value% &nbsp;&nbsp; <i class='fa fa-info-circle' aria-hidden='true'></i></strong></span><br />Deep diver</div><div class='child2 child_hide'>$learner_data_content</div></div>";*/

        $learner_diver_content = "<div class='persona-parent uds-tooltip-bg-black'><div id='deep_diver' class='personality uds-tooltip-container'><button tabindex='0' class='uds-tooltip uds-tooltip-dark' aria-describedby='tooltip-desc-diver'><span class='fa-stack'><span class='persona-percent'>$svg_deep_diver_title_value% </span><svg class='svg-inline--fa fa-circle fa-w-16 fa-stack-2x' aria-hidden='true' focusable='false' data-prefix='fas' data-icon='circle' role='img' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512' data-fa-i2svg=''><path fill='currentColor' d='M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z'></path></svg><svg style='width: 0.75rem;' class='svg-inline--fa fa-info fa-w-6 fa-stack-1x persona-svg' aria-hidden='true' focusable='false' data-prefix='fas' data-icon='info' role='img' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 192 512' data-fa-i2svg=''><path fill='currentColor' d='M20 424.229h20V279.771H20c-11.046 0-20-8.954-20-20V212c0-11.046 8.954-20 20-20h112c11.046 0 20 8.954 20 20v212.229h20c11.046 0 20 8.954 20 20V492c0 11.046-8.954 20-20 20H20c-11.046 0-20-8.954-20-20v-47.771c0-11.046 8.954-20 20-20zM96 0C56.235 0 24 32.235 24 72s32.235 72 72 72 72-32.235 72-72S135.764 0 96 0z'></path></svg></span><span class='uds-tooltip-visually-hidden'>Notifications</span><br /><span class='persona_text'>Deep diver</span></button><div role='tooltip' class='uds-tooltip-description persona-tip-description' id='tooltip-desc-diver'><span class='uds-tooltip-heading persona-tooltip-heading'>$svg_deep_diver_title_value%</span><div class='formatted-text persona-formatted-text'>$learner_data_content</div></div></div></div>";

            //social networker data
        $social_data_content = "My college experience is enhanced by the people I meet.";
       /* $social_netwoker_content = "<div class='persona-parent'><div id='natural_networker' class='personality' data-toggle='popover' title='$svg_natural_networker_title_value%' data-placement='bottom' data-html='true' data-content='$social_data_content'><span class='percent_value'><strong>$svg_natural_networker_title_value% &nbsp;&nbsp; <i class='fa fa-info-circle' aria-hidden='true'></i></strong></span><br />Natural networker</div><div class='child2 child_hide'>$social_data_content</div></div>";*/
        $social_netwoker_content = "<div class='persona-parent uds-tooltip-bg-black'><div id='natural_networker' class='personality uds-tooltip-container'><button tabindex='0' class='uds-tooltip uds-tooltip-dark' aria-describedby='tooltip-desc-networker'><span class='fa-stack'><span class='persona-percent'>$svg_natural_networker_title_value% </span><svg class='svg-inline--fa fa-circle fa-w-16 fa-stack-2x' aria-hidden='true' focusable='false' data-prefix='fas' data-icon='circle' role='img' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512' data-fa-i2svg=''><path fill='currentColor' d='M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z'></path></svg><svg style='width: 0.75rem;' class='svg-inline--fa fa-info fa-w-6 fa-stack-1x persona-svg' aria-hidden='true' focusable='false' data-prefix='fas' data-icon='info' role='img' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 192 512' data-fa-i2svg=''><path fill='currentColor' d='M20 424.229h20V279.771H20c-11.046 0-20-8.954-20-20V212c0-11.046 8.954-20 20-20h112c11.046 0 20 8.954 20 20v212.229h20c11.046 0 20 8.954 20 20V492c0 11.046-8.954 20-20 20H20c-11.046 0-20-8.954-20-20v-47.771c0-11.046 8.954-20 20-20zM96 0C56.235 0 24 32.235 24 72s32.235 72 72 72 72-32.235 72-72S135.764 0 96 0z'></path></svg></span><span class='uds-tooltip-visually-hidden'>Notifications</span><br /><span class='persona_text'>Natural networker</span></button><div role='tooltip' class='uds-tooltip-description persona-tip-description' id='tooltip-desc-networker'><span class='uds-tooltip-heading persona-tooltip-heading'>$svg_natural_networker_title_value%</span><div class='formatted-text persona-formatted-text'>$social_data_content</div></div></div></div>";


        //trailblazer (self actualizer)
        $self_data_content = '<p>My college experience is enhanced by the impact I make.</p>';
        /*$self_trailblazer_content = "<div class='persona-parent'><div id='trailblazer' class='personality child1' data-toggle='popover' title='$svg_trailblazer_title_value%' data-placement='bottom' data-html='true' data-content='$self_data_content'><span class='percent_value'><strong>$svg_trailblazer_title_value% &nbsp;&nbsp; <i class='fa fa-info-circle' aria-hidden='true'></i></strong></span><br />Trailblazer</div><div class='child2 child_hide'>$self_data_content</div></div>";*/
        $self_trailblazer_content = "<div class='persona-parent uds-tooltip-bg-black'><div id='trailblazer' class='personality uds-tooltip-container'><button tabindex='0' class='uds-tooltip uds-tooltip-dark' aria-describedby='tooltip-desc-actualizer'><span class='fa-stack'><span class='persona-percent'>$svg_trailblazer_title_value% </span><svg class='svg-inline--fa fa-circle fa-w-16 fa-stack-2x' aria-hidden='true' focusable='false' data-prefix='fas' data-icon='circle' role='img' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512' data-fa-i2svg=''><path fill='currentColor' d='M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z'></path></svg><svg style='width: 0.75rem;' class='svg-inline--fa fa-info fa-w-6 fa-stack-1x persona-svg' aria-hidden='true' focusable='false' data-prefix='fas' data-icon='info' role='img' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 192 512' data-fa-i2svg=''><path fill='currentColor' d='M20 424.229h20V279.771H20c-11.046 0-20-8.954-20-20V212c0-11.046 8.954-20 20-20h112c11.046 0 20 8.954 20 20v212.229h20c11.046 0 20 8.954 20 20V492c0 11.046-8.954 20-20 20H20c-11.046 0-20-8.954-20-20v-47.771c0-11.046 8.954-20 20-20zM96 0C56.235 0 24 32.235 24 72s32.235 72 72 72 72-32.235 72-72S135.764 0 96 0z'></path></svg></span><span class='uds-tooltip-visually-hidden'>Notifications</span><br /><span class='persona_text'>Trailblazer</span></button><div role='tooltip' class='uds-tooltip-description persona-tip-description' id='tooltip-desc-actualizer'><span class='uds-tooltip-heading persona-tooltip-heading'>$svg_trailblazer_title_value%</span><div class='formatted-text persona-formatted-text'>$self_data_content</div></div></div></div>";

        //athletic data
        //$athletic_data_content = "<p>Competitive, loyal, energetic, social, assertive, collaborative, and have work hard-play hard attitude.</p>";
        $athletic_data_content = "<p>My college experience is enhanced by being part of a bigger team.</p>";
     /*   $athlet_superfan_content = "<div class='persona-parent'><div id='superfan' class='personality' data-toggle='popover' title='$svg_superfan_title_value%'  data-placement='bottom' data-html='true' data-content='$athletic_data_content'><span class='percent_value'><strong>$svg_superfan_title_value% &nbsp;&nbsp; <i class='fa fa-info-circle' aria-hidden='true'></i></strong></span><br />Superfan</div><div class='child2 child_hide'>$athletic_data_content</div></div>";*/
        $athlet_superfan_content = "<div class='persona-parent uds-tooltip-bg-black'><div id='superfan' class='personality uds-tooltip-container'><button tabindex='0' class='uds-tooltip uds-tooltip-dark' aria-describedby='tooltip-desc-superfan'><span class='fa-stack'><span class='persona-percent'>$svg_superfan_title_value% </span><svg class='svg-inline--fa fa-circle fa-w-16 fa-stack-2x' aria-hidden='true' focusable='false' data-prefix='fas' data-icon='circle' role='img' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512' data-fa-i2svg=''><path fill='currentColor' d='M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z'></path></svg><svg style='width: 0.75rem;' class='svg-inline--fa fa-info fa-w-6 fa-stack-1x persona-svg' aria-hidden='true' focusable='false' data-prefix='fas' data-icon='info' role='img' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 192 512' data-fa-i2svg=''><path fill='currentColor' d='M20 424.229h20V279.771H20c-11.046 0-20-8.954-20-20V212c0-11.046 8.954-20 20-20h112c11.046 0 20 8.954 20 20v212.229h20c11.046 0 20 8.954 20 20V492c0 11.046-8.954 20-20 20H20c-11.046 0-20-8.954-20-20v-47.771c0-11.046 8.954-20 20-20zM96 0C56.235 0 24 32.235 24 72s32.235 72 72 72 72-32.235 72-72S135.764 0 96 0z'></path></svg></span><span class='uds-tooltip-visually-hidden'>Notifications</span><br /><span class='persona_text'>Superfan</span></button><div role='tooltip' class='uds-tooltip-description persona-tip-description' id='tooltip-desc-superfan'><span class='uds-tooltip-heading persona-tooltip-heading'>$svg_superfan_title_value% </span><div class='formatted-text persona-formatted-text'>$athletic_data_content</div></div></div></div>";

        //save percentages in webform hidden field

        // Change submission data.
        $result_saved_percent = '';
        foreach ($personality_copy_array as $skey => $svalue) {
            $sp_value = number_format($svalue, 2);
            $result_saved_percent .= "$skey = $sp_value, ";
        }

        //load submission
        $webform_submission_persona = WebformSubmission::load($sid);
		$submission_data_persona = $webform_submission_persona->getData();
		
        if (empty($submission_data_persona['quiz_results'])) {
            $webform_submission_persona->setElementData('quiz_results', $result_saved_percent); //save quiz results in webform field
            //$submission->data[24][0] =  $result_saved_percent;
        }
        if (empty($submission_data_persona['persona'])) {
            $webform_submission_persona->setElementData('persona', $top_persona_saved); //save persona value in webform field
            //$submission->data[25][0] = $top_persona_saved;
        }
		//ksm($submission_data_persona);
        // Save submission.
        $webform_submission_persona->save();

        $_SESSION['persona'] = $top_persona_saved;
		//$the_message = new TranslatableMarkup('Ssession persona: <pre>' . print_r($_SESSION, TRUE) . '<br />top presona:' . print_r($top_persona_saved, TRUE) . '<br /> </pre>', []);
		//$this->messenger()->addMessage($the_message);
		 \Drupal::logger('asu_quiz_persona')
					->notice('Persona value :<pre><code>' . print_r($_SESSION['persona'], TRUE) . '</code></pre>');
		//ksm($_SESSION);
        /* set the hero image path based on the persoan value */

        if($top_persona_saved == "focused_futurist"){
            $top_image = '<img src="https://admission.asu.edu/sites/default/files/futuristblur.jpg" alt="Focused futurist" class="media-element file-responsive-image img-fluid" data-delta="1" data-fid="916" data-media-element="1" data-mce-src="https://admission.asu.edu/sites/default/files/futurist.jpg?itok=ezhhQd0c" data-mce-selected="1">';
        }

        if($top_persona_saved == "deep_diver"){
            $top_image = '<img src="https://admission.asu.edu/sites/default/files/diverblur.jpg" alt="Deep diver" class="media-element file-responsive-image img-fluid" data-delta="2" data-fid="917" data-media-element="1" data-mce-src="https://admission.asu.edu//sites/default/files/diver.jpg?itok=PoA7UW4v" data-mce-selected="1">';
        }

        if($top_persona_saved == "trailblazer"){
            $top_image = '<img src="https://admission.asu.edu/sites/default/files/trailblazerblur.jpg" alt="Trailblazer" class="media-element file-responsive-image img-fluid" data-delta="3" data-fid="912" data-media-element="1" data-mce-src="https://admission.asu.edu/sites/default/files/trailblazer.jpg" data-mce-selected="1">';
        }

        if($top_persona_saved == "natural_networker"){
            $top_image = '<img src="https://admission.asu.edu/sites/default/files/networkerblur.jpg" alt="Natural networker" class="media-element file-responsive-image img-fluid" data-delta="4" data-fid="901" data-media-element="1" data-mce-src="https://admission.asu.edu/sites/default/files/networker.jpg" data-mce-selected="1">';
        }

        if($top_persona_saved == "superfan"){
            $top_image = '<img src="https://admission.asu.edu/sites/default/files/superfanblur.jpg" alt="Superfan" class="media-element file-responsive-image img-fluid" data-delta="6" data-fid="909" data-media-element="1" data-mce-src="https://admission.asu.edu/sites/default/files/superfan.jpg" data-mce-selected="1">';
        }

        $top_hero_image_content = "<div class='row no-gutters'><div class='rfi_conf_image bg-top bg-percent-100 layout__full-width'><div class='quiz_conf_h1_div'><h1 class='quiz_conf_h1'>What kind of student will you be?</h1></div>".$top_image."</div></div>";

        /* end of code: set the hero image path based on the persoan value */

        setcookie('persona', $top_persona_saved, time() + (86400 * 30), "/");
        
		//get persona confirmation content from admin settings page
        $personaconfig = \Drupal::config('asu_quiz.admin_settings');
        $question_count = $personaconfig->get('persona_questions_count');

        $personality_content_array = array('focused_futurist' => $driven_focused_content, 'deep_diver' => $learner_diver_content, 'trailblazer' => $self_trailblazer_content, 'natural_networker' => $social_netwoker_content, 'superfan' => $athlet_superfan_content);


        $results_array = array_merge($personality_array, $personality_content_array);

        reset($results_array); //reset array to get key values

        $top_result = key($results_array);
		$top_image = $personaconfig->get($top_result . '_image');
        $top_text = $personaconfig->get($top_result . '_content');
       
        $top_image_content = "<div class='conf_image'>" . $top_image . "</div>";
        $top_text_content = "<div class='conf_content_text'>" . $top_text . "</div>";

		
        $svg = "<svg width='100%' height='100%' viewBox='0 0 42 42' class='donut'><circle class='donut-hole' cx='21' cy='21' r='15.91549430918954' fill='#fff'></circle><circle class='donut-ring' cx='21' cy='21' r='15.91549430918954' fill='transparent' stroke='#d2d3d4' stroke-width='4'></circle>
   <circle class='donut-segment  focused_futurist' cx='21' cy='21' r='15.91549430918954' fill='transparent' stroke='#FF7F32' stroke-width='4' stroke-dasharray='$svg_focused_futurist_value $remain_driven_value' stroke-dashoffset='25'></circle><circle class='donut-segment  deep_diver' cx='21' cy='21' r='15.91549430918954' fill='transparent' stroke='#00A3E0' stroke-width='4' stroke-dasharray='$svg_deep_diver_value $rem_lifelong_learner' stroke-dashoffset='$lifelong_learner_offset'></circle><circle class='donut-segment trailblazer' cx='21' cy='21' r='15.91549430918954' fill='transparent' stroke='#FFC627' stroke-width='4' stroke-dasharray='$svg_trailblazer_value $rem_self_actualizer' stroke-dashoffset='$self_actualizer_offset'></circle><circle class='donut-segment  natural_networker' cx='21' cy='21' r='15.91549430918954' fill='transparent' stroke='#8C1D40' stroke-width='4' stroke-dasharray='$svg_natural_networker_value $rem_socially_involved' stroke-dashoffset='$socially_involved_offset'></circle><circle class='donut-segment superfan' cx='21' cy='21' r='15.91549430918954' fill='transparent' stroke='#78BE20' stroke-width='4' stroke-dasharray='$svg_superfan_value $rem_athletically_involved' stroke-dashoffset='$athletically_involved_offset'></circle></svg>";


        $combined_data = implode($results_array); //combine percentage and div content data into one array

        $jsonData = json_encode($combined_data);
        $right_personality_div_open = "<div id='right_personality_content'><div class='right_content_inner'>";

        $right_personality_div_close = "</div></div>";
        $body = $top_hero_image_content;
        $body .="<div id='quiz_conf_top_conatiner'class='quiz-container'>$top_image_content<hr /><div class='inner_quiz_cont'><div id='donut_svg'>$svg</div>$right_personality_div_open$combined_data$right_personality_div_close</div><hr />$top_text_content</div>";


        return array(
            '#markup' => \Drupal\Core\Render\Markup::create($body),
            '#cache' => array(
                'max-age' => 0,
            ),
            '#attached' => [
                'library' => [
                    'asu_quiz/Quizconfirmationjs',
                ],
            ],
        );


    }
}