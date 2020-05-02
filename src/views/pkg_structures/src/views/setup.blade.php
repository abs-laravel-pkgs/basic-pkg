@if(config('GGG.DEV'))
    <?php $III_prefix = '/packages/abs/GGG/src';?>
@else
    <?php $III_prefix = '';?>
@endif

<script type="text/javascript">
	app.config(['$routeProvider', function($routeProvider) {

	    $routeProvider.
	    //DDD
	    when('/GGG/FFF/list', {
	        template: '<FFF-list></FFF-list>',
	        title: 'CCC',
	    }).
	    when('/GGG/FFF/add', {
	        template: '<FFF-form></FFF-form>',
	        title: 'Add DDD',
	    }).
	    when('/GGG/FFF/edit/:id', {
	        template: '<FFF-form></FFF-form>',
	        title: 'Edit DDD',
	    }).
	    when('/GGG/FFF/card-list', {
	        template: '<FFF-card-list></FFF-card-list>',
	        title: 'DDD Card List',
	    });
	}]);

	//CCC
    var BBB_list_template_url = "{{asset($III_prefix.'/public/themes/'.$theme.'/GGG/FFF/list.html')}}";
    var BBB_form_template_url = "{{asset($III_prefix.'/public/themes/'.$theme.'/GGG/FFF/form.html')}}";
    var BBB_card_list_template_url = "{{asset($III_prefix.'/public/themes/'.$theme.'/GGG/FFF/card-list.html')}}";
    var BBB_modal_form_template_url = "{{asset($III_prefix.'/public/themes/'.$theme.'/GGG/partials/FFF-modal-form.html')}}";
</script>
<script type="text/javascript" src="{{asset($GGG_prefix.'/public/themes/'.$theme.'/GGG/FFF/controller.js')}}"></script>
