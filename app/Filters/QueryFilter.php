<?php

namespace App\Filters;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

abstract class QueryFilter
{
    protected $valid;

    abstract public function rules(): array;

    public function applyTo($query, array $filters)
    {
        $rules = $this->rules();

        $validator = Validator::make(array_intersect_key($filters, $rules), $rules);

        $this->valid = $validator->valid();

        foreach ($this->valid as $name => $value) {

            $this->applyFilters($query, $name, $value);
        }

        return $query;
    }

    public function applyFilters($query, $field, $value): void
    {
        $method = 'filterBy' . ucfirst(Str::camel($field));

        if (method_exists($this, $method)) {
            $this->$method($query, $value);
        } else {
            $query->where($field, $value);
        }
    }

    public function applyOrderBy($query, $field, $direction): void
    {
        $method = 'orderBy' . ucfirst($field);

        if (method_exists($this, $method)) {
            $this->$method($query, $direction);
        } else {
            $query->orderBy($field, $direction);
        }
    }

}
