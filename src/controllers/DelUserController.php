<?php

namespace Abs\BasicPkg;
use Abs\Basic\Company;
use App\Category;
use App\Http\Controllers\Controller;
use App\Index;

class UserController extends Controller {

	private $company_id;

	public function __construct() {
		$this->company_id = config('custom.company_id');
		$this->data = Company::getCommonData($this->company_id);
	}

	public function index($url = 'home') {

		$index = $this->data['company']->indexes()->where('url', $url)->first();
		if (!$index) {
			abort(404);
		}
		$this->data = Company::getCommonData($this->company_id);

		if ($index->page_type_id == 20) {
			//CATEGORY
			$category = Category::where('seo_name', $url)
				->where('company_id', $this->company_id)
			// ->select(
			// 	'id',
			// 	'id',
			// 	'id',
			// 	'id',
			// 	'id',
			// 	'id',
			// )
				->with('strengths', 'strengths.type')
				->first();
			if (!$category) {
				return response()->json([
					'success' => false,
					'error' => 'Category not found',
				]);
			}
			foreach ($category->strengths as $key => $strength) {
				$strength->products = $strength->products($category->id);
			}
			$this->data['category'] = $category;
			return view(config('custom.theme') . '/pages/category', $this->data);

		} else if ($index->page_type_id == 21) {
			//PAGE
			if ($url = 'home') {
				$this->data['best_selling_categories'] = $this->data['company']
					->categories()
					->bestSelling()
					->get();
			}
			return view(config('custom.theme') . '/pages/' . $url, $this->data);
		}

	}
}
