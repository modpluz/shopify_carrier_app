<?php

class CarrierServicesPostalCodeModel extends AppModel {
    public $name = 'CarrierServicesPostalCode';

    public $belongsTo = array(
        'PostalCode', 'CarrierService'
    );
}
