<?php

namespace Abs\YYY;
use App\Http\Controllers\Controller;
use App\ZZZ;
use Auth;
use Carbon\Carbon;
use DB;
use Entrust;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class ZZZController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.theme');
	}

	public function getZZZList(Request $request) {
		$AAA = ZZZ::withTrashed()

			->select([
				'AAA.id',
				'AAA.name',
				'AAA.short_name',

				DB::raw('IF(AAA.deleted_at IS NULL, "Active","Inactive") as status'),
			])
			->where('AAA.company_id', Auth::user()->company_id)

			->where(function ($query) use ($request) {
				if (!empty($request->name)) {
					$query->where('AAA.name', 'LIKE', '%' . $request->name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('AAA.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('AAA.deleted_at');
				}
			})
		;

		return Datatables::of($AAA)
			->rawColumns(['name', 'action'])
			->addColumn('name', function ($BBB) {
				$status = $BBB->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $BBB->name;
			})
			->addColumn('action', function ($BBB) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				if (Entrust::can('edit-BBB')) {
					$output .= '<a href="#!/GGG/BBB/edit/' . $BBB->id . '" id = "" title="Edit"><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1 . '" onmouseout=this.src="' . $img1 . '"></a>';
				}
				if (Entrust::can('delete-BBB')) {
					$output .= '<a href="javascript:;" data-toggle="modal" data-target="#BBB-delete-modal" onclick="angular.element(this).scope().deleteZZZ(' . $BBB->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete . '" onmouseout=this.src="' . $img_delete . '"></a>';
				}
				return $output;
			})
			->make(true);
	}

	public function getZZZFormData(Request $request) {
		$id = $request->id;
		if (!$id) {
			$BBB = new ZZZ;
			$action = 'Add';
		} else {
			$BBB = ZZZ::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['success'] = true;
		$this->data['BBB'] = $BBB;
		$this->data['action'] = $action;
		return response()->json($this->data);
	}

	public function saveZZZ(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'short_name.required' => 'Short Name is Required',
				'short_name.unique' => 'Short Name is already taken',
				'short_name.min' => 'Short Name is Minimum 3 Charachers',
				'short_name.max' => 'Short Name is Maximum 32 Charachers',
				'name.required' => 'Name is Required',
				'name.unique' => 'Name is already taken',
				'name.min' => 'Name is Minimum 3 Charachers',
				'name.max' => 'Name is Maximum 191 Charachers',
			];
			$validator = Validator::make($request->all(), [
				'short_name' => [
					'required:true',
					'min:3',
					'max:32',
					'unique:AAA,short_name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'name' => [
					'required:true',
					'min:3',
					'max:191',
					'unique:AAA,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$BBB = new ZZZ;
				$BBB->company_id = Auth::user()->company_id;
			} else {
				$BBB = ZZZ::withTrashed()->find($request->id);
			}
			$BBB->fill($request->all());
			if ($request->status == 'Inactive') {
				$BBB->deleted_at = Carbon::now();
			} else {
				$BBB->deleted_at = NULL;
			}
			$BBB->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'DDD Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'DDD Updated Successfully',
				]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json([
				'success' => false,
				'error' => $e->getMessage(),
			]);
		}
	}

	public function deleteZZZ(Request $request) {
		DB::beginTransaction();
		// dd($request->id);
		try {
			$BBB = ZZZ::withTrashed()->where('id', $request->id)->forceDelete();
			if ($BBB) {
				DB::commit();
				return response()->json(['success' => true, 'message' => 'DDD Deleted Successfully']);
			}
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function getZZZs(Request $request) {
		$AAA = ZZZ::withTrashed()
			->with([
				'EEE',
				'EEE.user',
			])
			->select([
				'AAA.id',
				'AAA.name',
				'AAA.short_name',
				DB::raw('IF(AAA.deleted_at IS NULL, "Active","Inactive") as status'),
			])
			->where('AAA.company_id', Auth::user()->company_id)
			->get();

		return response()->json([
			'success' => true,
			'AAA' => $AAA,
		]);
	}
}