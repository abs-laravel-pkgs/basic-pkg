<?php

namespace Abs\Basic;

use App\Cart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model {
	use SoftDeletes;
	protected $table = 'companies';
	protected $fillable = [
		'code',
		'name',
		'address_id',
		'logo_id',
		'contact_number',
		'email',
		'created_by_id',
		'updated_by_id',
		'deleted_by_id',
	];

	public function indexes() {
		return $this->hasMany('App\Index');
	}

	public function mainCategories() {
		return $this->hasMany('App\MainCategory')->orderBy('display_order', 'asc');
	}

	public function categories() {
		return $this->hasMany('App\Category');
	}

	public function strengths() {
		return $this->hasMany('App\Strength');
	}

	public function shippingMethods() {
		return $this->hasMany('App\ShippingMethod');
	}

	public function activeSubstances() {
		return $this->hasMany('App\Abs\Entity')->where('entity_type_id', 1);
	}

	public function drugCategories() {
		return $this->hasMany('App\Abs\Entity')->where('entity_type_id', 2);
	}

	public function manufacturers() {
		return $this->hasMany('App\Abs\Entity')->where('entity_type_id', 3);
	}

	public function packageTypes() {
		return $this->hasMany('App\Abs\Entity')->where('entity_type_id', 4);
	}

	public function strengthTypes() {
		return $this->hasMany('App\Abs\Entity')->where('entity_type_id', 5);
	}

	//STATIC METHOD
	public static function getCommonData($company_id) {
		$data = [];
		$data['company'] = $company = Company::findOrFail($company_id);

		//MAIN CATEGORIES LIST
		$data['main_categories'] = $company->mainCategories()->with(
			'categories:id,name,seo_name,main_category_id'
		)->get();

		//CART
		$cart = Cart::getCart();
		// dd($cart);
		$data['cart_summary'] = $cart;

		return $data;
	}
}
