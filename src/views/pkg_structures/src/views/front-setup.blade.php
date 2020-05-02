@if(config('GGG.DEV'))
    <?php $III_prefix = '/packages/abs/GGG/src';?>
@else
    <?php $III_prefix = '';?>
@endif

<script type="text/javascript">
    var AAA_voucher_list_template_url = "{{asset($III_prefix.'/public/themes/'.$theme.'/GGG/FFF/FFF.html')}}";
</script>
<script type="text/javascript" src="{{asset($III_prefix.'/public/themes/'.$theme.'/GGG/FFF/controller.js')}}"></script>
