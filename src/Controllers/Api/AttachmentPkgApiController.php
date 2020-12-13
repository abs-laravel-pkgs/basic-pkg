<?php

namespace Abs\BasicPkg\Controllers\Api;

use File;
use Abs\BasicPkg\Controllers\Api\BaseController;
use Abs\BasicPkg\Traits\CrudTrait;
use App\Classes\ApiResponse;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttachmentPkgApiController extends BaseController {
	use CrudTrait;
	public $model = Attachment::class;

	public function upload(Request $request){
		//dd($request->all());
		if (!File::exists(public_path() . '/files')) {
			File::makeDirectory(public_path() . '/files', 0777, true);
		}

		$fileInput = $request->file;
		$random_file_name = date('Y-m-d-H-i-s') . '-' . mt_rand() . '.';
		$extension = $fileInput->getClientOriginalExtension();
		$fileInput->move(public_path() . '/files/', $random_file_name . $extension);

		$attachment = new Attachment;
		$attachment->company_id = Auth::user()->company_id;
		$attachment->attachment_of_id = 21; //Company
		$attachment->attachment_type_id = 40; //Primary
		$attachment->entity_id = 1;
		$attachment->name = $random_file_name . $extension;
		$attachment->save();

		$response = new ApiResponse();
		$response->setData('attachment', $attachment);
		return $response->response();
	}
}
