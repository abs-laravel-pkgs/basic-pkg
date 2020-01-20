@if(config('custom.PKG_DEV'))
    <?php $basic_pkg_prefix = '/packages/abs/basic-pkg/src';?>
@else
    <?php $basic_pkg_prefix = '';?>
@endif

<script type="text/javascript">
	var base_url = '{{url('')}}';
</script>
<script src="{{ URL::asset($basic_pkg_prefix.'/public/angular/angular-setup.js')}}"></script>

<script type="text/javascript">
    var user_list_template_url = "{{asset($basic_pkg_prefix.'/public/angular/basic-pkg/pages/user/list.html')}}";
    var user_form_template_url = "{{asset($basic_pkg_prefix.'/public/angular/basic-pkg/pages/user/list.html')}}";
    var user_view_template_url = "{{asset($basic_pkg_prefix.'/public/angular/basic-pkg/pages/user/view.html')}}";
</script>
<script type="text/javascript" src="{{URL::asset($basic_pkg_prefix.'/public/angular/basic-pkg/pages/user/controller.js')}}"></script>