<?php

// This file contains common helper functions for CRUD generation

if (!function_exists('str_plural')) {
    function str_plural($value)
    {
        return \Illuminate\Support\Str::plural($value);
    }
}

if (!function_exists('collect')) {
    function collect($value = null)
    {
        return new \Illuminate\Support\Collection($value);
    }
}
