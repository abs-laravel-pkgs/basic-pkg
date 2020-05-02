<?php

namespace Abs\BasicPkg;
use App\Http\Controllers\Controller;
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

		$template = $r->template;

		Storage::makeDirectory($r->pkg_name, 0777);
		Storage::makeDirectory($r->pkg_name . '/src', 0777);

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

			Storage::makeDirectory($r->pkg_name . '/src/config', 0777);
			$contents = file_get_contents(view('basic-pkg::pkg_structures/src/config/config')->getPath());
			$contents = $this->replaceTemplate($contents);
			Storage::put($r->pkg_name . '/src/config/config.php', $contents, 'public');

			Storage::makeDirectory($r->pkg_name . '/src/controllers', 0777);
			Storage::makeDirectory($r->pkg_name . '/src/controllers/Api', 0777);
			$contents = file_get_contents(view('basic-pkg::pkg_structures/src/controllers/Controller')->getPath());
			$contents = $this->replaceTemplate($contents);
			Storage::put($r->pkg_name . '/src/controllers/' . $this->data['module_pascal'] . 'Controller.php', $contents, 'public');

			Storage::makeDirectory($r->pkg_name . '/src/database/migrations', 0777);
			Storage::makeDirectory($r->pkg_name . '/src/database/seeds', 0777);
			$contents = file_get_contents(view('basic-pkg::pkg_structures/src/database/seeds/PkgPermissionSeeder')->getPath());
			$contents = $this->replaceTemplate($contents);
			Storage::put($r->pkg_name . '/src/database/seeds/' . $this->data['pkg_pascal'] . 'PermissionSeeder.php', $contents, 'public');

			Storage::makeDirectory($r->pkg_name . '/src/models', 0777);
			$contents = file_get_contents(view('basic-pkg::pkg_structures/src/models/Model')->getPath());
			$contents = $this->replaceTemplate($contents);
			Storage::put($r->pkg_name . '/src/models/' . $this->data['module_pascal'] . '.php', $contents, 'public');

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

			$dir = $r->pkg_name . '/src/routes/';
			Storage::makeDirectory($dir, 0777);

			$contents = file_get_contents(view('basic-pkg::pkg_structures/src/routes/api')->getPath());
			$contents = $this->replaceTemplate($contents);
			Storage::put($dir . 'api.php', $contents, 'public');

			$contents = file_get_contents(view('basic-pkg::pkg_structures/src/routes/web')->getPath());
			$contents = $this->replaceTemplate($contents);
			Storage::put($dir . 'web.php', $contents, 'public');

			$dir = $r->pkg_name . '/src/views/';
			Storage::makeDirectory($dir, 0777);

			$contents = file_get_contents(view('basic-pkg::pkg_structures/src/views/front-setup')->getPath());
			$contents = $this->replaceTemplate($contents);
			Storage::put($dir . 'front-setup.blade.php', $contents, 'public');

			$contents = file_get_contents(view('basic-pkg::pkg_structures/src/views/setup')->getPath());
			$contents = $this->replaceTemplate($contents);
			Storage::put($dir . 'setup.blade.php', $contents, 'public');

			$contents = file_get_contents(view('basic-pkg::pkg_structures/src/PkgServiceProvider')->getPath());
			$contents = $this->replaceTemplate($contents);
			Storage::put($r->pkg_name . '/src/' . $this->data['pkg_pascal'] . 'ServiceProvider.php', $contents, 'public');
		}

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
		return $contents;
	}
}