# Laravel Eloquent custom casts trait

[![Latest Version on Packagist](https://img.shields.io/packagist/v/slruslan/laravel-eloquent-custom-casts.svg?style=flat-square)](https://packagist.org/packages/slruslan/laravel-eloquent-custom-casts)
![License GPL](http://img.shields.io/badge/license-GPL-blue.svg?style=flat-square)

Laravel Eloquent provides an Attribute Casting function, that allows you to automatically convert attributes to common data types.
By default, supported cast types are: ```integer, real, float, double,  string, boolean, object, array, collection, date, datetime, and timestamp.```

As you can see, custom class types are not supported. This trait adds this support, so you automatically serialize and deserialize your custom classes.

## Installation

Using Composer:

``` bash
$ composer require slruslan/laravel-eloquent-custom-casts --dev
```

## Usage

1. Create a field of TEXT type where your data will be stored.

2. To enable trait, add  ```use Slruslan\CustomCasts\CustomCasts;``` line to your Eloquent Model class.

For example:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Slruslan\CustomCasts\CustomCasts;

class Post extends Model
{
    use CustomCasts;

```

3. Add your fields and class names to be serialized in protected $casts array.

```php
    protected $casts = [
        'geo_location' => \App\Services\CustomLocation::class,
        'another_custom_field' => \App\Services\AnotherCustomField::class
    ];
```

4. You're ready to use your model. Here is a basic example:

```php
    $post = Post::find(1);

    // For example, imagine we have an \App\Services\CustomLocation class,
    // that implements any custom logics and for some reasons
    // we have to store it in DB as it is.

    // Let's instantinate it.
    $location = new \App\Services\CustomLocation(55.9937441, 92.7521816);

    // And call some methods, imagine we have them there.
    $location->updatePosition();
    $location->callAPI();

    // After that, we want to save it in our post model.
    // We can just assign the value and call default save() method.
    $post->geo_location = $location;
    $post->save();

    // It's saved. Let's get a post again
    // and check the field class.

    $post = Post::find(1);

    var_dump($post instanceof \App\Services\CustomLocation);

    // Outputs:
    // bool(true)
```

## License

GNU General Public License v3.0 (GPL). Refer to the [LICENSE](LICENSE) file to get more info.

## Author contacts:

You can ask me any questions by:

Email: me@slinkov.xyz

VK: [vk.com/slruslan](https://vk.com/slruslan)
