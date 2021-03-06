<?php
namespace Drupal\cityweather\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Exception\ClientException;
/**
 * Provides a 'cityweather' block.
 *
 * @Block(
 *   id = "cityweather_block",
 *   admin_label = @Translation("City Weather block"),
 * )
 */
class CityWeatherBlock extends BlockBase implements BlockPluginInterface {
  /**
  * {@inheritdoc}
  */
  public function build() {
    $config = \Drupal::config('cityweather.config');
    $appid = $config->get('appid');
    $get_currunt_node = \Drupal::routeMatch()->getParameter('node');
	$type_name = $get_currunt_node->bundle();
	
	//Check content type and get city name
	if($get_currunt_node && $type_name == 'city') {
		$city_name = $get_currunt_node->getTitle();
    } else {
		//Hardcoded City name so API won't break if block place on other node
		$city_name = 'Madrid';
	}
    if($appid=='')
    {
      return array(
        '#theme' => 'markup',
        '#markup' => $this->t("API key isn't setted. Set it in admin/config/system/weather")
      );
    }
    $client = \Drupal::httpClient();
    try{
      $response=$client->request('GET','api.openweathermap.org/data/2.5/weather?q='.$city_name.'&appid='.$appid);
      $response_json = $response->getBody()->getContents();
      $data = json_decode($response_json);
    }
    catch(ClientException $e)
    {
      $response = $e->getResponse();
      switch ($response->getStatusCode()) {
        case 401:
          return array(
            '#markup' => $this->t("Incorrect API key. Set it in admin/config/system/weather")
          );
          break;
        case 404:
          return array(
            '#markup' => $this->t('Incorrect city name. Set it in block settings')
          );
          break;
        default:
          return array(
            '#markup' => $this->t('OpenWeatherMap returned:').$response->getStatusCode().":".$response->getReasonPhrase()
          );
          break;
      }
    }
    return array(
      '#theme' => 'cityweather',
      '#cityweather'=>$data
    );
  }
  
   public function getCacheMaxAge() {
    return 0;
  }  
}
