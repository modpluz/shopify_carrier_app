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

    public function beforeFilter() {
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

        if (isset($this->request->data['rate']) && isset($this->request->data['rate']['destination'])){
            $carrier_services = $this->_carrierRates($this->request->data['rate']['destination']['postal_code']);

            if(count($carrier_services)){
                $rates = array();
                foreach($carrier_services as $idx=>$rate){
                    $rates[$idx]['service_name'] = $rate['CarrierService']['name'];
                    $rates[$idx]['service_code'] = Inflector::slug(strtolower($rate['CarrierService']['name']), '-');
                    $rates[$idx]['total_price'] = $rate['ShippingRate']['rate'];
                    $rates[$idx]['currency'] = $this->request->data['rate']['currency'];
                    $rates[$idx]['min_delivery_date'] = date('Y-m-d H:i:s');
                    $rates[$idx]['max_delivery_date'] = date('Y-m-d H:i:s');
                }

                return json_encode(array('rates' => $rates));
            } else {
                return json_encode(array('error' => array('code' => 400, 'msg' =>'There are no valid rates found for the supplied address!')));
            }
        } else {
            return  json_encode(array('error' => array('code' => 500, 'msg' =>'Please provide a valid postal code!')));
        }
    }

    public function create($id = null){
        require_once '../../vendor/autoload.php';

        $this->loadModel('CarrierService');
        $find_what = 'all';
        $options = array('conditions' => 'active_yn = 1');

        if(!is_null($id) && (int) $id > 0){
            $find_what = 'first';
            $options['conditions'] .= ' AND id = \''.$id.'\'';
        }
        $carrier_services = $this->CarrierService->find($find_what, $options);
        if(count($carrier_services)){
            $carriers = array();
            $count = 0;
            foreach($carrier_services as $carrier){
                $carrier_name = (isset($carrier['CarrierService'])) ? $carrier['CarrierService']['name'] : $carrier['name'];
                $callback_url = (isset($carrier['CarrierService'])) ? $carrier['CarrierService']['callback_url'] : $carrier['callback_url'];

                $carriers[$count]['name'] = $carrier_name;
                $carriers[$count]['callback_url'] = Configure::read('app.url').$callback_url;
                $carriers[$count]['format'] = 'json';
                $carriers[$count]['service_discovery'] = true;

                $count++;
            }

            //send create requests over to Shopify
            foreach($carriers as $carrier){
                $json_payload = json_encode(array('carrier_service' => $carrier));

                $client = new Client();

                try {
//                    $response = $request->send();
//                    $request = $client->post('https://uafrica4.myshopify.com/admin/carrier_services',
//                                    array('Accept' => 'application/json',
//                                            'X-Shopify-Access-Token' => 'sdfdsf',
//                                            'Content-Type' => 'application/json'), array());
                    $response = $client->post('https://uafrica4.myshopify.com/admin/carrier_services', [
                        'headers' => ['Accept' => 'application/json',
                                      'X-Shopify-Access-Token' => '8ecbdabcea92821e42437e5d42d98ea1',
                                      'Content-Type' => 'application/json'
                                     ],
                        'body'    => $json_payload]);
//                    $request->setHeader('Accept', 'application/json');
//                    $request->setHeader('X-Shopify-Access-Token', '');
//                    $request->setHeader('Content-Type', 'application/json');
//                    $request->setBody($json_payload);
//                    $response = $client->send($request);


//                    $response = $request->json();

//                    $resp = $response->getBody();


                    debug(('here'));
                    exit;


                    } catch (GuzzleHttp\Exception\BadResponseException $e) {
                        debug($e);
                    }

//                debug($client);

                $client = null;
                exit;
            }
        }


    }

    private function _carrierRates($postal_code = null) {
        $this->loadModel('CarrierService');
        $options['joins'] = array(
            array('table' => 'carrier_services_postal_codes',
                'alias' => 'CarrierServicesPostalCode',
                'type' => 'INNER',
                'conditions' => array(
                    'CarrierServicesPostalCode.carrier_service_id = CarrierService.id',
                )
            ), array('table' => 'postal_codes',
                'alias' => 'PostalCode',
                'type' => 'INNER',
                'conditions' => array(
                    'PostalCode.id = CarrierServicesPostalCode.postal_code_id',
                )
            ), array('table' => 'postal_codes_shipping_rates',
                'alias' => 'PostalCodesShippingRate',
                'type' => 'INNER',
                'conditions' => array(
                    'PostalCodesShippingRate.postal_code_id = CarrierServicesPostalCode.postal_code_id',
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
            'CarrierServicesPostalCode.postal_code_id',
            'CarrierServicesPostalCode.carrier_service_id'
        );
        $options['fields'] = array(
            'CarrierService.id', 'CarrierService.name',
            'CarrierService.active_yn', 'CarrierService.callback_url',
            'ShippingRate.rate');
        if(!is_null($postal_code)) $options['conditions'] = "PostalCode.code = '".$postal_code."'";

        $this->CarrierService->recursive = FALSE;

        return $this->CarrierService->find('all', $options);
    }

}
