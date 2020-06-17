<?php
namespace Abs\BasicPkg\Api;

use Abs\BasicPkg\Traits\CrudTrait;
use App\Config;
use App\Http\Controllers\Controller;

class ConfigController extends Controller {
	use CrudTrait;
	public $model = Config::class;
	public $successStatus = 200;

}