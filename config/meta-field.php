<?php

return [

    /*
    |--------------------------------------------------------------------------
    | The meta field table name
    |--------------------------------------------------------------------------
    |
    | The meta field table name where the model associated meta data will be 
    | stored.
    |
    */

    'table' => 'meta_fields',


    /*
    |--------------------------------------------------------------------------
    | The meta field table associated model
    |--------------------------------------------------------------------------
    |
    | The model that is to handle the meta field table.
    |
    */

    'model' => Touhidurabir\MetaFields\Models\MetaField::class,


    /*
    |--------------------------------------------------------------------------
    | The Meta table's metas column default cast
    |--------------------------------------------------------------------------
    |
    | This define in which format the metas from the meta fields table will be 
    | casted to at the retrieve time .
    | 
    | The only allowed options are ['array', 'object', 'collection']. 
    |
    | By default it is set to retrieved the details in the json string format.
    |
    */

    'meta_retrieve_cast' => 'collection',

];