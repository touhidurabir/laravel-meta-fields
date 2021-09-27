<?php

namespace Touhidurabir\MetaFields\Casts;

use Exception;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class MetaCast implements CastsAttributes{

    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string                               $key
     * @param  mixed                                $value
     * @param  array                                $attributes
     * 
     * @return 
     */
    public function get($model, $key, $value, $attributes) {

        $toCast = strtolower(config('meta-field.meta_retrieve_cast'));

        if ( $toCast === 'array' ) {

            return json_decode($model->getRawOriginal($key), true);
        }

        if ( $toCast === 'object' ) {

            return (object)json_decode($model->getRawOriginal($key), true);
        }

        if ( $toCast === 'collection' ) {

            return collect([(object)json_decode($model->getRawOriginal($key), true)]);
        }

        return $value;
    }
    

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string                               $key
     * @param                                       $value
     * @param  array                                $attributes
     * 
     * @return array
     */
    public function set($model, $key, $value, $attributes) {

        if ( is_array($value) ) {

            return json_encode($value);
        }

        if ( is_string($value) ) {

            json_decode($value);

            if ( json_last_error() !== JSON_ERROR_NONE ) {

                throw new Exception('Not a valid json string passed');
            }

            return $value;
        }

        if ( is_object($value) ) {

            return json_encode((array)$value);
        }
    }

}