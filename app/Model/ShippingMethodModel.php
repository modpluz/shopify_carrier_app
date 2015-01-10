<?php

class ShippingMethodModel extends AppModel {
    public $name = 'ShippingMethod';


    /*public $hasMany = array(
        'ShippingMethodPostalCode'
    );*/

    /*    public $hasMany = array(
            'CarrierServicesPostalCode'
        );*/

   /* public $hasAndBelongsToMany = array(
        'CarrierServicesPostalCode' =>
            array(
                'className' => 'PostalCode',
                'joinTable' => 'carrier_services_postal_codes',
                'foreignKey' => 'carrier_service_id',
                'associationForeignKey' => 'postalcode_id',
                'unique' => true,
                'conditions' => '',
                'fields' => '',
                'order' => '',
                'limit' => '',
                'offset' => '',
                'finderQuery' => '',
                'with' => 'CarrierServicesPostalCode'
            )
    );*/
}
