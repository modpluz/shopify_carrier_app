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
class ApiController extends Controller {
    public $components = array('DebugKit.Toolbar');

    public function index() {
        /*$client = $this->createClient();
        $requestToken = $client->getRequestToken('https://api.twitter.com/oauth/request_token', 'http://' . $_SERVER['HTTP_HOST'] . '/example/callback');

        if ($requestToken) {
            $this->Session->write('twitter_request_token', $requestToken);
            $this->redirect('https://api.twitter.com/oauth/authorize?oauth_token=' . $requestToken->key);
        } else {
            // an error occured when obtaining a request token
        }*/
        if(isset($_GET['shop'])) {
            $this->redirect('https://' . $_GET['shop'] . '/admin/oauth/authorize?client_id=e11587d0c1de09134a91e4ea4ad13a7f&scope=read_shipping,write_shipping&redirect_uri=http://devtest01.uafrica.com');
        } else {
            die('Please specify a valid shop!');
        }
    }

    public function install() {
        /*$client = $this->createClient();
        $requestToken = $client->getRequestToken('https://api.twitter.com/oauth/request_token', 'http://' . $_SERVER['HTTP_HOST'] . '/example/callback');

        if ($requestToken) {
            $this->Session->write('twitter_request_token', $requestToken);
            $this->redirect('https://api.twitter.com/oauth/authorize?oauth_token=' . $requestToken->key);
        } else {
            // an error occured when obtaining a request token
        }*/

        //$this->redirect('https://'.$_GET['shop'].'/admin/oauth/authorize?client_id=e11587d0c1de09134a91e4ea4ad13a7f&scope=read_shipping,write_shipping&redirect_uri=http://devtest01.uafrica.com');

        if(isset($_GET['code']) && isset($_GET['shop'])){
            die('installed');
        } else {
            die('Installation failed!');
        }

    }

    public function callback() {
        $requestToken = $this->Session->read('twitter_request_token');
        $client = $this->createClient();
        $accessToken = $client->getAccessToken('https://api.twitter.com/oauth/access_token', $requestToken);

        if ($accessToken) {
            $client->post($accessToken->key, $accessToken->secret, 'https://api.twitter.com/1/statuses/update.json', array('status' => 'hello world!'));
        }
    }

    private function createClient() {
        return new OAuthClient('e11587d0c1de09134a91e4ea4ad13a7f', 'ea173fb21be427f9413f44f59b90bd9e');
    }

}
