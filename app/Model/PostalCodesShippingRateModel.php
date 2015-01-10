<?php

class PostalCodesShippingRateModel extends AppModel {
    public $name = 'PostalCodeShippingRate';

    public $belongsTo = array(
        'PostalCode', 'ShippingRate'
    );
}
