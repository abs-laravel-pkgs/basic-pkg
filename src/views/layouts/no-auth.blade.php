<!DOCTYPE html>
<html dir="ltr" lang="{{ app()->getLocale() }}" ng-app="app">
    <head>
        @include($theme.'-pkg::includes/common/theme-head')
        @include('themes/'.$theme.'/includes/common/app-head')
        @yield('app-head')
    </head>
    <body>
        @include($theme.'/includes/common/theme-header')
        @yield('content')
        @include($theme.'/includes/common/theme-footer')
        @include($theme.'/includes/common/theme-js')
        @yield('footer_js')
    </body>
</html>