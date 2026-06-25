<?php

namespace Drupal\compensation_estimator\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

class CompensationEstimatorForm extends FormBase
{

    public function getFormId()
    {
        return 'compensation_estimator';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {

        // Get the config object
        $config = $this->config('compensation_estimator.settings');
        // $form['message'] = [
        //     '#type' => 'markup',
        //     '#markup' => '<h1>Compensation estimator form</h1>',
        // ];
        $introconfig = $config->get('introconfig');
        $form['intro'] = [
            '#type' => 'markup',
            //     '#markup' => t('ASU’s comprehensive benefits package is a significant part of your overall total compensation—and ASU pays for most of it when you are a benefits-eligible employee. To estimate the value of your total compensation, complete the form below and click Calculate. <br />
            //   MORE BENEFITS INFO: <a href="https://cfo.asu.edu/hr-benefits">cfo.asu.edu/hr-benefits</a><br /><br />'),
            '#markup' => $introconfig
        ];

        //field is hidden from form
        $form['date'] = [
            '#type' => 'select',
            '#title' => t('ER'),
            '#default_value' => 1,
            '#options' => array(
                0 => t('1/1/21-6/30/21'),
                1 => t('7/1/21-12/31/21'),
            ),
            '#access' => FALSE,
        ];

        // $form['hireddate'] = [
        //     '#type' => 'select',
        //     '#title' => t('When were you hired?'),
        //     '#required' => TRUE,
        //     '#empty_option' => t(' -- Select -- '),
        //     '#options' => array(
        //         0 => t('Hired on/after 7/1/96'),
        //         1 => t('Hired before 7/1/96'),
        //     ),
        // ];

        //FIFTH SELECT LIST
        $postdocrtwConfigtitle = $config->get('postdocrtwConfigtitle');
        $postdocrtwConfigValue = $config->get('postdocrtwConfig');
        $postdocrtwConfigValueSeperatedByNewline = explode("\n", $postdocrtwConfigValue);
        $form['postdocrtw'] = [
            '#type' => 'select',
            // '#title' => t('What type of job are you considering at ASU?'),
            '#title' => t($postdocrtwConfigtitle),
            '#empty_option' => t(' -- Select -- '),
            // '#options' => array(
            //    0 => t('Administrative, faculty or staff'),
            //    1 => t('Post-doctoral scholar'),
            //    2 => t('Return-to-work retiree'),
            // ),
            '#options' => $postdocrtwConfigValueSeperatedByNewline,
        ];
        //END OF FIFTH SELECT LIST

        //SIXTH SELECT LIST
        $rtwConfigtitle = $config->get('rtwconfigtitle');
        $rtwConfigValue = $config->get('rtwconfig');
        $rtwConfigValueSeperatedByNewline = explode("\n", $rtwConfigValue);
        $form['rtw'] = [
            '#type' => 'select',
            // '#title' => t('Which of the following applies to you?'),
            '#title' => t($rtwConfigtitle),
            '#empty_option' => t(' -- Select -- '),
            // '#options' => array(
            //    0 => t('I am an ASRS retiree collecting pension'),
            //    1 => t('I am a PSPRS retiree collecting pension'),
            //    2 => t('I am NOT an ASRS or PSPRS retiree'),
            // ),
            '#options' => $rtwConfigValueSeperatedByNewline,
            '#states' => array(
                'visible' => array(
                    ':input[name="postdocrtw"]' => array('value' => '2'),
                ),
            ),
        ];
        //END OF SIXTH SELECT LIST

        //SEVENTH SELECT LIST
        $retplanConfigtitle = $config->get('retplanconfigtitle');
        $retplanConfigValue = $config->get('retplanconfig');
        $retplanConfigValueSeperatedByNewline = explode("\n", $retplanConfigValue);
        $form['retplan'] = [
            '#type' => 'select',
            // '#title' => t('In which retirement plan are you enrolled?'),
            '#title' => t($retplanConfigtitle),
            '#empty_option' => t(' -- Select -- '),
            // '#options' => array(
            //    0 => t('Arizona State Retirement System (ASRS)'),
            //    1 => t('Optional Retirement Plan (ORP)'),
            //    2 => t('Public Safety Personnel Retirement System (PSPRS)'),
            //    -1 => t('Not enrolled'),
            // ),
            '#options' => $retplanConfigValueSeperatedByNewline,
            '#states' => array(
                'visible' => array(
                    ':input[name="postdocrtw"]' => array(
                        array('value' => '0'),
                        array('value' => '2'),
                    ),
                ),
            ),
        ];

        //EIGTH SELECT LIST
        $psprsConfigtitle = $config->get('psprsconfigtitle');
        $psprsConfigValue = $config->get('psprsconfig');
        $psprsConfigValueSeperatedByNewline = explode("\n", $psprsConfigValue);
        $form['psprsretplan'] = [
            '#type' => 'select',
            // '#title' => t('PSPRS retirement plan options'),
            '#title' => t($psprsConfigtitle),
            '#empty_option' => t(' -- Select -- '),
            // '#options' => array(
            //    0 => t('PSPRS member on or before June 30, 2017'),
            //    1 => t('PSPRS Defined benefit member on or after July 1, 2017'),
            //    2 => t('PSPRS Defined contribution member on or after July 1, 2017'),
            // ),
            '#options' => $psprsConfigValueSeperatedByNewline,
            '#states' => array(
                'visible' => array(
                    ':input[name="retplan"]' => array(
                        array('value' => 2),
                    ),
                ),
            ),
        ];
        //END OF EIGTH SELECT LIST

        //FIRST SELECT LIST
        // Get the key value
        $medPlanConfigtitle = $config->get('medplanconfigtitle');
        $medPlanConfigValue = $config->get('medplanconfig');
        $medPlanConfigValueSeperatedByNewline = explode("\n", $medPlanConfigValue);
        $form['medplan'] = [
            '#type' => 'select',
            // '#title' => t('In which medical plan are you enrolled?'),
            '#title' => t($medPlanConfigtitle),
            '#empty_option' => t(' -- Select -- '),
            // '#options' => array(
            //     0 => t('TCP - Triple Choice Plan, Blue Cross Blue Shield, or United HealthCare'),
            //     1 => t('HDHP - High Deductible Health Plan, Blue Cross Blue Shield, or United HealthCare'),
            //     -1 => t('Not enrolled'),
            // ),
            '#options' => $medPlanConfigValueSeperatedByNewline,
        ];
        //END OF FIRST SELECT LIST

        //SECOND SELECT LIST
        $medCovConfigtitle = $config->get('medcovconfigtitle');
        $medCovConfigValue = $config->get('medcovconfig');
        $medCovConfigValueSeperatedByNewline = explode("\n", $medCovConfigValue);
        $form['medcov'] = [
            '#type' => 'select',
            // '#title' => t('Whom does your medical plan cover?'),
            '#title' => t($medCovConfigtitle),
            '#empty_option' => t(' -- Select -- '),
            // '#options' => array(
            //     1 => t('Employee'),
            //     2 => t('Employee and spouse'),
            //     3 => t('Employee and child'),
            //     4 => t('Employee and family'),
            // ),
            '#options' => $medCovConfigValueSeperatedByNewline,
            // '#states' => array(
            //     'enabled' => array(
            //         ':input[name="medplan"]' => array('value' => -1),
            //     ),
            // ),
        ];
        //END OF SECOND SELECT LIST

        //THIRD SELECT LIST
        $denPlanConfigtitle = $config->get('denplanconfigtitle');
        $denPlanConfigValue = $config->get('denplanconfig');
        $denPlanConfigValueSeperatedByNewline = explode("\n", $denPlanConfigValue);
        $form['denplan'] = [
            '#type' => 'select',
            // '#title' => t('In which dental plan are you enrolled?'),
            '#title' => t($denPlanConfigtitle),
            '#empty_option' => t(' -- Select -- '),
            // '#options' => array(
            //    0 => t('Cigna Dental or Delta Dental PPO Plus Premier'),
            //    -1 => t('Not enrolled'),
            // ),
            '#options' => $denPlanConfigValueSeperatedByNewline,
        ];
        //END OF THIRD SELECT LIST

        //FOURTH SELECT LIST
        $denCovConfigtitle = $config->get('dencovconfigtitle');
        $denCovConfigValue = $config->get('dencovconfig');
        $denCovConfigValueSeperatedByNewline = explode("\n", $denCovConfigValue);
        $form['dencov'] = [
            '#type' => 'select',
            '#title' => t($denCovConfigtitle),
            // '#title' => t('Whom does your dental plan cover?'),
            '#empty_option' => t(' -- Select -- '),
            '#options' => $denCovConfigValueSeperatedByNewline,
            // '#options' => array(
            //     1 => t('Employee'),
            //     2 => t('Employee and spouse'),
            //     3 => t('Employee and child'),
            //     4 => t('Employee and family'),
            // ),
            // '#states' => array(
            //     'enabled' => array(
            //         ':input[name="denplan"]' => array('value' => -1),
            //     ),
            // ),
        ];



        $form['salary'] = [
            '#title' => t('What is your annual salary?'),
            '#type' => 'textfield',
            '#size' => 15,
            '#required' => TRUE,
        ];
        $salarydesciption = $config->get('salarydescriptionconfig');
        $form['hourlycheckbox'] = [
            '#type' => 'checkbox',
            '#title' => t('Hourly'),
            // '#description' =>t("(must be between 20 and 40)
            // Acceptable formats include: Annual (35000, 35,000 or $35,000.00); Hourly (12, 12.00 or $12.00)")
            '#description' => t($salarydesciption),
        ];

        $form['hourly'] = [
            '#title' => t('Hours per week'),
            '#type' => 'textfield',
            '#size' => 15,
            '#states' => array(
                'visible' => array(
                    ':input[name="hourlycheckbox"]' => array(
                        'checked' => TRUE,
                    ),
                ),
            )
        ];

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => 'Calculate',
            '#prefix' => '<br />',
            // '#suffix' => '<br /><br />',
        ];

        $form['reset'] = [
            '#type' => 'submit',
            '#value' => 'Reset',
            '#prefix' => '&emsp;',
            '#submit' => array('compensation_estimator_reset'),
            '#suffix' => '<br /><br />',
        ];

        $form['h1'] = [
            '#type' => 'item',
            '#markup' => 'Employee Benefit',
            '#prefix' => '<div class="uds-table"><table><tr><th>',
            '#suffix' => '</th>',
        ];

        $form['h2'] = [
            '#type' => 'item',
            '#markup' => 'ASU Contribution',
            '#prefix' => '<th>',
            '#suffix' => '</th></tr>',
        ];

        $form['r1c1'] = [
            '#type' => 'item',
            '#markup' => 'Medical Insurance',
            '#prefix' => '<tr><td>',
            '#suffix' => '</td>',
        ];

        $form['r1c2'] = [
            '#type' => 'item',
            '#markup' => empty($form_state->getValue('r1c2')) ? 0 : $form_state->getValue('r1c2'),
            '#prefix' => '<td>',
            '#suffix' => '</td></tr>',
        ];

        $form['r2c1'] = [
            '#type' => 'item',
            '#markup' => 'Dental Insurance',
            '#prefix' => '<tr><td>',
            '#suffix' => '</td>',
        ];

        $form['r2c2'] = [
            '#type' => 'item',
            '#markup' => empty($form_state->getValue('r2c2')) ? 0 : $form_state->getValue('r2c2'),
            '#prefix' => '<td>',
            '#suffix' => '</td></tr>',
        ];

        $form['r3c1'] = [
            '#type' => 'item',
            '#markup' => 'Retirement Plan Contributions*',
            '#prefix' => '<tr><td>',
            '#suffix' => '</td>',
        ];

        $form['r3c2'] = array(
            '#type' => 'item',
            '#markup' => empty($form_state->getValue('r3c2')) ? 0 : $form_state->getValue('r3c2'),
            '#prefix' => '<td>',
            '#suffix' => '</td></tr>',
        );
        // dpm("test");
        // dpm(empty($form_state->getValue('r3c2')) ? 0 : $form_state->getValue('r3c2'));

        $form['r4c1'] = [
            '#type' => 'item',
            '#markup' => 'Social Security**',
            '#prefix' => '<tr><td>',
            '#suffix' => '</td>',
        ];

        $form['r4c2'] = array(
            '#type' => 'markup',
            '#markup' => empty($form_state->getValue('r4c2')) ? 0 : $form_state->getValue('r4c2'),
            '#prefix' => '<td>',
            '#suffix' => '</td></tr>',
        );

        $form['r5c1'] = [
            '#type' => 'item',
            '#markup' => 'Medicare**',
            '#prefix' => '<tr><td>',
            '#suffix' => '</td>',
        ];

        $form['r5c2'] = [
            '#type' => 'markup',
            '#markup' => empty($form_state->getValue('r5c2')) ? 0 : $form_state->getValue('r5c2'),
            '#prefix' => '<td>',
            '#suffix' => '</td></tr>',
        ];

        $form['r6c1'] = [
            '#type' => 'item',
            '#markup' => 'Basic Life Insurance',
            '#prefix' => '<tr><td>',
            '#suffix' => '</td>',
        ];

        $form['r6c2'] = [
            '#type' => 'item',
            '#markup' => empty($form_state->getValue('r6c2')) ? 0 : $form_state->getValue('r6c2'),
            '#prefix' => '<td>',
            '#suffix' => '</td></tr>',
        ];

        $form['r7c1'] = [
            '#type' => 'item',
            '#markup' => 'Long-term Disability Insurance',
            '#prefix' => '<tr><td>',
            '#suffix' => '</td>',
        ];

        $form['r7c2'] = [
            '#type' => 'item',
            '#markup' => empty($form_state->getValue('r7c2')) ? 0 : $form_state->getValue('r7c2'),
            '#prefix' => '<td>',
            '#suffix' => '</td></tr>',
        ];

        $form['r8c1'] = [
            '#type' => 'item',
            '#markup' => 'Workers Compensation',
            '#prefix' => '<tr><td>',
            '#suffix' => '</td>',
        ];

        $form['r8c2'] = [
            '#type' => 'item',
            '#markup' => empty($form_state->getValue('r8c2')) ? 0 : $form_state->getValue('r8c2'),
            '#prefix' => '<td>',
            '#suffix' => '</td></tr></table></div><br />',
        ];

        $form['NotesOnSSMD'] = [
            '#type' => 'item',
            '#markup' => '*ASU contribution may be higher if you were hired before 1996. <br />
                        **Actual values may be lower based on your pre-tax deductions.<br /><br />',
        ];

        $form['sh1'] = [
            '#type' => 'item',
            '#markup' => 'Total',
            '#prefix' => '<div class="uds-table"><table><tr><th>',
            '#suffix' => '</th>',
        ];

        $form['sh2'] = [
            '#type' => 'item',
            '#markup' => 'Value',
            '#prefix' => '<th>',
            '#suffix' => '</th></tr>',
        ];

        $form['sr1c1'] = [
            '#type' => 'item',
            '#markup' => 'Benefits (ASU Contribution)',
            '#prefix' => '<tr><td>',
            '#suffix' => '</td>',
        ];

        $form['sr1c2'] = [
            '#type' => 'item',
            '#markup' => empty($form_state->getValue('sr1c2')) ? 0 : $form_state->getValue('sr1c2'),
            '#prefix' => '<td>',
            '#suffix' => '</td></tr>',
        ];

        $form['sr2c1'] = [
            '#type' => 'item',
            '#markup' => 'Salary',
            '#prefix' => '<tr><td>',
            '#suffix' => '</td>',
        ];

        $form['sr2c2'] = [
            '#type' => 'item',
            '#markup' => empty($form_state->getValue('sr2c2')) ? 0 : $form_state->getValue('sr2c2'),
            '#prefix' => '<td>',
            '#suffix' => '</td></tr>',
        ];

        $form['sr3c1'] = [
            '#type' => 'item',
            '#markup' => 'Total Compensation (Salary + ASU Contribution to Benefits)',
            '#prefix' => '<tr><td>',
            '#suffix' => '</td>',
        ];

        $form['sr3c2'] = [
            '#type' => 'item',
            '#markup' => empty($form_state->getValue('sr3c2')) ? 0 : $form_state->getValue('sr3c2'),
            '#prefix' => '<td>',
            '#suffix' => '</td></tr>',
        ];

        $form['sr4c1'] = [
            '#type' => 'item',
            '#markup' => 'ASU Contribution to Benefits as a Percentage of Salary',
            '#prefix' => '<tr><td>',
            '#suffix' => '</td>',
        ];

        $form['sr4c2'] = [
            '#type' => 'item',
            '#markup' => empty($form_state->getValue('sr4c2')) ? 0 : $form_state->getValue('sr4c2'),
            '#prefix' => '<td>',
            '#suffix' => '</td></tr></table></div>',
        ];

        $otherbenConfigValue = $config->get('otherbenconfig');
        $form['OtherBen'] = [
            '#type' => 'item',
            '#markup' => $otherbenConfigValue
            // '#markup' => '<br /><br />
            //     <b>Other ASU-sponsored Benefits</b>
            //     <br /><br />
            //     These benefits may be a part of your benefits package but are not included in your total compensation calculation:
            //     <ul>
            //     <li>Paid time-off benefits including*: 
            //     <ul>
            //     <li>Up to 22 vacation days per year</li> 
            //     <li>Up to 12 sick days per year</li>
            //     <li>10 paid holidays per year</li>
            //     <li>Up to 6 weeks of parental leave</li>
            //     <li>Up to 3 days of bereavement leave</li>
            //     <li>Jury duty leave</li>
            //     </ul>
            //     </li>
            //     <li>Employee Wellness Programs</li>
            //     <li>Employee Assistance Programs</li>
            //     </ul>
            //     ASU also provides access to these voluntary employee-paid programs:
            //     <ul>
            //     <li>Flexible Spending Accounts</li>
            //     <li>Voluntary Retirement Savings Plans</li>
            //     <li>Supplemental Life Insurance</li>
            //     <li>Long-term Care Insurance</li>
            //     <li>Auto/Home Insurance</li>
            //     <li>Discounted Valley Metro lightrail/bus pass</li>
            //     <li>Campus discounts (ASU Gammage, athletic events, ASU Sun Devil Store and more)</li>
            //     <li>Employee discounts for attractions and goods/services</li>
            //     </ul>
            //     *Paid time-off benefits vary and depend on your employment category, years of service and FTE.',
        ];

        $disclaimerConfigValue = $config->get('disclaimerconfig');
        $form['disclaimer'] = [
            '#type' => 'item',
            // '#markup' => '<br />
            //     <p><b>DISCLAIMER</b><br />This tool is for illustrative purposes only and provides benefits-eligible employees an estimate (not an actual dollar amount) of their total compensation based on specific options selected. The actual value of total compensation may be different. Estimates do not apply to student workers, part-time workers who are not benefits-eligible and independent contractors.
            //     <br /><br /><b>Current benefits-eligible employees</b>: Check your pay stub for exact figures or contact the Office of Human Resources at 855.ASU.5081 (855.278.5081).
            //     <br /><b>Prospective employees</b>: ASU cannot guarantee that you will receive the above computed benefits if you are hired in a benefits-eligible position.
            //     <br /><br />The Arizona Board of Regents  (ABOR) provides certain benefit plans to eligible employees described within each plan document or policy. ABOR or the ASU Office of Human Resources can provide copies of benefit plan documents and policies. All benefit plans and policies are subject to change.
            //     </p>',
            '#markup' => $disclaimerConfigValue
        ];

        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        parent::validateForm($form, $form_state);
        if (!is_numeric(trim($form_state->getValue('salary')))) {
            $form_state->setErrorByName('salary', t('Salary must be a number with no commas or dollar signs.'));
        }

        if (is_numeric(trim($form_state->getValue('salary')))) {

            // Get the config object
            $config = $this->config('compensation_estimator.settings');

            //Below are the annual contribution rates by ASU for CY 2021
            //note: 1 = employee, 2 = employee + adult, 3 = employee + child, 4 = family

            //medical rates for CY 2021
            $ep01ConfigValue = $config->get('ep01config');
            $EPO1 = $ep01ConfigValue;

            $ep02ConfigValue = $config->get('ep02config');
            $EPO2 = $ep02ConfigValue;

            $ep03ConfigValue = $config->get('ep03config');
            $EPO3 = $ep03ConfigValue;

            $ep04ConfigValue = $config->get('ep04config');
            $EPO4 = $ep04ConfigValue;

            // $PPO1=0;
            // $PPO2=0;
            // $PPO3=0;
            // $PPO4=0;

            $hsa1ConfigValue = $config->get('hsa1config');
            $hsa1ConfigValue = explode("+", $hsa1ConfigValue);
            $temp = 0;
            foreach ($hsa1ConfigValue as $hsa1value) {
                $temp = (float)$hsa1value + $temp;
            }
            $HSA1 = $temp;
            // $HSA1=4728.62+720;

            $hsa2ConfigValue = $config->get('hsa2config');
            $hsa2ConfigValue = explode("+", $hsa2ConfigValue);
            $temp = 0;
            foreach ($hsa2ConfigValue as $hsa2value) {
                $temp = (float)$hsa2value + $temp;
            }
            $HSA2 = $temp;
            // $HSA2=9800.70+1440;

            $hsa3ConfigValue = $config->get('hsa3config');
            $hsa3ConfigValue = explode("+", $hsa3ConfigValue);
            $temp = 0;
            foreach ($hsa3ConfigValue as $hsa3value) {
                $temp = (float)$hsa3value + $temp;
            }
            $HSA3 = $temp;
            // $HSA3=6406.66+1440;

            $hsa4ConfigValue = $config->get('hsa4config');
            $hsa4ConfigValue = explode("+", $hsa4ConfigValue);
            $temp = 0;
            foreach ($hsa4ConfigValue as $hsa4value) {
                $temp = (float)$hsa4value + $temp;
            }
            $HSA4 = $temp;
            // $HSA4=10919.22+1440;

            //dental rates for CY 2021
            $dental1ConfigValue = $config->get('dental1config');
            $dental1 = $dental1ConfigValue;
            // $dental1=59.54;

            $dental2ConfigValue = $config->get('dental2config');
            $dental2 = $dental2ConfigValue;
            // $dental2=119.08;

            $dental3ConfigValue = $config->get('dental3config');
            $dental3 = $dental3ConfigValue;
            // $dental3=119.08;

            $dental4ConfigValue = $config->get('dental4config');
            $dental4 = $dental4ConfigValue;
            // $dental4=164.32;

            $asrsConfigValue = $config->get('asrsconfig');
            // $PSPRSltd = 0.192; //max coverage of $180,000
            $ASRS = $asrsConfigValue;

            $asrsltdConfigValue = $config->get('asrsltdconfig');
            // $PSPRSltd = 0.192; //max coverage of $180,000
            $ASRSltd = $asrsltdConfigValue;

            $psprsltdConfigValue = $config->get('psprsltdconfig');
            // $PSPRSltd = 0.192; //max coverage of $180,000
            $PSPRSltd = $psprsltdConfigValue;

            $orpConfigValue = $config->get('orpconfig');
            // $ORP = 0.07; //cap at 255,000
            $ORP = $orpConfigValue;

            $orpltdConfigValue = $config->get('orpltdconfig');
            // $ORPltd = 0.192; //max coverage of $180,000
            $ORPltd = $orpltdConfigValue;

            //Social Security and Medicare rates for CY 2021
            $ssrConfigValue = $config->get('ssrconfig');
            // $SSR = 0.062; //social security capped 118,500
            $SSR = $ssrConfigValue;

            $mcrConfigValue = $config->get('mcrconfig');
            // $MCR = 0.0145; //no cap for medicare
            $MCR = $mcrConfigValue;

            //basic life insurance 
            $basiclife1ConfigValue = $config->get('basiclife1config');
            // $basiclife1=7.28; //this annual or 12*$1.95
            $basiclife1 = $basiclife1ConfigValue;

            $basiclife2ConfigValue = $config->get('basiclife2config');
            $basiclife2 = $basiclife2ConfigValue;
            // $basiclife2 = 0.0095; //cap at $385,000

            //worker's comp
            $wcompConfigValue = $config->get('wcompconfig');
            // $wcomp=0.0028; //no cap
            $wcomp = $wcompConfigValue;

            $ltdcapConfigValue = $config->get('ltdcapconfig');
            // $ltdcap = 180000; //cap for ltd rates for CY 2021
            $ltdcap = $ltdcapConfigValue;

            $sscapConfigValue = $config->get('sscapconfig');
            $sscap = $sscapConfigValue;
            // $sscap = 142800; //cap for social security taxes for CY 2021

            // getting all the salcap variables values from form
            // asrs sal cap
            $asrssalcapConfigValue = $config->get('asrssalcap');
            $asrssalcap = $asrssalcapConfigValue;
            // dpm("test");
            // dpm($asrssalcap);

            // orp sal cap
            $orpsalcapConfigValue = $config->get('orpsalcap');
            $orpsalcap = $orpsalcapConfigValue;
            // dpm($orpsalcap);

            // psrp sal cap 1
            $psprs1ConfigValue = $config->get('psprs1');
            $psprs1 = $psprs1ConfigValue;
            // dpm($psprs1);

            $psrpsalcap1ConfigValue = $config->get('psrpsalcap1');
            $psrpsalcap1 = $psrpsalcap1ConfigValue;
            // dpm($psrpsalcap1);

            // psrp sal cap 2
            $psprs2ConfigValue = $config->get('psprs2');
            $psprs2 = $psprs2ConfigValue;
            // dpm($psprs2);


            $psrpsalcap2ConfigValue = $config->get('psrpsalcap2');
            $psrpsalcap2 = $psrpsalcap2ConfigValue;
            // dpm($psrpsalcap2);

            // psrp sal cap 3
            $psprs3ConfigValue = $config->get('psprs3');
            $psprs3ConfigValue = explode("+", $psprs3ConfigValue);
            $temp = 0;
            foreach ($psprs3ConfigValue as $psprs3) {
                $temp = (float)$psprs3 + $temp;
            }
            $psprs3 = $temp;
            // dpm($psprs3);

            $psrpsalcap3ConfigValue = $config->get('psrpsalcap3');
            $psrpsalcap3 = $psrpsalcap3ConfigValue;
            // dpm($psrpsalcap3);

            // altASRS
            $altASRS = $config->get('altASRS');
            // dpm($altASRS);

            // altpsprs
            $altPSPRS = $config->get('altPSPRS');
            // dpm($altPSPRS);

            $date = $form_state->getValue('date');
            if ($date == 0) {

                $salcapcondition = $form_state->getValue('retplan');
                // $hireddate = $form_state->getValue('hireddate');
                //ASRS
                if ($salcapcondition == 0) {
                    //Hired on/after 7/1/96
                    // if($hireddate == 0){
                    $salcap = 330000;
                    // }
                    // else{
                    //     $salcap = 425000;
                    // }
                }
                //ORP
                else if ($salcapcondition == 1) {
                    // if($hireddate == 0){
                    $salcap = 330000;
                    // }
                    // else{
                    // $salcap = 430000;
                    // }
                } else {


                    //retirement & ltd rates for FY 2015
                    // $ASRS = 0.1204; //cap at 255,000
                    // $ASRSltd = 0.0018;

                    //PSPRS
                    $psprscondition = $form_state->getValue('psprsretplan');

                    if ($psprscondition == 0) {
                        $PSPRS = 0.4558; //cap at 280,000     
                        $salcap = 330000; //cap for retirement rates for FY 2021       
                    }

                    if ($psprscondition == 1) {
                        $PSPRS = 0.4066; //cap at 110,000   
                        $salcap = 115868; //cap for retirement rates for FY 2021                
                    }

                    if ($psprscondition == 2) {
                        $PSPRS = 0.3972 + 0.0141; //cap at 110,000
                        $salcap = 115868; //cap for retirement rates for FY 2021
                    }
                }

                $altASRS = 0.0999;
                $altPSPRS = 0.08;
            }


            if ($date == 1) {
                $salcapcondition = $form_state->getValue('retplan');
                // dpm($salcapcondition);
                // $hireddate = $form_state->getValue('hireddate');

                //ASRS
                if ($salcapcondition == 0) {
                    //Hired on/after 7/1/96
                    // if($hireddate == 0){
                    $salcap = $asrssalcap;
                    // }
                    // else{
                    // $salcap = 425000;
                    // }
                }

                //ORP
                else if ($salcapcondition == 1) {
                    // if($hireddate == 0){
                    $salcap = $orpsalcap;
                    // }
                    // else{
                    // $salcap = 430000;
                    // }
                }

                //PSPRS
                else if ($salcapcondition == 2) {

                    //retirement & ltd rates for FY 2021
                    // $ASRS = 0.1222; //cap at 255,000
                    // $ASRSltd = 0.0019;
                    //PSPRS
                    $psprscondition = $form_state->getValue('psprsretplan');
                    // dpm($psprscondition);
                    if ($psprscondition == 0) {
                        $PSPRS = $psprs1; //cap at 290,000            
                        $salcap = $psrpsalcap1;
                    }

                    if ($psprscondition == 1) {
                        $PSPRS = $psprs2; //cap at 115,868  
                        $salcap = $psrpsalcap2;
                    }

                    if ($psprscondition == 2) {
                        $PSPRS = $psprs3; //cap at 115,868
                        $salcap = $psrpsalcap3;
                    }
                }

                //default values required for all retirements plans
                $altASRS = $altASRS; //cap at 255,000
                $altPSPRS = $altPSPRS; //cap at 255,000
            }

            // drupal_set_message('See your total compensation estimate below');
            \Drupal::service('messenger')->addMessage("See your total compensation estimate below");

            switch ($form_state->getValue('medplan')) {
                case 0:
                    switch ($form_state->getValue('medcov')) {
                        case 0:
                            $form_state->setValue('r1c2', '$' . number_format($EPO1, 2));
                            $emp = $EPO1;
                            break;
                        case 1:
                            $form_state->setValue('r1c2', '$' . number_format($EPO2, 2));
                            $emp = $EPO2;
                            break;
                        case 2:
                            $form_state->setValue('r1c2', '$' . number_format($EPO3, 2));
                            $emp = $EPO3;
                            break;
                        case 3:
                            $form_state->setValue('r1c2', '$' . number_format($EPO4, 2));
                            $emp = $EPO4;
                            break;
                    };
                    break;
                case 1:
                    switch ($form_state->getValue('medcov')) {
                        case 0:
                            $form_state->setValue('r1c2', '$' . number_format($HSA1, 2));
                            $emp = $HSA1;
                            break;
                        case 1:
                            $form_state->setValue('r1c2', '$' . number_format($HSA2, 2));
                            $emp = $HSA2;
                            break;
                        case 2:
                            $form_state->setValue('r1c2', '$' . number_format($HSA3, 2));
                            $emp = $HSA3;
                            break;
                        case 3:
                            $form_state->setValue('r1c2', '$' . number_format($HSA4, 2));
                            $emp = $HSA4;
                            break;
                    };
                    break;
                case 2:
                    $form_state->setValue('r1c2', 0);
                    $emp = 0;
                    break;
            }

            switch ($form_state->getValue('denplan')) {
                case 0:
                    switch ($form_state->getValue('dencov')) {
                        case 0:
                            $form_state->setValue('r2c2', '$' . number_format($dental1, 2));
                            $edp = $dental1;
                            break;
                        case 1:
                            $form_state->setValue('r2c2', '$' . number_format($dental2, 2));
                            $edp = $dental2;
                            break;
                        case 2:
                            $form_state->setValue('r2c2', '$' . number_format($dental3, 2));
                            $edp = $dental3;
                            break;
                        case 3:
                            $form_state->setValue('r2c2', '$' . number_format($dental4, 2));
                            $edp = $dental4;
                            break;
                    };
                    break;
                case 1:
                    $form_state->setValue('r2c2', 0);
                    //$form['r2c2']['#markup'] = 0;
                    $edp = 0;
                    break;
            }

            if ($form_state->getValue('postdocrtw') == 1) {
                $form_state->setValueForElement($form['rtw'], 2);
                $form_state->setValueForElement($form['retplan'], 3);
                //form_set_value($form['rtw'], 2, $form_state);
                //form_set_value($form['retplan'], -1, $form_state);
            }
            if ($form_state->getValue('postdocrtw') == 0) {
                $form_state->setValueForElement($form['rtw'], 2);
                //form_set_value($form['rtw'], 2, $form_state);
            }

            switch ($form_state->getValue('retplan')) { //retplan
                case 0: //ASRS
                    switch ($form_state->getValue('rtw')) { //type of retiree
                        case 1: //PSPRS retiree
                            if ($form_state->getValue('salary') <= $salcap) {
                                // dpm("case 01 if");
                                // dpm($form_state->getValue('salary') * $altPSPRS + $form_state->getValue('salary') * $ASRS);
                                $form_state->setValue('r3c2', '$' . number_format($form_state->getValue('salary') * $altPSPRS + $form_state->getValue('salary') * $ASRS, 2));
                                $form_state->setValue('r7c2', 0);
                                $erp = $form_state->getValue('salary') * $altPSPRS + $form_state->getValue('salary') * $ASRS;
                                $ltd = 0;
                            } else {
                                // dpm("case 01 else");
                                // dpm($salcap * $altPSPRS + $salcap * $ASRS);
                                $form_state->setValue('r3c2', '$' . number_format($salcap * $altPSPRS + $salcap * $ASRS, 2));
                                $form_state->setValue('r7c2', 0);
                                $erp = $salcap * $altPSPRS + $salcap * $ASRS;
                                $ltd = 0;
                            }
                            break;
                        default:
                            if ($form_state->getValue('salary') <= $salcap) {
                                // dpm("case 0 default if1");
                                // dpm($form_state->getValue('salary') * $ASRS);
                                $form_state->setValue('r3c2', '$' . number_format($form_state->getValue('salary') * $ASRS, 2));
                                $erp = $form_state->getValue('salary') * $ASRS;
                            } else {
                                // dpm("case 0 default else1");
                                // dpm($salcap * $ASRS);
                                $form_state->setValue('r3c2', '$' . number_format($salcap * $ASRS, 2));
                                $erp = $salcap * $ASRS;
                            }
                            if ($form_state->getValue('salary') < $ltdcap) {
                                $form_state->setValue('r7c2', '$' . number_format($form_state->getValue('salary') * $ASRSltd, 2));
                                // $form['r7c2']['#markup']='$' . number_format($form_state->getValue('salary')*$ASRSltd,2);
                                $ltd = $form_state->getValue('salary') * $ASRSltd;
                            } else {
                                $form_state->setValue('r7c2', '$' . number_format($ltdcap * $ASRSltd, 2));
                                //$form['r7c2']['#markup']='$' . number_format($ltdcap*$ASRSltd,2);
                                $ltd = $ltdcap * $ASRSltd;
                            }
                            break;
                    }
                    break;
                case 1: //ORP
                    switch ($form_state->getValue('rtw')) { //type of retiree
                        case 0: //ASRS retiree
                            if ($form_state->getValue('salary') <= $salcap) {
                                // dpm("case 10 if1");
                                // dpm($form_state->getValue('salary') * $altASRS + $form_state->getValue('salary') * $ORP);
                                $form_state->setValue('r3c2', '$' . number_format($form_state->getValue('salary') * $altASRS + $form_state->getValue('salary') * $ORP, 2));
                                $form_state->setValue('r7c2', 0);
                                $erp = $form_state->getValue('salary') * $altASRS + $form_state->getValue('salary') * $ORP;
                                $ltd = 0;
                            } else {
                                // dpm("case 10 else1");
                                // dpm($salcap * $ASRS + $salcap * $ORP);
                                $form_state->setValue('r3c2', '$' . number_format($salcap * $ASRS + $salcap * $ORP, 2));
                                $form_state->setValue('r7c2', 0);
                                $erp = $salcap * $ASRS + $salcap * $ORP;
                                $ltd = 0;
                            }
                            break;
                        case 1: //PSPRS retiree
                            if ($form_state->getValue('salary') <= $salcap) {
                                // dpm("case 11 if1");
                                // dpm($form_state->getValue('salary') * $altPSPRS + $form_state->getValue('salary') * $ORP);
                                $form_state->setValue('r3c2', '$' . number_format($form_state->getValue('salary') * $altPSPRS + $form_state->getValue('salary') * $ORP, 2));
                                $form_state->setValue('r7c2', 0);
                                $erp = $form_state->getValue('salary') * $altPSPRS + $form_state->getValue('salary') * $ORP;
                                $ltd = 0;
                            } else {
                                // dpm("case 11 else1");
                                // dpm($salcap * $altPSPRS + $salcap * $ORP);
                                $form_state->setValue('r3c2', number_format($salcap * $altPSPRS + $salcap * $ORP, 2));
                                $form_state->setValue('r7c2', 0);
                                $erp = $salcap * $PSPRS + $salcap * $ORP;
                                $ltd = 0;
                            }
                            break;
                        default: //not a retiree
                            if ($form_state->getValue('salary') <= $salcap) {
                                // dpm("case 1 default if1");
                                // dpm($form_state->getValue('salary') * $ORP);
                                $form_state->setValue('r3c2', '$' . number_format($form_state->getValue('salary') * $ORP, 2));
                                $erp = $form_state->getValue('salary') * $ORP;
                            } else {
                                // dpm("case 1 default else1");
                                // dpm($salcap * $ORP);
                                $form_state->setValue('r3c2', '$' . number_format($salcap * $ORP, 2));
                                $erp = $salcap * $ORP;
                            }
                            if ($form_state->getValue('salary') < $ltdcap) {
                                $form_state->setValue('r7c2', '$' . number_format($form_state->getValue('salary') * $ORPltd, 2));
                                $ltd = $form_state->getValue('salary') * $ORPltd;
                            } else {
                                $form_state->setValue('r7c2', '$' . number_format($ltdcap * 0.0025, 2));
                                $ltd = $ltdcap * 0.0025;
                            }
                            break;
                    }
                    break;
                case 2: //PSPRS
                    if ($form_state->getValue('salary') <= $salcap) {
                        // dpm("case 2 if");
                        // dpm($form_state->getValue('salary') * $PSPRS);
                        $form_state->setValue('r3c2', '$' . number_format($form_state->getValue('salary') * $PSPRS, 2));
                        $form_state->setValue('r7c2', '$' . number_format($form_state->getValue('salary') * $PSPRSltd, 2));
                        $erp = $form_state->getValue('salary') * $PSPRS;
                        $ltd = $form_state->getValue('salary') * $PSPRSltd;
                    } else {
                        // dpm("case 2 else");
                        // dpm($salcap * $PSPRS);
                        $form_state->setValue('r3c2', '$' . number_format($salcap * $PSPRS, 2));
                        $form_state->setValue('r7c2', '$' . number_format($salcap * $PSPRSltd, 2));
                        $erp = $salcap * $PSPRS;
                        $ltd = $salcap * $PSPRSltd;
                    }
                    break;
                case 3: //not enrolled in retirement plan
                    switch ($form_state->getValue('rtw')) {
                        case 0: //ASRS retiree, not enrolled should get ACR
                            if ($form_state->getValue('salary') <= $salcap) {
                                // dpm("case 30 if1");
                                // dpm($form_state->getValue('salary') * $altASRS);
                                $form_state->setValue('r3c2', '$' . number_format($form_state->getValue('salary') * $altASRS, 2));
                                $form_state->setValue('r7c2', 0);
                                $erp = $form_state->getValue('salary') * $altASRS;
                                $ltd = 0;
                            } else {
                                // dpm("case 30 else1");
                                // dpm($salcap * $ASRS);
                                $form_state->setValue('r3c2', '$' . number_format($salcap * $ASRS, 2));
                                $form_state->setValue('r7c2', 0);
                                $erp = $salcap * $ASRS;
                                $ltd = 0;
                            }
                            break;
                        case 1: //PSPRS retiree, not enrolled should get PSPRS ACR
                            if ($form_state->getValue('salary') <= $salcap) {
                                // dpm("case 31 if1");
                                // dpm($form_state->getValue('salary') * $altPSPRS);
                                $form_state->setValue('r3c2', '$' . number_format($form_state->getValue('salary') * $altPSPRS, 2));
                                $form_state->setValue('r7c2', 0);
                                $erp = $form_state->getValue('salary') * $altPSPRS;
                                $ltd = 0;
                            } else {
                                // dpm("case 31 else1");
                                // dpm($salcap * $altPSPRS);
                                $form_state->setValue('r3c2', '$' . number_format($salcap * $altPSPRS, 2));
                                $form_state->setValue('r7c2', 0);
                                $erp = $salcap * $PSPRS;
                                $ltd = 0;
                            }
                            break;
                        case 2: //neither ASRS/PSPRS retiree

                            if ($form_state->getValue('postdocrtw') != 1) {
                                // dpm("case 32 if1");
                                $form_state->setValue('r3c2', 0);
                                $form_state->setValue('r7c2', 0);
                                $erp = 0;
                                $ltd = 0;
                            } else {
                                if ($form_state->getValue('salary') <= $ltdcap) {
                                    // dpm("case 32 else if1");
                                    $form_state->setValue('r3c2', 0);
                                    $form_state->setValue('r7c2', '$' . number_format($form_state->getValue('salary') * $ORPltd, 2));
                                    $erp = 0;
                                    $ltd = $form_state->getValue('salary') * $ORPltd;
                                } else {
                                    // dpm("case 32 else else1");
                                    $form_state->setValue('r3c2', 0);
                                    $form_state->setValue('r7c2', '$' . number_format($ltdcap * $ORPltd, 2));
                                    $erp = 0;
                                    $ltd = $ltdcap * $ORPltd;
                                }
                            }
                            break;
                    }
                    break;
            }


            //social security and medicare
            $form_state->setValue('r5c2', '$' . number_format($form_state->getValue('salary') * $MCR, 2));
            $emc = $form_state->getValue('salary') * $MCR;

            if ($form_state->getValue('salary') >= $sscap) {
                $form_state->setValue('r4c2', '$' . number_format($sscap * $SSR, 2));
                $ess = $sscap * $SSR;
            } else {
                $form_state->setValue('r4c2', '$' . number_format($form_state->getValue('salary') * $SSR, 2));
                $ess = $form_state->getValue('salary') * $SSR;
            }

            //basic life
            if ($form_state->getValue('salary') >= 400000) {
                $form_state->setValue('r6c2', '$' . number_format($basiclife1 + (((385000 * 0.001) * $basiclife2) * 12), 2));
                $ebl = $basiclife1 + (((385000 * 0.001) * $basiclife2) * 12);
            } else {
                $form_state->setValue('r6c2', '$' . number_format($basiclife1 + ((($form_state->getValue('salary') - 15000) * 0.001) * $basiclife2) * 12, 2));
                $ebl = $basiclife1 + ((($form_state->getValue('salary') - 15000) * 0.001) * $basiclife2) * 12;
            }

            $form_state->setValue('r8c2', '$' . number_format($form_state->getValue('salary') * $wcomp, 2));
            //workers comp
            $ewc = $form_state->getValue('salary') * $wcomp;
            $form_state->setValue('sr1c2', '$' . number_format($emp + $edp + $erp + $ltd + $emc + $ess + $ebl + $ewc, 2));
            $form_state->setValue('sr2c2', '$' . number_format($form_state->getValue('salary'), 2));
            // dpm(number_format($form_state->getValue('salary') + $emp + $edp + $erp + $ltd + $emc + $ess + $ebl + $ewc, 2));
            $form_state->setValue('sr3c2', '$' . number_format($form_state->getValue('salary') + $emp + $edp + $erp + $ltd + $emc + $ess + $ebl + $ewc, 2));
            $form_state->setValue('sr4c2', sprintf("%.2f%%", 100 * ($emp + $edp + $erp + $ltd + $emc + $ess + $ebl + $ewc) / $form_state->getValue('salary')));

            //   form_set_error('submit', t('Problem submitting estimator data'));
        }
        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $form_state->setRebuild(true);
    }

    public function compensation_estimator_reset(FormStateInterface $form_state)
    {
        $form_state->setRebuild(false);
    }
}
