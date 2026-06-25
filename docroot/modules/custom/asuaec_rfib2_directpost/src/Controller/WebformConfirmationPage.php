<?php
/**
 * @file
 */
namespace Drupal\asuaec_rfib2_directpost\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

class WebformConfirmationPage extends ControllerBase
{
    /**
     * Called from Block plugin (rfiConfirmationBlock.php).
     * Build content of the confirmation block.
     */
    public function process(Request $request = null, string $sid = null, string $fname = null, string $campus_option = null, string $grad_ugrad = null, string $plancode = null, string $student_type = null, string $interest = null) {

        $config = \Drupal::config('asuaec_rfib2_directpost.settings');
        $rfipage_nid = $config->get('asuaec_rfi_rfipage_nid');
        $rfipage_nid = ($rfipage_nid != null || $rfipage_nid != '') ? $rfipage_nid : 3297;

        // If there is plancode, get degree data:
        // - ND: $degree_data_array['nd_college_url'], $degree_data_array['programDesrc100'] <--- There is no ND included in the new form.
        // - Else: $degree_data_array['plan_url'], $degree_data_array['desrc100'], campuses_text'
        // Check if Session has the new $degree_data_array info.
        $degree_data_array = array();
        $session = \Drupal::request()->getSession();
        $degree_data_array = $session->get('asuaec_rfi.degree_data_array');
//        ksm($degree_data_array, "process - degree_data_array");

        if($campus_option != 'ONLNE') {
            if(is_null($degree_data_array) || $degree_data_array == '' || is_null($degree_data_array['sid']) || ($degree_data_array['sid'] == '') || ($degree_data_array['sid'] != $sid)) {
                if($plancode != null) {
                    $degree_data_array = $this->getDegreeData($plancode, $grad_ugrad, $sid);
                } else if ($interest != null) {
                    $degree_data_array = $this->getInterestData($interest, $grad_ugrad, $sid);
                }
            }
        }

        if($campus_option == 'ONLNE') {
            $output = "<p>Your future is very bright.</p><p>Thank you for your interest in transferring to Arizona State University. You will be receiving information from ASU very soon.</p>";
        }
        else { // Ground

            if ($grad_ugrad == 'GRAD') { // Ground Grad

                $output = '<p>' . $fname . ',</p>';
                $output .= '<p>We’re excited you’re interested in Arizona State University, and we invite you to take full advantage of the Sun Devil experience. At ASU, we are here to support you as you transform into an accomplished learner, prepared to succeed in your career.</p>';
                // If there is plancode
                if($plancode != null && sizeof($degree_data_array) > 0) {
                    $output .= '<p>Find out more about your program of interest, <a href="' . $degree_data_array['plan_url'] . '">' . $degree_data_array['descr100'] . ' - ' . $degree_data_array['campuses_text'] . '</a></p>';
                }
                $output .= 'If you have any questions, don’t hesitate to contact us at <a href="mailto:gograd@asu.edu">gograd@asu.edu</a>.';

            }
            else { // Ground Ugrad

                // if came from Degree search, also, if there is plancode
                if($plancode != null && sizeof($degree_data_array) > 0) {
                    $new_output = isset($degree_data_array['plan_linked']) ? '<p>Thank you for your interest in ' . $degree_data_array['plan_linked'] . '. Continue to explore the requirements of the degree and plan your transition to ASU.</p>' : '<p>Continue to explore the requirements of the degree and plan your transition to ASU.</p>';

                }

                else {
                    ///////////////////////////////////////////////////
                    // When the form submission doesn't have plan code
                    // (When user didn't come from the Degree Search)
                    ///////////////////////////////////////////////////

                    $new_output = isset($degree_data_array['interest_linked']) ? '<p>Take a look at your area of interest: ' . $degree_data_array['interest_linked'] . '. Explore the requirements of the degrees offered and continue to plan your transition to ASU.</p>' : '<p>Explore the requirements of the degrees offered and continue to plan your transition to ASU.</p>';
                }

                if($student_type == 'Transfer'){
                    $output = '<h2>Thank you for contacting Transfer Admissions.</h2><p>We’re interested in you too! You&#8217;ll be receiving more information from us soon. Until then, here are several ways for you to explore ASU degrees and campus environments to see what fits you best. ' . $new_output . '</p><h3>All are welcome at ASU</h3><p>There isn\'t one right way to be a human, so there definitely isn’t one right way to be a student. Luckily there are many ways to make your ASU experience have meaning and impact. Take this quiz to see what kind of student you’re likely to be, and how you’ll thrive in college.</p><p><a href="/quiz/experience?rfi_sid='.$sid.'&rfi_nid='.$rfipage_nid.'&fname='.$fname.'" class="btn btn-primary" style="margin-top:0px;">Take the persona quiz</a></p><h3>Find your perfect campus size and feel.</h3><p>ASU is made up of several campuses — each with its own identity, focus and size. Though different in feel and atmosphere, each campus offers you access to the benefits of ASU resources, support, student life and quality academics. Take this quick quiz to find the campus experience that best suits you.</p><p><a href="https://yourfuture.asu.edu/content/asu-campus-fits-you" class="btn btn-primary" style="margin-top:0px;">Take the my ASU fit quiz</a></p><h3>Visit campus and see for yourself </h3><p>We encourage you to plan a visit to campus to see for yourself what Sun Devil life is like. ASU offers year-round campus tours at all five ASU locations to give you a firsthand look at student life.</p><p><a href="https://visit.asu.edu" class="btn btn-gold" style="margin-top:0px;">Schedule a visit</a>&nbsp;&nbsp;<a href="https://tours.asu.edu" class="btn btn-gold" style="margin-top:0px;">Take a virtual tour</a></p><h3>Take the next step</h3><p>If you’re ready, apply to ASU today. Your admission specialist can help answer any questions you have about the enrollment process or becoming a Sun Devil. If you are an on-campus student, contact Jackie Collins by <a href="mailto:ASUJackiecollins@asu.edu">email</a> or by <a href="https://calendly.com/jackiecollins">scheduling an appointment</a>.<p><a href="https://admission.asu.edu/apply" class="btn btn-gold" style="margin-top:0px;">Apply now</a></p>';
                    $output .= '<p><strong>We look forward to working with you on your transfer to the university.</strong></p>';

                }

                if($student_type == 'First Time Freshman'){
                    $output = '<h2>Thank you for your interest in ASU.</h2><p>We’re interested in you too! You&#8217;ll be receiving more information from us soon. Until then, here are several ways for you to explore ASU degrees and campus environments to see what fits you best. ' . $new_output . '</p><h3>All are welcome at ASU</h3><p>There isn\'t one right way to be a human, so there definitely isn’t one right way to be a student. Luckily there are many ways to make your ASU experience have meaning and impact. Take this quiz to see what kind of student you’re likely to be, and how you’ll thrive in college.</p><p><a href="/quiz/experience?rfi_sid='.$sid.'&rfi_nid='.$rfipage_nid.'&fname='.$fname.'" class="btn btn-primary" style="margin-top:0px;">Take the persona quiz</a></p><h3>Find your perfect campus size and feel.</h3><p>ASU is made up of several campuses — each with its own identity, focus and size. Though different in feel and atmosphere, each campus offers you access to the benefits of ASU resources, support, student life and quality academics. Take this quick quiz to find the campus experience that best suits you.</p><p><a href="https://yourfuture.asu.edu/content/asu-campus-fits-you" class="btn btn-primary" style="margin-top:0px;">Take the my ASU fit quiz</a></p><h3>Visit campus and see for yourself </h3><p>We encourage you to plan a visit to campus to see for yourself what Sun Devil life is like. ASU offers year-round campus tours at all five ASU locations to give you a firsthand look at student life.</p><p><a href="https://visit.asu.edu" class="btn btn-gold" style="margin-top:0px;">Schedule a visit</a>&nbsp;&nbsp;<a href="https://tours.asu.edu" class="btn btn-gold" style="margin-top:0px;">Take a virtual tour</a></p><h3>Take the next step</h3><p>If you’re ready, apply to ASU today. Your admission specialist can help answer any questions you have about the enrollment process or becoming a Sun Devil. If you are an on-campus student, <a href="https://admission.asu.edu/contact/undergraduate">contact your admission representative</a>.<p><a href="https://admission.asu.edu/apply" class="btn btn-gold" style="margin-top:0px;">Apply now</a></p>';
                    $output .= '<p><strong> It\'s time to be a Sun Devil! </strong></p>';
                }

            } // END OF else { // Ground Ugrad
        } // END OF else { // Ground

        return array(
            '#markup' => \Drupal\Core\Render\Markup::create($output),
            '#cache' => array( // Turn off cache.
                'max-age' => 0,
            ),
        );

    } // END OF public function process()


    /**
     * Get degree data from DB.
     * Then, place it in session variable for later use.
     */
    public function getDegreeData($plancode, $grad_ugrad, $sid) {
        $degree_data_array = array();
        // Submission id
        $degree_data_array['sid'] = $sid;

        // plan_url
        if($grad_ugrad == 'GRAD') {
            $degree_data_array['plan_url'] = 'https://webapp4.asu.edu/programs/t5/majorinfo/ASU00/' . $plancode . '/graduate/false';
        } else if($grad_ugrad == 'UGRAD') {
            $degree_data_array['plan_url'] = 'https://webapp4.asu.edu/programs/t5/majorinfo/ASU00/' . $plancode . '/undergrad/false';
        }

        // descr100 (degree name) and campuses
        // Get them from db table
        if($grad_ugrad == 'GRAD') {
            $table = 'asu_grad_interest_category_degrees';
        } else if ('UGRAD') {
            $table = 'asu_ugrad_interest_category_degrees';
        }
        $fields_array  = [];
        if($grad_ugrad == 'GRAD') {
            $fields_array = array('categorymajorname', 'categorycampuscode', 'categoryDegreeDescShort'); // descr100 and CampusStringArray and degree_descr_short ex. MS, PHD, etc.
        } else if ('UGRAD') {
            $fields_array = array('categorymajorname', 'categorycampuscode', 'categorydegree'); // descr100 and CampusStringArray
        }

        $database = \Drupal::database();
        $query = $database->select($table, 't');
        $query->fields('t', $fields_array);
//        $query->condition('keyword', '%' . Database::getConnection()->escapeLike($input) . '%', 'LIKE');
        $query->condition('categorymajorcode', $plancode, '=');
//        $query->orderBy($field, 'ASC');
//        $query->range(0, 10);
        $result = $query->distinct()->execute();
        $campus_codes_string = '';
        foreach ($result as $record) {
            $degree_data_array['descr100'] = $record->categorymajorname;
            $campus_codes_string = isset($record->categorycampuscode) ? $record->categorycampuscode : '';
            if($grad_ugrad == 'GRAD') {
                $degree_data_array['degree_descr_short'] = $record->categoryDegreeDescShort; // degree_descr_short ex. MS, PHD, etc.
            } else if ('UGRAD') {
                $degree_data_array['degree_descr_short'] = $record->categorydegree; // degree_descr_short ex. MS, PHD, etc.
            }
//            $degree_data_array['categoryid'] = $record->categoryid;
        }

        // Campuses
        $degree_data_array['campuses_text'] = generateCampusesText($campus_codes_string);

        // Clear session before save the new one.
        $request = \Drupal::request();
        $session = $request->getSession();
        $session->remove('asuaec_rfi.degree_data_array');

        // Save degree_data_array in session
        $session = $request->getSession();
        $session->set('asuaec_rfi.degree_data_array', $degree_data_array);

        return $degree_data_array;
    }

    /**
     * Build $degree_data_array['interest_linked'].
     * Then, place it in session variable for later use.
     */
    public function getInterestData($interest, $grad_ugrad, $sid) { // It will be always Ugrad.
        $degree_data_array['sid'] = $sid;

        // Get Interest code
        $interest_code = getInterestCode($interest, $grad_ugrad);

        $url = 'https://webapp4.asu.edu/programs/t5/programs/AreaOfInterest/' . $interest_code . '/undergrad/false';
        $degree_data_array['interest_linked'] = '<a style="color:#8C1D40;" href="' . $url . '">' . $interest . '</a>';

        // Clear session before save the new one.
        $request = \Drupal::request();
        $session = $request->getSession();
        $session->remove('asuaec_rfi.degree_data_array');

        // Save degree_data_array in session
        $session = $request->getSession();
        $session->set('asuaec_rfi.degree_data_array', $degree_data_array);

        return $degree_data_array;

    } // END OF public function getInterestData($interest)



} // END OF class WebformConfirmationPage


/**
 * Helping function
 * Generate text such as "Tempe, Downtown Phoenix, West".
 * Called from function getDegreeData.
 */
function generateCampusesText($campus_codes_string = '') {

    // Create campuses array
    if ($campus_codes_string != '') {

        $campus_codes_array = explode(',', $campus_codes_string);

        $campus_array = array();
        $online = false;
        foreach ($campus_codes_array as $campus) {
            switch ($campus) {
                case 'DTPHX':
                    $campus_array['Downtown Phoenix'] = 'http://www.asu.edu/tour/downtown/index.html';
                    break;
                case 'TEMPE':
                    $campus_array['Tempe'] = 'http://www.asu.edu/tour/tempe/index.html';
                    break;
                case 'WEST':
                    $campus_array['West'] = 'http://www.asu.edu/tour/west/index.html';
                    break;
                case 'POLY':
                    $campus_array['Polytechnic'] = 'http://www.asu.edu/tour/polytechnic/index.html';
                    break;
                case 'ONLNE':
                    $campus_array['Online'] = 'http://asuonline.asu.edu/';
                    $online = true;
                    break;
                case 'CALHC':
                    $campus_array['Lake Havasu City'] = '';
                    break;
                case 'EAC':
                    $campus_array['Eastern Arizona College'] = '';
                    break;
                case 'TBIRD':
                    $campus_array['Thunderbird'] = 'http://www.thunderbird.edu';
                    break;
            }
        }
        ksort($campus_array);
//        $degree_data_array['campus_array'] = $campus_array;
//        $degree_data_array['online'] = $online;

        // List campuses
        $campuses_text = '';
        $i = 0;
        foreach ($campus_array as $key => $value) {
            if ($i == 0) {
                $campuses_text .= $key;
            } else {
                $campuses_text .= ", $key";
            }
            $i++;
        }
        return $campuses_text;

    } // END OF if (!is_null($campus_string_array))

} // END OF function generateCampusesText()

/**
 * Helping function
 * Get Interest code (category ID) from DB.
 * Called from function getInterestData.
 * It will be always UGRAD.
 */
function getInterestCode($interest, $grad_ugrad) {
    $categoryid = '';
    // Get interest code from db table
    if($grad_ugrad == 'GRAD') {
        $table = 'asu_grad_interest_category_degrees';
    } else if ('UGRAD') {
        $table = 'asu_ugrad_interest_category_degrees';
    }
    $fields_array  = array('categoryid');

    $database = \Drupal::database();
    $query = $database->select($table, 't');
    $query->fields('t', $fields_array);
//        $query->condition('keyword', '%' . Database::getConnection()->escapeLike($input) . '%', 'LIKE');
    $query->condition('categoryname', $interest, '=');
//        $query->orderBy($field, 'ASC');
//        $query->range(0, 1);
    $result = $query->distinct()->execute();
    foreach ($result as $record) {
        $categoryid = $record->categoryid;
    }
    return $categoryid;
}
