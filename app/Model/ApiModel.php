<?php
/**
 * Created by PhpStorm.
 * User: remmy
 * Date: 2015/01/08
 * Time: 8:00 PM
 */

class ApiModel extends AppModel {
    public $name = 'Api';

    public $validate = array(
        'shop' => array('rule' => 'notEmpty'),
        'access_token' => array('rule' => 'notEmpty'),
    );


}