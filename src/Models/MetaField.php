<?php

namespace Touhidurabir\MetaFields\Models;

use Touhidurabir\ModelUuid\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Touhidurabir\MetaFields\Casts\MetaCast;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MetaField extends Model {

    use SoftDeletes;

    use HasUuid;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];


    /**
     * The attributes that will be casted to given types
     *
     * @var array
     */
    protected $casts = [
        'metas' => MetaCast::class,
    ];
    

    /**
     * Get the model associated table name
     *
     * @return string
     */
    public function getTable() {

        return config('meta-field.table', parent::getTable());
    }


    /**
     * Get the metable data of model
     *
     * @return object<\Illuminate\Database\Eloquent\Relations\MorphTo>
     */
    public function metable(): MorphTo {
        
        return $this->morphTo();
    }

}