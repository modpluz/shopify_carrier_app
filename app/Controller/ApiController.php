<?php
/**
 * Created by PhpStorm.
 * User: remmy
 * Date: 2015/01/07
 * Time: 11:14 PM
 */


App::uses('Controller', 'Controller');

/**
 * API Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 */
class ApiController extends Controller
{
    public $helpers = array('Session');
    public $components = array(/*'DebugKit.Toolbar',*/ 'Session');

    public function index()
    {
        if ($this->request->is('get') && isset($this->request->query['shop'])) {
           $this->redirect('https://' . $this->request->query['shop'] . '/admin/oauth/authorize?client_id=e11587d0c1de09134a91e4ea4ad13a7f&scope=read_shipping,write_shipping&redirect_uri=http://devtest01.uafrica.com');
        }
        die('Please specify a valid shop!');
    }

    public function install()
    {
        if ($this->request->is('get')) {
            if (isset($this->request->query['code']) && isset($this->request->query['shop'])) {
                //instantiate model
                $this->Api->create();
                //insert record
                if ($this->Api->save(array(
                    'code' => $this->request->query['code'],
                    'hmac' => $this->request->query['hmac'],
                    'signature' => $this->request->query['signature'],
                    'shop' => $this->request->query['shop'],
                    'created' => date('Y-m-d H:i:s'),
                    'modified' => date('Y-m-d H:i:s')
                ))) {
//                    die('Installation successful');
                    $this->redirect('https://' . $this->request->query['shop'] . '/admin/apps');
                }
            }
        }
        die('Installation failed!');
    }
}
