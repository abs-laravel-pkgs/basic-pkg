<?php

namespace Abs\Basic;
use Abs\Basic\Country;
use App\Address;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;

class AddressController extends Controller {

	public function getAddress($address_id) {
		$address = Address::find($address_id);
		if (!$address) {
			return response()->json([
				'success' => false,
				'error' => 'Address not found',
			]);

		}
		$address->html_address = $address->formatted_address;
		return response()->json([
			'success' => true,
			'address' => $address,
		]);
	}

	public function getMyAddresses(Request $r) {
		$user = Auth::user();
		$addresses = Address::where([
			'address_of_id' => 80,
			'entity_id' => $user->id,
		])
			->with([
				'state',
				'country',
			])
			->get();
		return response()->json([
			'success' => true,
			'addresses' => $addresses,
			'extras' => [
				'theme' => $this->theme,
			],
		]);
	}

	public function getAddressFormData(Request $r) {

		if (!$r->id) {
			$address = new Address;
			$address->country = new Country;
		} else {
			$address = Address::with([
				'state',
				'country',
			])
				->find($r->id);
			if (!$address) {
				return response()->json([
					'success' => false,
					'error' => 'Address not found',
				]);
			}

		}
		return response()->json([
			'success' => true,
			'address' => $address,
			'extras' => [
				'country_list' => Country::getCountries(),
				'state_list' => State::getStates(['country_id' => 223]),

			],
		]);
	}

	public function saveAddress(Request $r) {

		if (!$r->id) {
			$address = new Address;
		} else {
			$address = Address::find($r->id);
			if (!$address) {
				return response()->json([
					'success' => false,
					'error' => 'Address not found',
				]);
			}
		}
		$address->fill($r->address);
		$address->address_of_id = 20;
		$address->entity_id = Auth::id();
		$address->save();

		return response()->json([
			'success' => true,
			'message' => 'Address saved successfully!',
		]);
	}

}
