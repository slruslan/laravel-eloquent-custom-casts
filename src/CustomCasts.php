<?php

namespace Slruslan\CustomCasts;

use Illuminate\Support\Str;

trait CustomCasts
{
    /**
     * Get the type of cast for a model attribute.
     *
     * @param  string  $key
     * @return string
     */
    protected function getCastType($key)
    {
        return ($this->getCasts()[$key]);
    }

    /**
     * Cast an attribute to a native PHP type.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function castAttribute($key, $value)
    {
        if (is_null($value)) {
            return $value;
        }

        switch ($this->getCastType($key)) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'object':
                return $this->fromJson($value, true);
            case 'array':
            case 'json':
                return $this->fromJson($value);
            case 'collection':
                return new BaseCollection($this->fromJson($value));
            case 'date':
            case 'datetime':
                return $this->asDateTime($value);
            case 'timestamp':
                return $this->asTimeStamp($value);
            default:
                return $this->toClass($this->getCastType($key), $value);
        }
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        // First we will check for the presence of a mutator for the set operation
        // which simply lets the developers tweak the attribute as it is set on
        // the model, such as "json_encoding" an listing of data for storage.
        if ($this->hasSetMutator($key)) {
            $method = 'set'.Str::studly($key).'Attribute';

            return $this->{$method}($value);
        }

        // If an attribute is listed as a "date", we'll convert it from a DateTime
        // instance into a form proper for storage on the database tables using
        // the connection grammar's date format. We will auto set the values.
        elseif ($value && (in_array($key, $this->getDates()) || $this->isDateCastable($key))) {
            $value = $this->fromDateTime($value);
        }

        if ($this->isJsonCastable($key) && ! is_null($value)) {
            $value = $this->asJson($value);
        }

        // If this attribute contains a JSON ->, we'll set the proper value in the
        // attribute's underlying array. This takes care of properly nesting an
        // attribute in the array's value in the case of deeply nested items.
        if (Str::contains($key, '->')) {
            return $this->fillJsonAttribute($key, $value);
        }

        // CustomCasts:
        //
        // If an attribute is listed as a a class name and it's valid,
        // we seriaize it to string using php serialize() function
        if ($this->isCustomCastable($key) && !is_null($value)) {
            $value = serialize($value);
        }

        $this->attributes[$key] = $value;

        return $this;
    }


    /**
     * Determine whether a value is a valid class name
     * and can be custom-casted.
     *
     * @param  string  $key
     * @return bool
     */
    protected function isCustomCastable($key)
    {
        return $this->hasCast($key) && class_exists($this->getCastType($key));
    }

    /**
     * Unserialize string value, if it is a valid class
     * Otherwise return the original value.
     *
     * @param string $class
     * @param string $value
     * @return mixed
     */
    protected function toClass($class, $value)
    {
        if(!class_exists($class)) {
            return $value;
        }

        return unserialize($value);
    }
}
