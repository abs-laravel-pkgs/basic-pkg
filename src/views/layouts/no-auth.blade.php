<!DOCTYPE html>
<html dir="ltr" lang="{{ app()->getLocale() }}" ng-app="app">
    <head>
        @include($theme.'-pkg::theme-head')
        @include('themes/'.$theme.'/includes/common/app-head')
        @yield('app-head')
    </head>
    <body>
        @yield('content')
        include('themes/'.$theme.'/includes/common/theme-footer')
        include('themes/'.$theme.'/includes/common/theme-js')
        @yield('footer_js')
    </body>
</html>
