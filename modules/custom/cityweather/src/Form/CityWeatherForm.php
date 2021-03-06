<?php

namespace Drupal\cityweather\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Exception\ClientException;

class CityWeatherForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cityweather_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::config('cityweather.config');
    $appid = $config->get('appid');
    $form['appid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your API key:'),
      '#description' => $this->t('Enter API key which you generated from https://openweathermap.org/'),
      '#default_value' => $appid,
      '#required' => true
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $appid = $form_state->getValue('appid');
    if (!strlen($appid)) {
      $form_state->setErrorByName('appid', $this->t('Please enter API key'));
    }
    $client = \Drupal::httpClient();
    try{
      $response=$client->request('GET','api.openweathermap.org/data/2.5/weather?q=Madrid&units=metric&appid='.$appid);
    }
    catch(ClientException $e)
    {
      $form_state->setErrorByName('appid', $this->t('Incorrect API key'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::configfactory()->getEditable('cityweather.config');
    $config
      ->set('appid', $form_state->getValue('appid'))
      ->save();
  }

}
