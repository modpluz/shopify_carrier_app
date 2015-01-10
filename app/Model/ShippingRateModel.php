<?php

class ShippingRateModel extends AppModel {
    public $name = 'ShippingRate';

    public $hasMany = array(
        'PostalCodeShippingRate'
    );

}
