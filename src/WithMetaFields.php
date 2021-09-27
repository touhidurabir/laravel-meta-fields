<?php

namespace Touhidurabir\MetaFields;

use Throwable;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

trait WithMetaFields {

    /**
     * The list of meta fields to set up for this campaign and campaign type combinations
     *
     * @var array
     */
    protected $metaFields = [];


    /**
     * The meta fields validation rules
	 * 
     * @return array
     */
    abstract public function metaRules() : array;


    /**
     * Generate the storable meta fields to value mappings
     *
	 * @param  array $validateds
     * @return array
     */
	public function metas(array $validateds = []) {

        if ( empty($validateds) ) {

            $validateds = $this->validated();
        }

        return array_intersect_key($validateds, array_flip($this->metaFields));
	}


	/**
     * Get the final rules set after merging the meta field rules
	 * 
     * @return array
     */
	public function getRules() {

        $metaRules = $this->metaRules();

        $this->metaFields = array_keys($metaRules);

		return array_merge($this->rules(), $metaRules);
	}


	/**
     * Create the default validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Factory  $factory
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function createDefaultValidator(ValidationFactory $factory) {
        
        return $factory->make(
            $this->validationData(), $this->container->call([$this, 'getRules']),
            $this->messages(), $this->attributes()
        )->stopOnFirstFailure($this->stopOnFirstFailure);
    }

}