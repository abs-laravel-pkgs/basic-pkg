<?php

namespace Abs\BasicPkg\Models\V1;

use Abs\CompanyPkg\Traits\CompanyableTrait;
use App\Company;
use App\Entity;
use App\Index;
use App\MainCategory;
use App\Models\BaseModel;
use App\Models\Config;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends BaseModel {
	use CompanyableTrait;

	protected $table = 'attachments';
	public $timestamps = false;

	public function __construct(array $attributes = []) {
		parent::__construct($attributes);
		$this->rules = [
			'name' => [
				'min:3',
			],
		];

	}

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name',
	];

	protected $casts = [
	];

	public $sortable = [
		'name',
	];

	public $sortScopes = [
		//'id' => 'orderById',
		//'code' => 'orderCode',
		//'name' => 'orderBytName',
		//'mobile_number' => 'orderByMobileNumber',
		//'email' => 'orderByEmail',
	];

	// Custom attributes specified in this array will be appended to model
	protected $appends = [
		'url',
	];

	//This model's validation rules for input values
	public $rules = [
		//Defined in constructor
	];

	public $fillableRelationships = [
		'company',
		'attachmentOf',
		'attachmentType',
		'entity',
	];

	public $relationshipRules = [
		'attachmentOf' => [
			'required',
			//'hasOne:App\Models\Address,App\Models\Address::optionIds',
		],
		'attachmentType' => [
			'required',
			//'hasOne:App\Models\Address,App\Models\Address::optionIds',
		],
	];

	// Relationships to auto load
	public static function relationships($action = '', $format = ''): array
	{
		$relationships = [];

		if ($action === 'index') {
			$relationships = array_merge($relationships, [
				'attachmentOf',
				'entity',
			]);
		} else if ($action === 'read') {
			$relationships = array_merge($relationships, [
				'attachmentOf',
				'attachmentType',
				'entity',
			]);
		} else if ($action === 'save') {
			$relationships = array_merge($relationships, [
			]);
		} else if ($action === 'options') {
			$relationships = array_merge($relationships, [
			]);
		}

		return $relationships;
	}

	public static function appendRelationshipCounts($action = '', $format = ''): array
	{
		$relationships = [];

		if ($action === 'index') {
			$relationships = array_merge($relationships, [
				'items',
			]);
		} else if ($action === 'options') {
			$relationships = array_merge($relationships, [
			]);
		}

		return $relationships;
	}

	// Dynamic Attributes --------------------------------------------------------------
	public function getUrlAttribute(): string{
		return url('/public/files/'.$this->name);
	}

	// Relationships --------------------------------------------------------------
	public function attachmentOf(): BelongsTo {
		return $this->belongsTo(Config::class, 'attachment_of_id');
	}

	//public function entity(): BelongsTo {
	//	return $this->belongsTo(Attachment::class, 'image_id');
	//}

	public function attachmentType(): BelongsTo {
		return $this->belongsTo(Config::class, 'attachment_type_id');
	}

	//--------------------- Query Scopes -------------------------------------------------------
	public function scopeFilterSearch($query, $term): void
	{
		if ($term !== '') {
			$query->where(function ($query) use ($term) {
				$query->orWhere('name', 'LIKE', '%' . $term . '%');
			});
		}
	}

	//--------------------- Static Operations -------------------------------------------------------

	public static function saveFromObject($record_data): array
	{
		$record = [
			'Company Code' => $record_data->company_code,
			'Main Category' => $record_data->main_category,
			'Display Order' => $record_data->display_order,
			'Category Name' => $record_data->category_name,
			'Image' => $record_data->image,
			'SEO Name' => $record_data->seo_name,
			'Page Title' => $record_data->page_title,
			'Meta Description' => $record_data->meta_description,
			'Meta Keywords' => $record_data->meta_keywords,
			'Description' => $record_data->description,
			'Usage' => $record_data->usage,
			'Manufacturer' => $record_data->manufacturer,
			'Active Substance' => $record_data->active_substance,
			'Customer Rating' => $record_data->customer_rating,
			'Has Free' => $record_data->has_free,
			'Has Free Shipping' => $record_data->has_free_shipping,
			'Package Type' => $record_data->package_type,
			'Is Best Selling' => $record_data->is_best_selling,
			'Status' => $record_data->status,
		];
		return static::saveFromExcelArray($record);
	}

	public static function saveFromExcelArray($record_data, $company = null): array
	{

		$errors = [];
		$company = Company::where('code', $record_data['Company Code'])->first();
		if (!$company) {
			return [
				'success' => false,
				'errors' => ['Invalid Company : ' . $record_data['Company Code']],
			];
		}

		if (!isset($record_data['created_by_id'])) {
			$admin = $company->admin();

			if (!$admin) {
				return [
					'success' => false,
					'errors' => ['Default Admin user not found'],
				];
			}
			$created_by_id = $admin->id;
		} else {
			$created_by_id = $record_data['created_by_id'];
		}

		$main_category_id = null;
		if(!empty($record_data['Main Category'])){

			$main_category = MainCategory::where([
				'name' => $record_data['Main Category'],
				'company_id' => $company->id,
			])->first();
			if (!$main_category) {
				$errors[] = 'Invalid Main Category Name : ' . $record_data['Main Category'];
			} else {
				$main_category_id = $main_category->id;
			}
		}

		$manufacturer_id = null;
		if ($record_data['Manufacturer']) {
			$manufacturer = Entity::where([
				'company_id' => $company->id,
				'name' => $record_data['Manufacturer'],
				'entity_type_id' => 3,
			])->first();
			if (!$manufacturer) {
				$errors[] = 'Invalid manufacturer : ' . $record_data['Manufacturer'];
			} else {
				$manufacturer_id = $manufacturer->id;
			}
		}

		$active_substance_id = null;
		if(!empty($record_data['Active Substance'])){
			$active_substance = Entity::where([
				'company_id' => $company->id,
				'name' => $record_data['Active Substance'],
				'entity_type_id' => 1,
			])->first();
			if (!$active_substance) {
				$errors[] = 'Invalid active_substance : ' . $record_data['Active Substance'];
			}
		}

		$package_type_id = null;
		if(!empty($record_data['Package Type'])){
			$package_type = Entity::where([
				'company_id' => $company->id,
				'name' => $record_data['Package Type'],
				'entity_type_id' => 4,
			])->first();
			if (!$package_type) {
				$errors[] = 'Invalid package_type : ' . $record_data['Package Type'];
			}
		}

		if (count($errors) > 0) {
			return [
				'success' => false,
				'errors' => $errors,
			];
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'name' => $record_data['Category Name'],
		]);
		$record->display_order = $record_data['Display Order'];
		$record->seo_name = $record_data['SEO Name'];
		$record->page_title = $record_data['Page Title'];
		$record->meta_description = $record_data['Meta Description'];
		$record->meta_keywords = $record_data['Meta Keywords'];
		$record->description = $record_data['Description'];
		$record->usage = $record_data['Usage'];
		$record->package_type_id = $package_type_id;
		$record->manufacturer_id = $manufacturer_id;
		$record->active_substance_id = $active_substance_id;
		$record->customer_rating = $record_data['Customer Rating'];
		$record->main_category_id = $main_category_id;
		$record->starts_at = 0;
		$record->has_free = $record_data['Has Free'] == 'Yes' ? 1 : 0;
		$record->has_free_shipping = $record_data['Has Free Shipping'] == 'Yes' ? 1 : 0;
		$record->is_best_selling = $record_data['Is Best Selling'] == 'Yes' ? 1 : 0;

		$record->created_by_id = $admin->id;
		if ($record_data['Status'] != 1) {
			$record->deleted_at = date('Y-m-d');
		}
		$record->created_by_id = $created_by_id;
		$record->save();

		// $image = Attachment::firstOrNew([
		// 	'attachment_of_id' => 40, //CATEGORY
		// 	'attachment_type_id' => 60, //PRIMARY
		// 	'entity_id' => $record->id,
		// ]);
		// $image->name = str_replace(' ', '-', $record->name) . '.jpg';
		// $image->save();

		// $record->image_id = $image->id;
		// $record->save();

		// $destination = categoryImagePath($record->id);
		// $status = Storage::makeDirectory($destination, 0777);
		// if (!Storage::exists($destination . '/' . $image->name)) {
		// 	$src_file = 'public/product-src-img/01 big/' . $image->name;
		// 	if (Storage::exists($src_file)) {
		// 		Storage::copy($src_file, $destination . '/' . $image->name);
		// 	} else {
		// 		dump('Category Image Src File Note Found : ' . $src_file);
		// 	}
		// }

		$index = Index::firstOrNew([
			'company_id' => $company->id,
			'url' => $record['seo_name'],
		]);
		$index->page_type_id = 20; //CATEGORY
		$index->save();
		return [
			'success' => true,
		];

	}

}
