<?php
/**
 * Created by PhpStorm.
 * User: remmy
 * Date: 2015/01/07
 * Time: 11:14 PM
 */


App::uses('Controller', 'Controller');
use GuzzleHttp\Client;

require '../../vendor/autoload.php';

//use Guzzle\Http\Client;

/**
 * API Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 */
class CarriersController extends Controller
{
    public $helpers = array('Session');
    public $components = array(/*'DebugKit.Toolbar',*/
        'Session', 'RequestHandler');

    public function beforeFilter()
    {
        /*if ($this->RequestHandler->accepts('json')) {
            // Execute code only if client accepts an HTML (text/html)
            // response
        }*/
    }

    public function index()
    {
        $this->redirect('/');
    }

    public function rates()
    {
        $this->autoRender = false;
        $this->response->type('json');

        $data = json_encode($this->request->data['rate']['destination']);
        file_put_contents('files/rates_response.json', $data);

        if (isset($this->request->data['rate']) && isset($this->request->data['rate']['destination'])) {
            $method_rates = $this->_shippingMethodRates($this->request->data['rate']['destination']['postal_code']);

            if (count($method_rates)) {
                $rates = array();
                foreach ($method_rates as $idx => $rate) {
                    $rates[$idx]['service_name'] = $rate['ShippingMethod']['name'];
                    $rates[$idx]['service_code'] = Inflector::slug(strtolower($rate['ShippingMethod']['name']), '-');
                    $rates[$idx]['total_price'] = $rate['ShippingRate']['rate'];
                    $rates[$idx]['currency'] = $this->request->data['rate']['currency'];
                    $rates[$idx]['min_delivery_date'] = date('Y-m-d H:i:s');
                    $rates[$idx]['max_delivery_date'] = date('Y-m-d H:i:s', strtotime(date('Y-m-d').' +4 days'));
                }

//                file_put_contents('files/rates_response.json', json_encode(array('rates' => $rates)));
                return json_encode(array('rates' => $rates));
            } else {
                return json_encode(array('error' => array('code' => 400, 'msg' => 'There are no valid rates found for the supplied address!')));
            }
        } else {
            return json_encode(array('error' => array('code' => 500, 'msg' => 'Please provide a valid postal code!')));
        }

    }

    private function _shippingMethodRates($postal_code = null)
    {
        $this->loadModel('ShippingMethod');
        $options['joins'] = array(
            array('table' => 'shipping_methods_postal_codes',
                'alias' => 'ShippingMethodsPostalCode',
                'type' => 'INNER',
                'conditions' => array(
                    'ShippingMethodsPostalCode.shipping_method_id = ShippingMethod.id',
                )
            ), array('table' => 'postal_codes',
                'alias' => 'PostalCode',
                'type' => 'INNER',
                'conditions' => array(
                    'PostalCode.id = ShippingMethodsPostalCode.postal_code_id',
                )
            ), array('table' => 'postal_codes_shipping_rates',
                'alias' => 'PostalCodesShippingRate',
                'type' => 'INNER',
                'conditions' => array(
                    'PostalCodesShippingRate.postal_code_id = ShippingMethodsPostalCode.postal_code_id',
                )
            ), array('table' => 'shipping_rates',
                'alias' => 'ShippingRate',
                'type' => 'INNER',
                'conditions' => array(
                    'ShippingRate.id = PostalCodesShippingRate.rate_id',
                )
            )
        );
        $options['group'] = array(
            'ShippingMethodsPostalCode.postal_code_id',
            'ShippingMethodsPostalCode.shipping_method_id'
        );
        $options['fields'] = array(
            'ShippingMethod.id', 'ShippingMethod.name','ShippingRate.rate');
        if (!is_null($postal_code)) $options['conditions'] = "PostalCode.code = '" . $postal_code . "'";

        $this->ShippingMethod->recursive = FALSE;

        return $this->ShippingMethod->find('all', $options);
    }

}
