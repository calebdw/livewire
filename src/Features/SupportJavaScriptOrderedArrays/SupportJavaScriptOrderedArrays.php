<?php

namespace Livewire\Features\SupportJavaScriptOrderedArrays;

use function Synthetic\on;

class SupportJavaScriptOrderedArrays
{
    function boot()
    {
        on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof \Livewire\LivewireSynth) return;

            return function ($value) {
                return $this->reindexArrayWithNumericKeysOtherwiseJavaScriptWillMessWithTheOrder($value);
            };
        });
    }

    function reindexArrayWithNumericKeysOtherwiseJavaScriptWillMessWithTheOrder($value)
    {
        // Make sure string keys are last (but not ordered) and numeric keys are ordered.
        // JSON.parse will do this on the frontend, so we'll get ahead of it.

        $isCollection = false;
        $collectionClass = Collection::class;

        if ($value instanceof \Illuminate\Support\Collection) {
            $isCollection = true;
            $collectionClass = get_class($value);

            $value = $value->all();
        }

        if (! is_array($value)) {
            return $value;
        }

        $itemsWithNumericKeys = array_filter($value, function ($key) {
            return is_numeric($key);
        }, ARRAY_FILTER_USE_KEY);
        ksort($itemsWithNumericKeys);

        $itemsWithStringKeys = array_filter($value, function ($key) {
            return ! is_numeric($key);
        }, ARRAY_FILTER_USE_KEY);

        //array_merge will reindex in some cases so we stick to array_replace
        $normalizedData = array_replace($itemsWithNumericKeys, $itemsWithStringKeys);

        $output = array_map(function ($value) {
            return static::reindexArrayWithNumericKeysOtherwiseJavaScriptWillMessWithTheOrder($value);
        }, $normalizedData);

        if ($isCollection) {
            return new $collectionClass($output);
        }

        return $output;
    }
}
