<?php

namespace Rockero\ModelStateField;

use ReflectionClass;
use Laravel\Nova\Fields\Select;
use Spatie\ModelStates\Validation\ValidStateRule;

class State extends Select
{
    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->resolveUsing(function ($value, $resource, $attribute) {
            $parent = (new ReflectionClass($value))->getParentClass()->name;

            $this->rules('required', ValidStateRule::make($parent));

            $this->options(function () use ($value) {
                return collect($value->transitionableStates())
                    ->prepend($value::getMorphClass())
                    ->keyBy(fn ($value) => $value)
                    ->map(fn ($label) => __($label));
            });

            return $value;
        });

        $this->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
            if (! $model->{$attribute}->equals($request->$requestAttribute)) {
                $model->{$attribute}->transitionTo($request->$requestAttribute);
            }
        });
    }
}
