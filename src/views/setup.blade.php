@if(config('basic-pkg.DEV'))
    <?php $basic_pkg_prefix = '/packages/abs/basic-pkg/src';?>
@else
    <?php $basic_pkg_prefix = '';?>
@endif
<script type="text/javascript" src='{{asset($basic_pkg_prefix."/public/services/basic-pkg-services.js")}}'></script>
