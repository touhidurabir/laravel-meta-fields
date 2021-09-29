# Laravel Meta Fields

A php package for laravel framework to handle model meta data in a elegant way. 

## Installation

Require the package using composer:

```bash
composer require touhidurabir/laravel-meta-fields
```

To publish the config and migration file:
```bash
php artisan vendor:publish --provider="Touhidurabir\ModelRepository\ModelRepositoryServiceProvider"
```

## Configuration

The **meta-field** config file contains the configuration option of meta table name and meta model. It also provides an cast option that will be applied to meta table's **metas** column which contains the meta fields as **array**, **object** or **collection(Laravel Collection)** . Read the config file to know more details . 

## Usage

To use with a model , just use the **HasMeta** tarit.

```php
<?php

namespace App\Models;

use Touhidurabir\MetaFields\HasMeta;

class User extends Authenticatable {

    use HasMeta;
}
```

And then use it in such way as 

```php
$user = User::create([...]);
$meta = $user->storeMeta([]);
```

As this package use the **Illuminate\Database\Eloquent\Relations\MorphMany** to maintain a one to many poly morphic relation between the parent model and metas , it is possible to restrict if a model can have multiple metas or not by overriding the **HasMeta** traits **canHaveMultipleMetas** method my returning **false** as : 

```php
/**
 * Define if a model can have multiple meta data records
 *
 * @return bool
 */
public function canHaveMultipleMetas() {

    return false;
}
```

Also the metas are state aware , that is if the associated parent model got deleted, force deleted or restored , meta recored will follow the same model record state . This is by default enabled . To disable this override **syncWithParent** directly in the model calss to return **false** .

```php
/**
 * Define if the metas associated with a parent model sync with parents state
 * The sync up behaviour apply to parent's [delete, force delete and restore] events
 * 
 * @return bool
 */
public function syncWithParent() {

    return true;
}
```

As metas are managed as **Polymorphic Relations** and use laravel's MorphMany relations, all models related functionality are availabel . But this package also provide some additional methods such .

To Update, the **updateMeta** method such as : 

```php
$user->updateMeta([])
```
> NOTE : if model can have multiple meta records, calling **updateMeta** will throw exception as not possible to know which one of multiple meta records to update . 

To delete, the **deleteMeta** methods can be used as : 

```php
$user->deleteMeta()
```

> By default metas will be deleted as soft deleted record as this package uses **softDelets** trait on meta model . to force delete, pass **true** as the only argument to **deleteMeta** method . 

This package Provide some handly out of box methods to handle metas of a model instance . such as 

### To Determine if model instance has any meta associated 

```php
$user->hasMeta()
```
which will return **boolean** based on any meta associated with or not. 

### To Determine if aspecific meta fields associated

To deletermine if a specific metafields associated with model instance, follow as : 

```php
$user->isMetaAssociated('field_name')
```

By default it will return **boolean** to indicate if there is any associated meta field associate with this one . But if need those records also , pass **true** as the second argument which will return collection of matched metas or empty collection if none found.

### To get the meta value 

To get the meta value of a a given field for a model instance, follow as : 

```php
$user->metaValue('field_name')
```

By default, it will only pass one single and first match value even if there is multiple of of meta records that contains the give meta fields . to get all pass **true** as the second argument which will return an array as list of all values . 

### Storing Metas and Validation

To make the storing and validattion process of meta easier , this package provide another trait named **WithMetaFields** whcih can be used with the **FormRequest** class . This trait has as an abstract method **metaRules** that must be defined which will contains the validation rules for metas . The package will also use the field name defined in this methods to determine the meta fields . 

Use the trait in the **FormRequest** class as such : 

```php
use Illuminate\Foundation\Http\FormRequest;
use Touhidurabir\MetaFields\WithMetaFields;

class StoreUser extends FormRequest {

    use WithMetaFields;
    
    /**
     * The meta fields validation rules
     *
     * @return array
     */
    public function metaRules() : array {

        return [
            'bio' => [
                ...
            ],
            'dob' => [
                ...
            ],
        ];
    }
}
```

And then from the **controller** class, 

```php
/**
 * Store a newly created resource in storage.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\Response
 */
public function store(StoreUser $request) {
    
    // create user
    $user = User::create(...);

    // store meta for user 
    $metas = $request->metas($request->validated()); // this array[$request->validated()] argument is optional
    $user->storeMeta($metas);
}
```


## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](./LICENSE.md)
