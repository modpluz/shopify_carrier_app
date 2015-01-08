<?php
/**
 * Created by PhpStorm.
 * User: remmy
 * Date: 2015/01/08
 * Time: 8:00 PM
 */

class ApiModel extends AppModel {
//    public $name = 'auth';
    public $useTable = 'auth';
    public $validate = array(
        'shop' => array('rule' => 'notEmpty'),
        'code' => array('rule' => 'notEmpty'),
        'hmac' => array('rule' => 'notEmpty'),
        'signature' => array('rule' => 'notEmpty')
    );


}