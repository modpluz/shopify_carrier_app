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
    public function index()
    {
        if ($this->request->is('get') && isset($this->request->query['shop'])) {
            $this->redirect('https://' . $this->request->query['shop'] . '/admin/oauth/authorize?client_id='.Configure::read('shopify.key').'&scope=read_shipping,write_shipping&redirect_uri=http://devtest01.uafrica.com');
        }
        die('An authorization error occured!');
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


                    if($resp = $response->json()){
                        $save_data = array(
                            'access_token' => $resp['access_token']);

                        //do we already have an API record for this shop? then update, otherwise create record
                        $shop = $this->Api->find('first', array('conditions' => 'shop = \''.$this->request->query['shop'].'\''));
                        if(count($shop)) {
                            $save_data['id'] = $shop['Api']['id'];
                            $save_data['modified'] = date('Y-m-d H:i:s');
                        } else {
                            //instantiate create model
                            $this->Api->create();
                            $save_data['shop'] = $this->request->query['shop'];
                            $save_data['created'] = date('Y-m-d H:i:s');
                            $save_data['modified'] = date('Y-m-d H:i:s');

                            //We don't have an API record for this shop, let's go ahead and create a Carrier Service for this shop
                            try {
                                $response = $client->post('https://'.$this->request->query['shop'].'/admin/carrier_services.json', [
                                    'headers' => ['Accept' => 'application/json',
                                        'X-Shopify-Access-Token' => $resp['access_token'],
                                        'Content-Type' => 'application/json'
                                    ],
                                    'body' => '{"carrier_service": {"name": "imerCourier","callback_url": "http://devtest01.uafrica.com/carriers/rates.json","format": "json","service_discovery": true}}']);
                            } catch (GuzzleHttp\Exception\BadResponseException $e) {
                                //throw error
                                die('An error occurred while creating carrier service!');
                            }
                        }

                        //insert/update record
                        if ($this->Api->save($save_data)){
                            $this->redirect('https://' . $this->request->query['shop'] . '/admin/apps');
                        } else {
//                            return json_encode(array('error' => array('code' => 500, 'msg' =>'An internal error occurred')));
                            die('An internal error occurred!');
                        }

                    }
                } catch (GuzzleHttp\Exception\BadResponseException $e) {
                    //return json_encode(array('error' => array('code' => $e->getCode(), 'msg' =>'Service replied with error: '.$e->getMessage())));
                    die('Service replied with error: '.$e->getMessage());
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


        $this->loadModel('ShippingMethod');
        $this->loadModel('PostalCode');
        $this->loadModel('ShippingRate');
        $this->loadModel('ShippingMethodsPostalCode');
        $this->loadModel('PostalCodesShippingRate');

        $db = ConnectionManager::getDataSource('default');
        //TRUNCATE TABLES
        $db->rawQuery("TRUNCATE shipping_methods_postal_codes;");
        $db->rawQuery("TRUNCATE postal_codes_shipping_rates;");
        $db->rawQuery("TRUNCATE shipping_methods;");
        $db->rawQuery("TRUNCATE postal_codes;");
        $db->rawQuery("TRUNCATE shipping_rates;");


        $methods = array('1 Day', 'Overnight');
        for ($i = 0; $i <= 10; $i++) {
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
        $valid_codes = array();
        for ($i = 0; $i <= 10; $i++) {
            $code_id = rand(array_keys($postalCodes, min($postalCodes))[0], array_keys($postalCodes, max($postalCodes))[0]);
            $rate_id = rand(array_keys($shippingRates, min($shippingRates))[0], array_keys($shippingRates, max($shippingRates))[0]);
            if ($code_id > 0 && $rate_id > 0) {
                $valid_codes[] = $code_id;
                $this->PostalCodesShippingRate->create();
                //$this->PostalCodesShippingRate->save(array('postal_code_id' => $code_id, 'rate_id' => $rate_id));
                $db->rawQuery("INSERT IGNORE INTO postal_codes_shipping_rates(postal_code_id, rate_id) VALUES ('" . (int)$code_id . "', '" . (int)$rate_id . "')");
            }
        }

        if(count($valid_codes)){
            foreach ($methods as $method) {
                $this->ShippingMethod->create();
                if ($this->ShippingMethod->save(array('name' => $method))) {

                    if ($new_method = $this->ShippingMethod->find('list', array('conditions' => array('name' => $method), 'fields' => array('id')))) {
                        //create random relationships between shipping methods and post codes
                        //now lets create a loop that makes sure that this shipping method has at least 1
                        // shipping rate per post code with valid rate
                        foreach ($valid_codes as $code_id) {
//                        $code_id = rand(array_keys($postalCodes, min($postalCodes))[0], array_keys($postalCodes, max($postalCodes))[0]);
                            if ($code_id) {
                                //lets make sure this code has a valid rate in the db
//                            $postalCodeRate = $db->query("SELECT rate_id FROM postal_codes_shipping_rates WHERE postal_code_id='".$code_id."' LIMIT 1");
                                if ($postalCodeRate = $this->PostalCodesShippingRate->find('first', array('fields' => array('rate_id'), 'conditions' => array('postal_code_id' => $code_id)))) {
                                    $this->ShippingMethodsPostalCode->create();
                                    /*if($this->CarrierServicesPostalCode->isUnique()){
                                        $this->CarrierServicesPostalCode->save(array('postal_code_id' => $code_id, 'carrier_service_id' => reset($new_carrier)));
                                    }*/
                                    $db->rawQuery("INSERT IGNORE INTO shipping_methods_postal_codes(postal_code_id, shipping_method_id) VALUES ('" . (int)$code_id . "', '" . (int)reset($new_method) . "')");
                                }
                            }
                        }
                    }
                }
            }
            die('Database fake data population completed!');
        }
        die('An error occurred!');
    }

    public function test_has()
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
//        $options['conditions'] = "PostalCode.code = '0083'";
        $this->ShippingMethod->recursive = FALSE;

        $shipping_methods = $this->ShippingMethod->find('all', $options);


        pr($shipping_methods);
        exit;
    }
}
