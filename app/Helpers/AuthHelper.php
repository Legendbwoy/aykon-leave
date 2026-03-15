<?php

if (!function_exists('isAdmin')) {
    function isAdmin() {
        return auth()->check() && auth()->user()->isAdmin();
    }
}

if (!function_exists('isManager')) {
    function isManager() {
        return auth()->check() && auth()->user()->isManager();
    }
}

if (!function_exists('isEmployee')) {
    function isEmployee() {
        return auth()->check() && auth()->user()->isEmployee();
    }
}

if (!function_exists('hasRole')) {
    function hasRole($role) {
        return auth()->check() && auth()->user()->role === $role;
    }
}