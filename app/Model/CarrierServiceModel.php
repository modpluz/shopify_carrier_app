<?php

class CarrierServiceModel extends AppModel {
    public $name = 'CarrierService';


    public $hasMany = array(
        'CarrierServicePostalCode'
    );

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
