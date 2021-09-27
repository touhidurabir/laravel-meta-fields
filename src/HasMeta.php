<?php

namespace Touhidurabir\MetaFields;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMeta {

    /**
     * Handle associated metas on model delete, force delete or restore.
     *
     * @return void
     */
	public static function bootHasMeta() {

        $self = new self;

        if ( ! $self->syncWithParent() ) {

            return;
        }

		static::deleted(function($model) {

            $model->isForceDeleting() ? $model->metas()->forceDelete() : $model->metas()->delete();
        });

        static::restored(function($model) {

            $model->metas()->withTrashed()->restore();
        });
	}


    /**
     * Get all the metas associated with this model
     *
     * @return object<\Illuminate\Database\Eloquent\Relations\MorphMany>
     */
    public function metas(): MorphMany {
        
        return $this->morphMany(config('meta-field.model'), 'metable');
    }


    /**
     * Define if the metas associated with a parent model sync with parents state
     * The sync up behaviour apply to parent's [delete, force delete and restore] events
     * 
     * @return bool
     */
    public function syncWithParent() {

        return true;
    }


    /**
     * Define if a model can have multiple meta data records
     *
     * @return bool
     */
    public function canHaveMultipleMetas() {

        return true;
    }



    /**
     * Store metas of a model instance
     *
     * @param  array $metas
     * @return object
     */
    public function storeMeta(array $metas) {
        
        if ( $this->canHaveMultipleMetas() ) {

            return $this->metas()->create([
                'metas' => json_encode($metas)
            ]);
        }
        
        return $this->metas()->updateOrCreate(
            ['metable_type' => get_class($this), 'metable_id' => $this->id],
            ['metas' => json_encode($metas)]
        );
    }


    /**
     * Update metas of a model instance
     *
     * @param  array $metas
     * @return object
     * 
     * @throws \Exception
     */
    public function updateMeta(array $metas) {

        if ( $this->canHaveMultipleMetas() ) {

            throw new Exception('can not use shortcut updateMeta method when model can have multiple meta records');
        }

        return $this->metas()->updateOrCreate(
            ['metable_type' => get_class($this), 'metable_id' => $this->id],
            ['metas' => json_encode($metas)]
        );
    }


    /**
     * Delete metas of a model instance
     * The second argument define if this will be soft deleted or force deleted
     * 
     * @param  bool $permanent
     * @return bool
     */
    public function deleteMeta(bool $permanent = false) {

        return $permanent ? $this->metas()->forceDelete() : $this->metas()->delete();
    }


    /**
     * Has any meta associated
     *
     * @return bool
     */
    public function hasMeta() {

        return (bool)count($this->metas);
    }


    /**
     * If the given meta field associated with model resource
     *
     * @param  string   $field
     * @param  bool     $asBool
     * 
     * @return mixed
     */
    public function isMetaAssociated(string $field, bool $asBool = true) {

        if ( $this->hasMeta() ) {

            $metas = $this->metas->filter(function($meta) use ($field) {

                return collect(
                    json_decode($meta->getRawOriginal('metas'))
                )->has($field);

            });

            return $asBool ? (bool)$metas->count() : $metas;
        }

        return $asBool ? false : collect([]);
    }


    /**
     * Get the given meta field value set for model resource
     *
     * @param  string   $field
     * @param  bool     $all
     * 
     * @return mixed
     */
    public function metaValue(string $field, bool $all = false) {

        $metas = $this->isMetaAssociated($field, false);

        if ( ! (bool)$metas->count() ) {

            return null;
        }
        
        $values = $metas->map(function($meta) use ($field) {
            
            return collect(
                json_decode($meta->getRawOriginal('metas'))
            )[$field] ?? null;
        });

        return $all ? $values->filter()->toArray() : $values->first();
    }
    
}