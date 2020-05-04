<?php

namespace Abs\BasicPkg;
use App\Http\Controllers\Controller;
use App\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PkgHelperController extends Controller {
	public function pkgHelperForm(Request $r) {
		$this->data = [];
		$this->data['theme'] = config('custom.theme');
		return view('basic-pkg::pkg-helper-form', $this->data);
	}

	public function generatePkg(Request $r) {

		$this->data['pkg_upper_snake'] = str_replace('-', '_', strtoupper($r->pkg_name)); //XXX
		$this->data['pkg_lower_chain'] = str_replace('-', '-', strtolower($r->pkg_name)); //GGG
		$this->data['pkg_lower_snake'] = str_replace('-', '_', strtolower($r->pkg_name)); //III
		$this->data['pkg_pascal'] = str_replace(' ', '', ucwords(str_replace('-', ' ', $r->pkg_name))); //YYY
		$this->data['web_routes'] = ''; //KKK
		$this->data['ng_routes'] = ''; //LLL
		$this->data['permissions'] = ''; //MMM

		$template = $r->template;

		Storage::makeDirectory($r->pkg_name, 0777);
		Storage::makeDirectory($r->pkg_name . '/src', 0777);

		$web_routes = "";
		$ng_routes = "";
		$permissions = "";
		foreach ($r->module_name as $key => $module_name) {
			if (!$module_name) {
				continue;
			}
			$module_plural_name = $r->module_plural_name[$key];

			$this->data['module_pascal'] = str_replace(' ', '', ucwords(str_replace('-', ' ', $module_name))); //ZZZ
			$this->data['module_lower_snake_plural'] = str_replace('-', '_', strtolower($module_plural_name)); //AAA
			$this->data['module_lower_snake'] = str_replace('-', '_', strtolower($module_name)); //BBB
			$this->data['module_word_pascal_plural'] = ucwords(str_replace('-', ' ', $module_plural_name)); //CCC
			$this->data['module_word_pascal'] = ucwords(str_replace('-', ' ', $module_name)); //DDD
			$this->data['module_lower_chain_plural'] = $module_plural_name; //EEE
			$this->data['module_lower_chain_singular'] = $module_name; //FFF
			$this->data['module_camel_case_singular'] = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $module_name)))); //HHH
			$this->data['columns'] = '';

			$contents = file_get_contents(view('basic-pkg::pkg_structures/composer')->getPath());
			$contents = $this->replaceTemplate($contents);
			Storage::put($r->pkg_name . '/composer.json', $contents, 'public');

			$contents = file_get_contents(view('basic-pkg::pkg_structures/gitignore')->getPath());
			$contents = $this->replaceTemplate($contents);
			Storage::put($r->pkg_name . '/.gitignore', $contents, 'public');

			Storage::makeDirectory($r->pkg_name . '/src/config', 0777);
			$contents = file_get_contents(view('basic-pkg::pkg_structures/src/config/config')->getPath());
			$contents = $this->replaceTemplate($contents);
			Storage::put($r->pkg_name . '/src/config/config.php', $contents, 'public');

			Storage::makeDirectory($r->pkg_name . '/src/controllers', 0777);
			Storage::makeDirectory($r->pkg_name . '/src/controllers/Api', 0777);
			$contents = file_get_contents(view('basic-pkg::pkg_structures/src/controllers/Controller')->getPath());
			$contents = $this->replaceTemplate($contents);
			Storage::put($r->pkg_name . '/src/controllers/' . $this->data['module_pascal'] . 'Controller.php', $contents, 'public');

			$contents = file_get_contents(view('basic-pkg::pkg_structures/src/controllers/AppController')->getPath());
			$contents = $this->replaceTemplate($contents);
			Storage::put($r->pkg_name . '/' . $this->data['module_pascal'] . 'Controller.php', $contents, 'public');

			Storage::makeDirectory($r->pkg_name . '/src/models', 0777);
			$contents = file_get_contents(view('basic-pkg::pkg_structures/src/models/Model')->getPath());
			$table = Table::where('name', $this->data['module_lower_snake_plural'])->first();
			$this->data['columns'] = '';
			if ($table) {
				$columns = $table->columns()->pluck('name')->toArray();
				$this->data['columns'] = json_encode($columns);
			}
			$contents = $this->replaceTemplate($contents);
			Storage::put($r->pkg_name . '/src/models/' . $this->data['module_pascal'] . '.php', $contents, 'public');

			$contents = file_get_contents(view('basic-pkg::pkg_structures/src/models/AppModel')->getPath());
			$contents = $this->replaceTemplate($contents);
			Storage::put($r->pkg_name . '/' . $this->data['module_pascal'] . '.php', $contents, 'public');

			$dir = $r->pkg_name . '/src/public/themes/' . $template . '/' . $this->data['pkg_lower_chain'] . '/' . $this->data['module_lower_chain_singular'] . '/';
			Storage::makeDirectory($dir, 0777);

			$contents = file_get_contents(view('basic-pkg::pkg_structures/src/public/themes/' . $template . '/pkg/module/controller')->getPath());
			$contents = $this->replaceTemplate($contents);
			Storage::put($dir . 'controller.js', $contents, 'public');

			$contents = file_get_contents(view('basic-pkg::pkg_structures/src/public/themes/' . $template . '/pkg/module/form')->getPath());
			$contents = $this->replaceTemplate($contents);
			Storage::put($dir . 'form.html', $contents, 'public');

			$contents = file_get_contents(view('basic-pkg::pkg_structures/src/public/themes/' . $template . '/pkg/module/list')->getPath());
			$contents = $this->replaceTemplate($contents);
			Storage::put($dir . 'list.html', $contents, 'public');

			$dir = $r->pkg_name . '/src/views/';
			Storage::makeDirectory($dir, 0777);

			$contents = file_get_contents(view('basic-pkg::pkg_structures/src/views/front-setup')->getPath());
			$contents = $this->replaceTemplate($contents);
			Storage::put($dir . 'front-setup.blade.php', $contents, 'public');

			$contents = file_get_contents(view('basic-pkg::pkg_structures/src/PkgServiceProvider')->getPath());
			$contents = $this->replaceTemplate($contents);
			Storage::put($r->pkg_name . '/src/' . $this->data['pkg_pascal'] . 'ServiceProvider.php', $contents, 'public');

			$web_routes_template = "
		//DDD
		Route::get('/FFF/get-list', 'ZZZController@getZZZList')->name('getZZZList');
		Route::get('/FFF/get-form-data', 'ZZZController@getZZZFormData')->name('getZZZFormData');
		Route::post('/FFF/save', 'ZZZController@saveZZZ')->name('saveZZZ');
		Route::get('/FFF/delete', 'ZZZController@deleteZZZ')->name('deleteZZZ');
		Route::get('/FFF/get-filter-data', 'ZZZController@getZZZFilterData')->name('getZZZFilterData');

		";

			$web_routes .= $this->replaceTemplate($web_routes_template);

			$ng_routes_template = "
<script type='text/javascript'>
	app.config(['\$routeProvider', function(\$routeProvider) {
	    \$routeProvider.
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
    var BBB_list_template_url = '{{asset(\$III_prefix.'/public/themes/'.\$theme.'/GGG/FFF/list.html')}}';
    var BBB_form_template_url = '{{asset(\$III_prefix.'/public/themes/'.\$theme.'/GGG/FFF/form.html')}}';
    var BBB_card_list_template_url = '{{asset(\$III_prefix.'/public/themes/'.\$theme.'/GGG/FFF/card-list.html')}}';
    var BBB_modal_form_template_url = '{{asset(\$III_prefix.'/public/themes/'.\$theme.'/GGG/partials/FFF-modal-form.html')}}';
</script>
<script type='text/javascript' src='{{asset(\$III_prefix.'/public/themes/'.\$theme.'/GGG/FFF/controller.js')}}'></script>

";

			$ng_routes .= $this->replaceTemplate($ng_routes_template);

			$pemissions_template = "
			//CCC
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'EEE',
				'display_name' => 'CCC',
			],
			[
				'display_order' => 1,
				'parent' => 'EEE',
				'name' => 'add-FFF',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'EEE',
				'name' => 'edit-FFF',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'EEE',
				'name' => 'delete-FFF',
				'display_name' => 'Delete',
			],

			";
			$permissions .= $this->replaceTemplate($pemissions_template);

		}

		$this->data['web_routes'] = $web_routes; //KKK
		$this->data['ng_routes'] = $ng_routes; //LLL
		$this->data['permissions'] = $permissions; //MMM

		$dir = $r->pkg_name . '/src/routes/';
		Storage::makeDirectory($dir, 0777);

		$contents = file_get_contents(view('basic-pkg::pkg_structures/src/routes/api')->getPath());
		$contents = $this->replaceTemplate($contents);
		Storage::put($dir . 'api.php', $contents, 'public');

		$contents = file_get_contents(view('basic-pkg::pkg_structures/src/routes/web')->getPath());
		$contents = $this->replaceTemplate($contents);
		Storage::put($dir . 'web.php', $contents, 'public');

		$dir = $r->pkg_name . '/src/views/';
		$contents = file_get_contents(view('basic-pkg::pkg_structures/src/views/setup')->getPath());
		$contents = $this->replaceTemplate($contents);
		Storage::put($dir . 'setup.blade.php', $contents, 'public');

		Storage::makeDirectory($r->pkg_name . '/src/database/migrations', 0777);
		Storage::makeDirectory($r->pkg_name . '/src/database/seeds', 0777);
		$contents = file_get_contents(view('basic-pkg::pkg_structures/src/database/seeds/PkgPermissionSeeder')->getPath());
		$contents = $this->replaceTemplate($contents);
		Storage::put($r->pkg_name . '/src/database/seeds/' . $this->data['pkg_pascal'] . 'PermissionSeeder.php', $contents, 'public');

	}

	private function replaceTemplate($contents) {
		$contents = str_replace('XXX', $this->data['pkg_upper_snake'], $contents);
		$contents = str_replace('YYY', $this->data['pkg_pascal'], $contents);
		$contents = str_replace('ZZZ', $this->data['module_pascal'], $contents);
		$contents = str_replace('AAA', $this->data['module_lower_snake_plural'], $contents);
		$contents = str_replace('BBB', $this->data['module_lower_snake'], $contents);
		$contents = str_replace('CCC', $this->data['module_word_pascal_plural'], $contents);
		$contents = str_replace('DDD', $this->data['module_word_pascal'], $contents);
		$contents = str_replace('EEE', $this->data['module_lower_chain_plural'], $contents);
		$contents = str_replace('FFF', $this->data['module_lower_chain_singular'], $contents);
		$contents = str_replace('GGG', $this->data['pkg_lower_chain'], $contents);
		$contents = str_replace('HHH', $this->data['module_camel_case_singular'], $contents);
		$contents = str_replace('III', $this->data['pkg_lower_snake'], $contents);
		$contents = str_replace('JJJ,', $this->data['columns'], $contents);
		$contents = str_replace('KKK', $this->data['web_routes'], $contents);
		$contents = str_replace('LLL', $this->data['ng_routes'], $contents);
		$contents = str_replace('MMM,', $this->data['permissions'], $contents);
		return $contents;
	}
}