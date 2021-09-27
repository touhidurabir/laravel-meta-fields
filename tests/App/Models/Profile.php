<?php

namespace Touhidurabir\MetaFields\Tests\App\Models;

use Touhidurabir\MetaFields\HasMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model {

    use SoftDeletes;

    use HasMeta;

    /**
     * The model associated table
     *
     * @var string
     */
    protected $table = 'profiles';


    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];


    /**
     * Define if a model can have multiple meta data records
     *
     * @return bool
     */
    public function canHaveMultipleMetas() {

        return false;
    }


    /**
     * Define if the metas associated with a parent model sync with parents state
     * The sync up behaviour apply to parent's [delete, force delete and restore] events
     * 
     * @return bool
     */
    public function syncWithParent() {

        return false;
    }

}