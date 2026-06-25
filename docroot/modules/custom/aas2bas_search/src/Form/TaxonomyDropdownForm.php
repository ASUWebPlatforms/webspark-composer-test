<?php

namespace Drupal\aas2bas_search\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\File\FileUrlGenerator;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\StringTranslation\StringTranslationTrait;

class TaxonomyDropdownForm extends FormBase {
    use StringTranslationTrait;

    protected $fileUrlGenerator;
    protected $messenger;

    protected $bas_majors;

    public function __construct(FileUrlGenerator $file_url_generator, MessengerInterface $messenger) {
        $this->fileUrlGenerator = $file_url_generator;
        $this->messenger = $messenger;
    }

    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('file_url_generator'),
            $container->get('messenger')
        );
    }

    public function getFormId() {
        return 'aas2bas_search_dropdown_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['#attached']['library'][] = 'aas2bas_search/aas2bas_search_form_styles';
        // $form['#attached']['library'][] = 'aas2bas_search/aas2bas_search_form_scripts';

        // Colleges JSON path and URL
        $colleges_json_path = 'public://aas2bas-json/AAS2BAS-Colleges.json';
        $colleges_json_url = $this->fileUrlGenerator->generateAbsoluteString($colleges_json_path);


        // Get Colleges JSON content
        try {
            $colleges_json_content = file_get_contents($colleges_json_url);
            if ($colleges_json_content === FALSE) {
                throw new \Exception('Failed to get JSON content.');
            }
        } catch (\Exception $e) {
            $this->messenger->addError($this->t('Failed to get Colleges JSON content.'));
            return $form;
        }

        // Decode Colleges JSON content
        $college_data = json_decode($colleges_json_content, TRUE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->messenger->addError($this->t('Failed to decode Colleges JSON content: @error', ['@error' => json_last_error_msg()]));
            return $form;
        }

        // College dropdown options
        $college_options = [];
        foreach ($college_data as $college) {
            $college_options[$college['File']] = $college['College'];
        }

        // College dropdown
        $form['college_dropdown'] = [
            '#type' => 'select',
            '#title' => $this->t('Arizona Community College'),
            '#options' => $college_options,
            '#empty_option' => $this->t('-- Select a College --'),
            '#prefix' => '<div id="college-dropdown" area-live="polite" role="region">',
            '#suffix' => '</div>',
            '#ajax' => [
                'callback' => '::ajaxCallbackDropdowns',
                'wrapper' => 'aas_degree_dropdown',
                'event' => 'change',
                'progress' => [
                    'type' => 'throbber',
                    'message' => $this->t('Loading degrees...'),
                ],
            ],
        ];

        // College text - after selection of college
        $form['college_text'] = [
            '#type' => 'markup',
            '#markup' => '',
            '#prefix' => '<div id="college-text" aria-live="polite" role="status">',
            '#suffix' => '</div>',
            '#states' => [
                'visible' => [
                    ':input[name="college_dropdown"]' => ['!value' => ''],
                ],
            ],
        ];

        // AAS Degree dropdown
        $form['aas_degree_dropdown'] = [
            '#type' => 'select',
            '#title' => $this->t('AAS Degrees'),
            '#options' => ['' => $this->t('-- Select an AAS Degree --')],
            '#empty_option' => $this->t('-- Select an AAS Degree --'),
            '#prefix' => '<div id="aas_degree_dropdown" aria-live="polite" role="region">',
            '#suffix' => '</div>',
            '#validated' => TRUE,
            '#states' => [
                'visible' => [
                    ':input[name="college_dropdown"]' => ['!value' => ''],
                ],
            ],
            '#ajax' => [
                'callback' => '::ajaxCallbackBAS',
                'wrapper' => 'bas_content',
                'event' => 'change',
                'progress' => [
                    'type' => 'throbber',
                    'message' => $this->t('Loading degrees...'),
                ]
            ]
        ];

        // BAS content
        $form['bas_content'] = [
            '#type' => 'markup',
            '#markup' => '<ul></ul>',
            '#prefix' => '<div id="bas-content">',
            '#suffix' => '</div>',
            '#states' => [
                'visible' => [
                    ':input[name="aas_degree_dropdown"]' => ['!value' => ''],
                ],
            ],
        ];

        // ASU Major list
        $form['asu_major_list'] = [
            '#type' => 'markup',
            '#markup' => '',
            '#prefix' => '<div id="asu-major-list">',
            '#suffix' => '</div>',
            '#states' => [
                'visible' => [
                    ':input[name="aas_degree_dropdown"]' => ['!value' => ''],
                ],
            ],
        ];

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $selected_file = $form_state->getValue('college_dropdown');
    }

    public function ajaxCallbackBAS(array &$form, FormStateInterface $form_state) {
        $response = new AjaxResponse();

        $selected_aas = $form_state->getValue('aas_degree_dropdown'); // Get selected AAS

        // BAS Major JSON path and URL
        $bas_major_json_path = 'public://aas2bas-json/AAS2BAS-BASLinks.json';
        $bas_major_json_url = $this->fileUrlGenerator->generateAbsoluteString($bas_major_json_path);

        try {
            $bas_major_json_content = file_get_contents($bas_major_json_url);
            if ($bas_major_json_content === FALSE) {
                throw new \Exception('Failed to get BAS Majors JSON content.');
            }
        } catch (\Exception $e) {
            $this->messenger()->addError($this->t('Failed to get BAS Majors JSON content.'));
            return $form;
        }

        // Decode BAS Majors JSON content - used for ASU Major list
        $bas_major_data = json_decode($bas_major_json_content, TRUE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->messenger()->addError($this->t('Failed to decode BAS Majors JSON content: @error', ['@error' => json_last_error_msg()]));
            return $form;
        }

        try {
            $selected_file = $form_state->getValue('college_dropdown'); // Get selected college
            $college_json_path = "public://aas2bas-json/{$selected_file}.json"; // Get College JSON path
            $college_json_url = $this->fileUrlGenerator->generateAbsoluteString($college_json_path); // Get College JSON URL
         
            // Set BAS content block
            $bas_content = '<div id="bas_content_block" class="spacing-top-24 spacing-bottom-24 block block-layout-builder block-inline-blocktext-content clearfix default">
                <div class="formatted-text">
                    <div class="uds-highlighted-heading">
                        <h3><span class="highlight-black">AAS to BAS for ' . $selected_aas . '</span></h3>
                    </div>
                    <p>ASU degrees you may be interested in.</p>
                </div>
            </div>';

            $form['bas_content']['#markup'] = $bas_content; // Set AAS to BAS text
            $response->addCommand(new ReplaceCommand('#bas-content', $form['bas_content'])); // Replace AAS to BAS text

            // Set ASU Major list block
            try {
                // Get College JSON content
                $college_json_content = file_get_contents($college_json_url);
                if ($college_json_content === FALSE) {
                    throw new \Exception('Failed to get College JSON content.');
                }
            } catch (\Exception $e) {
                // Add error message
                $this->messenger->addError($this->t('Failed to get College JSON content.'));
                return $response;
            }

            // Decode College JSON content
            $aas_data = json_decode($college_json_content, TRUE);
            if ($aas_data === NULL) {
                $this->messenger->addError($this->t('Filed to decode College JSON content.'));
                return $response;
            }

            $bas_major_list = '';
            if ($selected_aas) {
                foreach ($aas_data as $aas) {
                    if (isset($aas['In-State AAS'], $aas['ASU Major']) && $aas['In-State AAS'] === $selected_aas ) {
                        $bas_major_link = '';
                        foreach ($bas_major_data as $bas_link) {
                            if (isset($bas_link['BAS Major'], $bas_link['BAS Major URL']) && $aas['ASU Major'] === $bas_link['BAS Major']) {
                                $bas_major_link = $bas_link['BAS Major URL'];
                                break;
                            }
                        }
                        $bas_major_list .= '<li><a href="' . $bas_major_link . '">' . $this->t($aas['ASU Major']) . '</a></li>';
                    }
                }
            }

            // Set ASU Major list
            $form['asu_major_list']['#markup'] = !empty($bas_major_list) 
                ? '<ul>' . $bas_major_list . '</ul>' 
                : '<strong>No ASU Majors found for this AAS Degree.</strong>';

            // Replace ASU Major list
            $response->addCommand(new ReplaceCommand('#asu-major-list', $form['asu_major_list']));

        } catch (\Exception $e) {
            // Log the error or display a message
            $this->messenger->addError($this->t('An error occurred: @message', ['@message' => $e->getMessage()]));
        }

        return $response;
    }

    public function ajaxCallbackDropdowns(array &$form, FormStateInterface $form_state) {
        $response = new AjaxResponse();

        // Get College JSON content
        try {

            $selected_file = $form_state->getValue('college_dropdown'); // Get selected college
            $college_json_path = "public://aas2bas-json/{$selected_file}.json"; // Get College JSON path
            $college_json_url = $this->fileUrlGenerator->generateAbsoluteString($college_json_path); // Get College JSON URL

            try {
                // Get College JSON content
                $college_json_content = file_get_contents($college_json_url);
                if ($college_json_content === FALSE) {
                    throw new \Exception('Failed to get College JSON content.');
                }
            } catch (\Exception $e) {
                // Add error message
                $this->messenger->addError($this->t('Failed to get College JSON content.'));
                return $response;
            }

            // Decode College JSON content
            $aas_data = json_decode($college_json_content, TRUE);
            if ($aas_data === NULL) {
                $this->messenger->addError($this->t('Filed to decode College JSON content.'));
                return $response;
            }

            // Save AAS data to form state
            $form_state->set('aas_data', $aas_data);

            // AAS Degree dropdown
            $aas_options = [];
            foreach ($aas_data as $aas) {
                // Check if In-State AAS is set
                if (isset($aas['In-State AAS'])) {
                    $aas_options[$aas['In-State AAS']] = $aas['In-State AAS']; // Add In-State AAS to options
                }
            }

            // Set AAS Degree dropdown options
            $form['aas_degree_dropdown']['#options'] = !empty($aas_options) 
                ? ['' => $this->t('-- Select an AAS Degree --')] + $aas_options 
                : ['' => $this->t('No AAS Degrees Available')]; // Set AAS Degree dropdown options

            // College text
            $selected_college = $this->t($form['college_dropdown']['#options'][$selected_file]); // Get College text
            $aas2bas_content = '
            <div class="spacing-top-24 spacing-bottom-24 block block-layout-builder block-inline-blocktext-content clearfix default">
                <div class="formatted-text">
                    <div class="uds-highlighted-heading">
                        <h3><span class="highlight-black">AAS to BAS for ' . $selected_college . '</span></h3>
                        <p>Bachelor of Applied Science (BAS) degrees are designed specifically for students who 
                        have earned an Associate of Applied Science from a regionally accredited institution.</p>
                        <p>Students who have earned an Associate of Applied Science from a regionally accredited 
                        institution may transfer 60 credit hours toward the Bachelor of Applied Science degree. 
                        Students who have earned an Associate of Applied Science degree from a regionally accredited 
                        Arizona community college may be eligible to transfer up to 75 credits toward the Bachelor 
                        of Applied Science degree. Students pursuing the 75 credit option may have more than 45 
                        credit hours to complete their BAS upon transfer to ASU. Students should work with their 
                        academic advisor to ensure their courses will meet degree requirements.</p>
                        <p>Please select the AAS degree that you are currently earning at your community college.</p>
                    </div>
                </div>
            </div>';

            $form['college_text']['#markup'] = !empty($selected_college) ? $aas2bas_content : ''; // Set College text

            // Reset AAS to BAS text and ASU Major list when College dropdown changes
            $form['bas_content']['#markup'] = ''; // Reset AAS to BAS text
            $form['asu_major_list']['#markup'] = ''; // Reset ASU Major list
            $response->addCommand(new ReplaceCommand('#bas-content', $form['bas_content'])); // Replace AAS to BAS text
            $response->addCommand(new ReplaceCommand('#asu-major-list', $form['asu_major_list'])); // Replace ASU Major list

            $response->addCommand(new ReplaceCommand('#aas_degree_dropdown', $form['aas_degree_dropdown'])); // Replace AAS Degree dropdown
            $response->addCommand(new ReplaceCommand('#college-text', $form['college_text'])); // Replace College text
                
        } catch (\Exception $e) {
            // Log the error or display a message
            $this->messenger->addError($this->t('An error occurred: @message', ['@message' => $e->getMessage()]));
        }

        return $response;
    }
}