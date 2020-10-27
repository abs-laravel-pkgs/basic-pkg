<!DOCTYPE html>
<html dir="ltr" lang="{{ app()->getLocale() }}" ng-app="app">
    <head>
        @include('themes/'.$theme.'/includes/common/app-head')
        @yield('header_css')
    </head>
    <body>
        @include('themes/'.$theme.'/includes/common/common-header')
        <div class="main-wrap">
            @include('themes/'.$theme.'/includes/common/theme-header')
            @yield('content')
        </div><!-- Main Wrap -->
        @include('themes/'.$theme.'/includes/common/theme-footer')
        @include('themes/'.$theme.'/includes/common/common-js')
        @yield('footer_js')
    </body>

</html>
