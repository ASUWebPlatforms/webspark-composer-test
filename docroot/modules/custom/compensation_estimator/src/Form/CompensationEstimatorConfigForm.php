<?php

namespace Drupal\compensation_estimator\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class CompensationEstimatorConfigForm extends ConfigFormBase
{
    protected function getEditableConfigNames()
    {
        return [
            'compensation_estimator.settings',
        ];
    }

    public function getFormId()
    {
        return 'compensation_estimator_config_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('compensation_estimator.settings');

        //Intro text
        $form['introconfig'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('Introduction'),
            '#default_value' => $config->get('introconfig'),
        );

        //title of Fifth Select list Configuration
        $form['postdocrtwConfigtitle'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('post-doctoral scholar or return-to-work retiree Select list Title'),
            '#default_value' => $config->get('postdocrtwConfigtitle'),
        );


        //Fifth Select list Configuration
        $form['postdocrtwConfig'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('post-doctoral scholar or return-to-work retiree Select list'),
            '#default_value' => $config->get('postdocrtwConfig'),
        );

        //title of Sixth Select list Configuration
        $form['rtwconfigtitle'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('Retirement Plan Select list (Which of the following applies to you?) Title'),
            '#default_value' => $config->get('rtwconfigtitle'),
        );

        //Sixth Select list Configuration
        $form['rtwconfig'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('Retirement Plan Select list (Which of the following applies to you?)'),
            '#default_value' => $config->get('rtwconfig'),
        );

        //title of Seventh Select list Configuration
        $form['retplanconfigtitle'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('Retirement Plan Second Select list (In which retirement plan are you enrolled?) Title'),
            '#default_value' => $config->get('retplanconfigtitle'),
        );

        //Seventh Select list Configuration
        $form['retplanconfig'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('Retirement Plan Second Select list (In which retirement plan are you enrolled?)'),
            '#default_value' => $config->get('retplanconfig'),
        );

        //title of Eighth Select list Configuration
        $form['psprsconfigtitle'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('PSPRS Select List Title'),
            '#default_value' => $config->get('psprsconfigtitle'),
        );

        //Eighth Select list Configuration
        $form['psprsconfig'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('PSPRS Select List'),
            '#default_value' => $config->get('psprsconfig'),
        );

        //title of First Select list Configuration
        $form['medplanconfigtitle'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('Medical Plan Select list Title'),
            '#default_value' => $config->get('medplanconfigtitle'),
        );
        //First Select list Configuration
        $form['medplanconfig'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('Medical Plan Select list'),
            '#default_value' => $config->get('medplanconfig'),
        );

        //title of Second Select list Configuration
        $form['medcovconfigtitle'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('Medical Plan Coverage Select list Title'),
            '#default_value' => $config->get('medcovconfigtitle'),
        );

        //Second Select list Configuration
        $form['medcovconfig'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('Medical Plan Coverage Select list'),
            '#default_value' => $config->get('medcovconfig'),
        );

        //title of Third Select list Configuration
        $form['denplanconfigtitle'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('Dental Plan Select list Title'),
            '#default_value' => $config->get('denplanconfigtitle'),
        );

        //Third Select list Configuration
        $form['denplanconfig'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('Dental Plan Select list'),
            '#default_value' => $config->get('denplanconfig'),
        );

        //title of Fourth Select list Configuration
        $form['dencovconfigtitle'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('Dental Plan Coverage Select list Title'),
            '#default_value' => $config->get('dencovconfigtitle'),
        );

        //Fourth Select list Configuration
        $form['dencovconfig'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('Dental Plan Coverage Select list'),
            '#default_value' => $config->get('dencovconfig'),
        );

        $form['salarydescriptionconfig'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('Salary description'),
            '#default_value' => $config->get('salarydescriptionconfig'),
        );


        //ep01 Configuration
        $form['ep01config'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('EP01'),
            '#default_value' => $config->get('ep01config'),
        );

        //ep02 Configuration
        $form['ep02config'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('EP02'),
            '#default_value' => $config->get('ep02config'),
        );

        //ep03 Configuration
        $form['ep03config'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('EP03'),
            '#default_value' => $config->get('ep03config'),
        );

        //ep04 Configuration
        $form['ep04config'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('EP04'),
            '#default_value' => $config->get('ep04config'),
        );

        //HSA1 Configuration
        $form['hsa1config'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('HSA1'),
            '#default_value' => $config->get('hsa1config'),
        );

        //HSA2 Configuration
        $form['hsa2config'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('HSA2'),
            '#default_value' => $config->get('hsa2config'),
        );

        //HSA3 Configuration
        $form['hsa3config'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('HSA3'),
            '#default_value' => $config->get('hsa3config'),
        );

        //HSA4 Configuration
        $form['hsa4config'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('HSA4'),
            '#default_value' => $config->get('hsa4config'),
        );

        //Dental1 Configuration
        $form['dental1config'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('Dental1'),
            '#default_value' => $config->get('dental1config'),
        );

        //Dental2 Configuration
        $form['dental2config'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('Dental2'),
            '#default_value' => $config->get('dental2config'),
        );

        //Dental3 Configuration
        $form['dental3config'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('Dental3'),
            '#default_value' => $config->get('dental3config'),
        );

        //Dental4 Configuration
        $form['dental4config'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('Dental4'),
            '#default_value' => $config->get('dental4config'),
        );

        //psprsltd Configuration
        $form['asrsconfig'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('ASRS'),
            '#default_value' => $config->get('asrsconfig'),
        );

        $form['asrssalcap'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('ASRS - Compensation'),
            '#default_value' => $config->get('asrssalcap'),
        );

        //psprsltd Configuration
        $form['asrsltdconfig'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('ASRSLtd'),
            '#default_value' => $config->get('asrsltdconfig'),
        );

        $form['psprs1'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('PSPRS (if Ben Plan=RET5, RET5_B, RET2)'),
            '#default_value' => $config->get('psprs1'),
        );

        $form['psrpsalcap1'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('PSPRS (if Ben Plan=RET5, RET5_B, RET2) - Compensation'),
            '#default_value' => $config->get('psrpsalcap1'),
        );

        $form['psprs2'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('PSPRS (if Ben Plan=RET3DB)'),
            '#default_value' => $config->get('psprs2'),
        );

        $form['psrpsalcap2'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('PSPRS (if Ben Plan=RET3DB) - Compensation'),
            '#default_value' => $config->get('psrpsalcap2'),
        );

        $form['psprs3'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('PSPRS (if Ben Plans=RET3DC and DIS_FD)'),
            '#default_value' => $config->get('psprs3'),
        );

        $form['psrpsalcap3'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('PSPRS (if Ben Plans=RET3DC and DIS_FD) - Compensation'),
            '#default_value' => $config->get('psrpsalcap3'),
        );

        //psprsltd Configuration
        $form['psprsltdconfig'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('PSPRSLtd'),
            '#default_value' => $config->get('psprsltdconfig'),
        );

        $form['altASRS'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('altASRS'),
            '#default_value' => $config->get('altASRS'),
        );
        $form['altPSPRS'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('alt PSPRS'),
            '#default_value' => $config->get('altPSPRS'),
        );

        //orp Configuration
        $form['orpconfig'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('ORP'),
            '#default_value' => $config->get('orpconfig'),
        );
        $form['orpsalcap'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('ORP - Compensation'),
            '#default_value' => $config->get('orpsalcap'),
        );

        //orpltdconfig Configuration
        $form['orpltdconfig'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('ORP Ltd'),
            '#default_value' => $config->get('orpltdconfig'),
        );




        //ssrconfig Configuration
        $form['ssrconfig'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('SSR'),
            '#default_value' => $config->get('ssrconfig'),
        );

        //mcrconfig Configuration
        $form['mcrconfig'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('MCR'),
            '#default_value' => $config->get('mcrconfig'),
        );

        //basiclife1 Configuration
        $form['basiclife1config'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('basiclife1'),
            '#default_value' => $config->get('basiclife1config'),
        );

        //basiclife2 Configuration
        $form['basiclife2config'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('basiclife2'),
            '#default_value' => $config->get('basiclife2config'),
        );

        //wcomp Configuration
        $form['wcompconfig'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('wcomp'),
            '#default_value' => $config->get('wcompconfig'),
        );

        //ltdcap Configuration
        $form['ltdcapconfig'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('ltdcap'),
            '#default_value' => $config->get('ltdcapconfig'),
        );

        //sscap Configuration
        $form['sscapconfig'] = array(
            '#type' => 'number',
            '#step' => '.0001',
            '#title' => $this->t('sscap'),
            '#default_value' => $config->get('sscapconfig'),
        );
        // salcal confirguartion





        $form['otherbenconfig'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('Other Benefits'),
            '#default_value' => $config->get('otherbenconfig'),
        );

        $form['disclaimerconfig'] = array(
            '#type' => 'textarea',
            '#title' => $this->t('Disclaimer'),
            '#default_value' => $config->get('disclaimerconfig'),
        );

        return parent::buildForm($form, $form_state);
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        parent::validateForm($form, $form_state);
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        parent::submitForm($form, $form_state);
        $this->config('compensation_estimator.settings')
            ->set('medplanconfigtitle', $form_state->getValue('medplanconfigtitle'))
            ->set('medplanconfig', $form_state->getValue('medplanconfig'))
            ->set('medcovconfigtitle', $form_state->getValue('medcovconfigtitle'))
            ->set('medcovconfig', $form_state->getValue('medcovconfig'))
            ->set('denplanconfigtitle', $form_state->getValue('denplanconfigtitle'))
            ->set('denplanconfig', $form_state->getValue('denplanconfig'))
            ->set('dencovconfigtitle', $form_state->getValue('dencovconfigtitle'))
            ->set('dencovconfig', $form_state->getValue('dencovconfig'))
            ->set('postdocrtwConfigtitle', $form_state->getValue('postdocrtwConfigtitle'))
            ->set('postdocrtwConfig', $form_state->getValue('postdocrtwConfig'))
            ->set('rtwconfigtitle', $form_state->getValue('rtwconfigtitle'))
            ->set('rtwconfig', $form_state->getValue('rtwconfig'))
            ->set('retplanconfigtitle', $form_state->getValue('retplanconfigtitle'))
            ->set('retplanconfig', $form_state->getValue('retplanconfig'))
            ->set('psprsconfigtitle', $form_state->getValue('psprsconfigtitle'))
            ->set('psprsconfig', $form_state->getValue('psprsconfig'))
            ->set('salarydescriptionconfig', $form_state->getValue('salarydescriptionconfig'))
            ->set('ep01config', $form_state->getValue('ep01config'))
            ->set('ep02config', $form_state->getValue('ep02config'))
            ->set('ep03config', $form_state->getValue('ep03config'))
            ->set('ep04config', $form_state->getValue('ep04config'))
            ->set('hsa1config', $form_state->getValue('hsa1config'))
            ->set('hsa2config', $form_state->getValue('hsa2config'))
            ->set('hsa3config', $form_state->getValue('hsa3config'))
            ->set('hsa4config', $form_state->getValue('hsa4config'))
            ->set('dental1config', $form_state->getValue('dental1config'))
            ->set('dental2config', $form_state->getValue('dental2config'))
            ->set('dental3config', $form_state->getValue('dental3config'))
            ->set('dental4config', $form_state->getValue('dental4config'))
            ->set('asrsconfig', $form_state->getValue('asrsconfig'))
            ->set('asrsltdconfig', $form_state->getValue('asrsltdconfig'))
            ->set('psprsltdconfig', $form_state->getValue('psprsltdconfig'))
            ->set('orpconfig', $form_state->getValue('orpconfig'))
            ->set('orpltdconfig', $form_state->getValue('orpltdconfig'))
            ->set('ssrconfig', $form_state->getValue('ssrconfig'))
            ->set('mcrconfig', $form_state->getValue('mcrconfig'))
            ->set('basiclife1config', $form_state->getValue('basiclife1config'))
            ->set('basiclife2config', $form_state->getValue('basiclife2config'))
            ->set('wcompconfig', $form_state->getValue('wcompconfig'))
            ->set('ltdcapconfig', $form_state->getValue('ltdcapconfig'))
            ->set('sscapconfig', $form_state->getValue('sscapconfig'))
            ->set('disclaimerconfig', $form_state->getValue('disclaimerconfig'))
            ->set('otherbenconfig', $form_state->getValue('otherbenconfig'))
            ->set('introconfig', $form_state->getValue('introconfig'))
            ->set('asrssalcap', $form_state->getValue('asrssalcap'))
            ->set('orpsalcap', $form_state->getValue('orpsalcap'))
            ->set('psprs1', $form_state->getValue('psprs1'))
            ->set('psrpsalcap1', $form_state->getValue('psrpsalcap1'))
            ->set('psprs2', $form_state->getValue('psprs2'))
            ->set('psrpsalcap2', $form_state->getValue('psrpsalcap2'))
            ->set('psprs3', $form_state->getValue('psprs3'))
            ->set('psrpsalcap3', $form_state->getValue('psrpsalcap3'))
            ->set('altASRS', $form_state->getValue('altASRS'))
            ->set('altPSPRS', $form_state->getValue('altPSPRS'))
            ->save();
    }
}
