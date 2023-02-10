<?php

namespace Drupal\pdf_merger\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * PDFTK config settings
 *
 */
class PdfMergerSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['pdf_merger.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pdf_merger_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('pdf_merger.settings');

    $form['exec'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('PDFTK Binary File Path'),
      '#description' => $this->t('This module requires that you install Pdftk on your server and enter the path to the executable.'),
      '#default_value' => $config->get('exec'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    // Check that pdftk exists.
    if (!file_exists($form_state->getValue('exec'))) {
      $form_state->setError($form['exec'], $this->t('The pdftk binary was not found at the location given.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('pdf_merger.settings')
        ->set('exec', $values['exec'])
        ->save();

    parent::submitForm($form, $form_state);
  }

}
