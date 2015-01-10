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

/**
 * API Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 */
class ApiController extends Controller
{
    public $helpers = array('Session');
    public $components = array(/*'DebugKit.Toolbar',*/
        'Session');

    public function index()
    {
        if ($this->request->is('get') && isset($this->request->query['shop'])) {
            $this->redirect('https://' . $this->request->query['shop'] . '/admin/oauth/authorize?client_id='.Configure::read('shopify.key').'&scope=read_shipping,write_shipping&redirect_uri=http://devtest01.uafrica.com');
        }
        die('Please specify a valid shop!');
    }

    public function install()
    {
        if ($this->request->is('get')) {
            if (isset($this->request->query['code']) && isset($this->request->query['shop'])) {
                //Request Access Token
                $client = new Client();
                try {
                    $response = $client->post('https://'.$this->request->query['shop'].'/admin/oauth/access_token', [
                        'body'    => [
                            'client_id' => Configure::read('shopify.key'),
                            'client_secret' => Configure::read('shopify.secret'),
                            'code' => $this->request->query['code'],
                        ]
                    ]);


                    if($response->json()){
                        $resp = $response->json();

                        $save_data = array(
                            'code' => $this->request->query['code'],
                            'hmac' => $this->request->query['hmac'],
                            'signature' => $this->request->query['signature'],
                            'access_token' => $resp['access_token']);

                        //do we already have API record for this shop?
                        $shop = $this->Api->find('first', array('conditions' => 'shop = \''.$this->request->query['shop'].'\''));
                        if(count($shop)) {
                            $save_data['Api']['id'] = $shop['id'];
                            $save_data['modified'] = date('Y-m-d H:i:s');
                        } else {
                            //instantiate model
                            $this->Api->create();
                            $save_data['shop'] = $this->request->query['shop'];
                            $save_data['created'] = date('Y-m-d H:i:s');
                            $save_data['modified'] = date('Y-m-d H:i:s');
                        }


                        //insert/update record
                        if ($this->Api->save($save_data)){
                            $this->redirect('https://' . $this->request->query['shop'] . '/admin/apps');
                        } else {
                            return json_encode(array('error' => array('code' => 500, 'msg' =>'An internal error occurred')));
                        }

                    }
                } catch (GuzzleHttp\Exception\BadResponseException $e) {
                    return json_encode(array('error' => array('code' => $e->getCode(), 'msg' =>'Service replied with error: '.$e->getMessage())));
                }

                exit;
            }
        }
        die('Installation failed!');
    }


    public function fake()
    {
        // require the Faker autoloader
        require_once '../../vendor/fzaninotto/faker/src/autoload.php';
        $faker = Faker\Factory::create();


        $this->loadModel('CarrierService');
        $this->loadModel('PostalCode');
        $this->loadModel('ShippingRate');
        $this->loadModel('CarrierServicesPostalCode');
        $this->loadModel('PostalCodesShippingRate');

        $db = ConnectionManager::getDataSource('default');
        //TRUNCATE TABLES
        $db->rawQuery("TRUNCATE carrier_services_postal_codes;");
        $db->rawQuery("TRUNCATE postal_codes_shipping_rates;");
        $db->rawQuery("TRUNCATE carrier_services;");
        $db->rawQuery("TRUNCATE postal_codes;");
        $db->rawQuery("TRUNCATE shipping_rates;");


        $carriers = array('imerGX - Day', 'imerGX - Overnight', 'imerFX - Day', 'imerFX - Overnight');
        for ($i = 0; $i <= 20; $i++) {
            $this->PostalCode->create();
            $this->ShippingRate->create();
            //populate post codes
            $this->PostalCode->save(
                array('code' => '00' . $faker->numberBetween(80, 90)));
            //populate shipping rates
            $this->ShippingRate->save(
                array('rate' => '00' . $faker->randomFloat(1, 120, 500)));
        }

        //let's insert random relationships for these records
        $postalCodes = $this->PostalCode->find('list', array('fields' => array('id')));
        $shippingRates = $this->ShippingRate->find('all', array('fields' => array('id')));
//        if(count($postalCodes)){
//            sort($postalCodes);
//        }
        for ($i = 0; $i <= 10; $i++) {
            $code_id = rand(array_keys($postalCodes, min($postalCodes))[0], array_keys($postalCodes, max($postalCodes))[0]);
            $rate_id = rand(array_keys($shippingRates, min($shippingRates))[0], array_keys($shippingRates, max($shippingRates))[0]);
            if ($code_id > 0 && $rate_id > 0) {
                $this->PostalCodesShippingRate->create();
                //$this->PostalCodesShippingRate->save(array('postal_code_id' => $code_id, 'rate_id' => $rate_id));
                $db->rawQuery("INSERT IGNORE INTO postal_codes_shipping_rates(postal_code_id, rate_id) VALUES ('" . (int)$code_id . "', '" . (int)$rate_id . "')");
            }
        }

        foreach ($carriers as $carrier) {
            $this->CarrierService->create();
            if ($this->CarrierService->save(
                array('name' => $carrier,
                    'callback_url' => 'carriers/rates.json'))
            ) {

                if ($new_carrier = $this->CarrierService->find('list', array('conditions' => array('name' => $carrier), 'fields' => array('id')))) {
                    //create random relationships between carrier and post codes
                    for ($i = 0; $i <= 10; $i++) {
                        $code_id = rand(array_keys($postalCodes, min($postalCodes))[0], array_keys($postalCodes, max($postalCodes))[0]);
                        if ($code_id) {
                            //lets make sure this code has a valid rate in the db
//                            $postalCodeRate = $db->query("SELECT rate_id FROM postal_codes_shipping_rates WHERE postal_code_id='".$code_id."' LIMIT 1");
                            if ($postalCodeRate = $this->PostalCodesShippingRate->find('first', array('fields' => array('rate_id'), 'conditions' => array('postal_code_id' => $code_id)))) {
                                $this->CarrierServicesPostalCode->create();
                                /*if($this->CarrierServicesPostalCode->isUnique()){
                                    $this->CarrierServicesPostalCode->save(array('postal_code_id' => $code_id, 'carrier_service_id' => reset($new_carrier)));
                                }*/
                                $db->rawQuery("INSERT IGNORE INTO carrier_services_postal_codes(postal_code_id, carrier_service_id) VALUES ('" . (int)$code_id . "', '" . (int)reset($new_carrier) . "')");
                            } else {
                                //if not, decrement i
                                $i--;
                            }
                        }
                    }
                }
            }
        }
        die('Database fake data population completed!');
    }

    public function test_has()
    {
        $this->loadModel('CarrierService');
        $this->CarrierService->create();
        /*$this->CarrierService->bindModel(
            array('hasMany' => array(
                'CarrierServicesPostalCode',

                'hasAndBelongsToMany' => array(
                    'PostalCode' => array(
                        'className' => 'PostalCode',
                        'foreignKey' => 'id',
                        'joinTable' => 'carrier_services_postal_codes',
                        'foreignKey' => 'carrier_service_id',
                        'unique' => true,
                        'with' => 'CarrierServicesPostalCode',
                        'conditions' => "CarrierServicesPostalCode.postal_code_id=PostalCode.id AND PostalCode.code='0083'",




                    ),
                    'ShippingRates' =>
                        array(
                            'className' => 'ShippingRates',
                            'joinTable' => 'postal_codes_shipping_rates',
                            'foreignKey' => 'rate_id',
                            'associationForeignKey' => 'postal_code_id',
                            'unique' => true,
                            'conditions' => 'PostalCodesShippingRates.postal_code_id=12',
                            'fields' => '',
                            'order' => '',
                            'limit' => '',
                            'offset' => '',
                            //'finderQuery' => "postal_codes.code = '0083'",
                            'with' => 'PostalCodesShippingRates'
                        ))
            ));*/

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
        $options['conditions'] = "PostalCode.code = '0083'";
        $this->CarrierService->recursive = FALSE;

        $carrier_services = $this->CarrierService->find('all', $options);
        /*foreach($carrier_services as $carrier_service){
            debug($carrier_service->postal_code_id);
            exit;
        }*/
        pr($carrier_services);
        exit;
    }
}
