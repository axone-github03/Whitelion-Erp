<?php

namespace App\Http\Controllers;
use App\Models\ChannelPartner;
use App\Models\CityList;
use App\Models\Company;
use App\Models\CountryList;
use App\Models\CreditTranscationLog;
use App\Models\DataMaster;
use App\Models\MainMaster;
use App\Models\ProductInventory;
use App\Models\ProductLog;
use App\Models\SalePerson;
use App\Models\SalesHierarchy;
use App\Models\StateList;
use App\Models\User;
use App\Models\UserDiscount;
use DB;
use File;
use Illuminate\Support\Facades\Hash;

//use Session;

class MigrationProcessController extends Controller {

	public function __construct() {

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');

			}

			return $next($request);

		});

	}

	function index() {

		//$this->migrationCountry();
		//$this->migrationState();
		//$this->migrationCity();
		//$this->migrationMainMaster();
		//$this->migrationDataMaster();
		//$this->migrationSalesHierarchy();
		//$this->migrationCompanyMaster();
		//$this->migrationUsers();
		//$this->migrationProductImages();
		//$this->migrationProducts();
		//$this->migrationChannelPartner();
		//$this->migrationUserDiscount();
		$this->uploadDataOnDigitalOcean();

	}

	function uploadDataOnDigitalOcean() {
		print_r("okay");
		die;

	}

	function dataFromCollection($collectionName) {
		return json_decode(file_get_contents(public_path('mdata/' . $collectionName)), true);
	}
	function findIdFromCollection($collectionName, $tableName, $collectionId, $collectionFieldName, $tableFieldName) {

		$data = $this->dataFromCollection($collectionName);

		foreach ($data as $key => $value) {

			if ($value['_id']['$oid'] == $collectionId) {

				$TableData = DB::table($tableName)->where($tableFieldName, $value[$collectionFieldName])->first();

				return $TableData;

			}

		}

		return;

	}

	function findIdFromCollection1($collectionName, $tableName, $collectionId, $collectionFieldName1, $collectionFieldName2, $tableFieldName) {

		$data = $this->dataFromCollection($collectionName);

		foreach ($data as $key => $value) {

			if ($value['_id']['$oid'] == $collectionId) {

				$TableData = DB::table($tableName)->where($tableFieldName, $value[$collectionFieldName1][$collectionFieldName2])->first();

				return $TableData;

			}

		}

		return;

	}

	function migrationCountry() {
		$data = $this->dataFromCollection('countries.json');

		foreach ($data as $key => $value) {
			if ($value['country'] == "India") {

				$CountryList = new CountryList();
				$CountryList->code = $value['abbreviation'];
				$CountryList->name = $value['country'];
				$CountryList->save();

			}

		}

		echo 'migrationCountry->Done';

	}

	function migrationState() {

		$data = $this->dataFromCollection('states.json');

		foreach ($data as $key => $value) {

			if ($value['countryId']['$oid'] == "5fc889cd2e41a6158a8ee41a") {

				$countryObject = $this->findIdFromCollection('countries.json', 'country_list', $value['countryId']['$oid'], 'country', 'name');

				$StateList = new StateList();
				$StateList->country_id = $countryObject->id;
				$StateList->name = $value['name'];
				$StateList->save();

			}

		}

		echo 'migrationState->Done';

	}

	function migrationCity() {

		$data = $this->dataFromCollection('cities.json');

		foreach ($data as $key => $value) {
			if (isset($value['stateId']['$oid'])) {

				$stateObject = $this->findIdFromCollection('states.json', 'state_list', $value['stateId']['$oid'], 'name', 'name');
				if (isset($stateObject) && isset($stateObject->country_id)) {

					$CityList = new CityList();
					$CityList->country_id = $stateObject->country_id;
					$CityList->state_id = $stateObject->id;
					$CityList->name = $value['city'];
					$CityList->save();

				}

			} else {
				echo $value['city'] . " without state";
				echo '<br>';
			}

		}

		echo 'migrationCity->Done';

	}

	function migrationMainMaster() {

		$data = $this->dataFromCollection('masters.json');

		// echo '<pre>';
		// print_r($data);
		// die;

		foreach ($data as $key => $value) {

			$MainMaster = new MainMaster();
			$MainMaster->name = $value['name'];
			$MainMaster->code = $value['code'];
			$MainMaster->save();

		}

		echo 'migrationMainMaster->Done';

	}

	function migrationDataMaster() {

		$data = $this->dataFromCollection('submasters.json');

		foreach ($data as $key => $value) {
			if (isset($value['masterId']['$oid'])) {

				$masterObject = $this->findIdFromCollection('masters.json', 'main_master', $value['masterId']['$oid'], 'name', 'name');

				$DataMaster = new DataMaster();
				$DataMaster->main_master_id = $masterObject->id;
				$DataMaster->name = $value['name'];
				$DataMaster->code = $value['code'];
				$DataMaster->save();
			} else {
				echo $value['code'] . " without master";
				echo '<br>';
			}

		}

		echo 'migrationDataMaster->Done';

	}

	function migrationSalesHierarchy() {

		$data = $this->dataFromCollection('salepersonmasters.json');

		foreach ($data as $key => $value) {

			$SalesHierarchy = new SalesHierarchy();
			$SalesHierarchy->name = $value['name'];
			$SalesHierarchy->code = $value['code'];

			$parentId = 0;

			if (isset($value['parentId']['$oid'])) {

				$SalesHierarchyObject = $this->findIdFromCollection('salepersonmasters.json', 'sales_hierarchy', $value['parentId']['$oid'], 'name', 'name');
				$parentId = $SalesHierarchyObject->id;

			}
			$SalesHierarchy->parent_id = $parentId;
			$SalesHierarchy->save();

		}

		echo 'migrationSalesHierarchy->Done';

	}

	function migrationCompanyMaster() {

		$data = $this->dataFromCollection('bussinessentities.json');

		foreach ($data as $key => $value) {

			$countryObject = $this->findIdFromCollection('countries.json', 'country_list', $value['address']['countryId']['$oid'], 'country', 'name');

			$stateObject = $this->findIdFromCollection('states.json', 'state_list', $value['address']['stateId']['$oid'], 'name', 'name');

			$cityObject = $this->findIdFromCollection('cities.json', 'city_list', $value['address']['cityId']['$oid'], 'city', 'name');

			$Company = new Company();
			$Company->name = $value['companyName'];
			$Company->first_name = $value['first_name'];
			$Company->last_name = $value['last_name'];
			$Company->email = $value['email'];
			$Company->phone_number = $value['phoneNo']['ccode'] . " " . $value['phoneNo']['mobile'];
			$Company->address_line1 = $value['address']['line1'];
			$Company->address_line2 = $value['address']['line2'];
			$Company->pincode = $value['address']['pincode'];
			$Company->country_id = $countryObject->id;
			$Company->state_id = $stateObject->id;
			$Company->city_id = $cityObject->id;
			$Company->status = 1;
			$Company->save();

		}

		echo 'migrationCompanyMaster->Done';

	}

	public function migrationUsers() {

		$data = $this->dataFromCollection('users.json');

		foreach ($data as $key => $value) {

			$userType = "";

			if ($value['type'] == "admin") {
				$userType = 0;

			} else if ($value['type'] == "companyadmin") {
				$userType = 1;

			} else if ($value['type'] == "saleperson") {
				$userType = 2;

			} else if ($value['type'] == "accountuser") {

				$userType = 3;
			} else if ($value['type'] == "dispatcher") {

				$userType = 4;

			} else {
				echo '<pre>';
				print_r($value);
				die;
				continue;

			}

			if ($userType == 2) {

				$saleHierarchyiObject = $this->findIdFromCollection('salepersonmasters.json', 'sales_hierarchy', $value['subType']['$oid'], 'name', 'name');

				$cityList = $value['city'];

				$cityIds = array();
				$stateIds = array();

				foreach ($cityList as $keyC => $valueC) {
					$cityObject = $this->findIdFromCollection('cities.json', 'city_list', $valueC['$oid'], 'city', 'name');
					$cityIds[] = $cityObject->id;
					$stateIds[] = $cityObject->state_id;

				}
				$cityIds = array_unique($cityIds);
				$cityIds = array_values($cityIds);

				$stateIds = array_unique($stateIds);
				$stateIds = array_values($stateIds);

				$reporting_manager_id = 0;

				if (isset($value['higherAuthorityId']) && isset($value['higherAuthorityId']['$oid']) && $value['higherAuthorityId']['$oid'] != "") {

					$userObject = $this->findIdFromCollection1('users.json', 'users', $value['higherAuthorityId']['$oid'], 'email', 'emailId', 'email');

					if ($userObject) {
						$reporting_manager_id = $userObject->id;
					} else {

						echo 'parent not added :' . $value['email']['emailId'];
						echo '<br>';
						continue;
					}

				}

			}

			$Status = ($value['status'] == "active") ? 1 : 0;

			$countryObject = $this->findIdFromCollection('countries.json', 'country_list', $value['regAddress']['countryId']['$oid'], 'country', 'name');

			$stateObject = $this->findIdFromCollection('states.json', 'state_list', $value['regAddress']['stateId']['$oid'], 'name', 'name');

			$cityObject = $this->findIdFromCollection('cities.json', 'city_list', $value['regAddress']['cityId']['$oid'], 'city', 'name');

			$User = User::where('email', $value['email']['emailId'])->first();
			if ($User) {
				continue;
			}

			$User = new User();
			$User->password = Hash::make("111111");
			$User->last_active_date_time = date('Y-m-d H:i:s');
			$User->last_login_date_time = date('Y-m-d H:i:s');
			$User->avatar = "default.png";
			$User->company_id = 1;
			$User->type = $userType;
			$User->reference_type = getUserTypes()[$userType]['lable'];
			if ($userType == 2) {

				$SalePerson = new SalePerson();
				$SalePerson->reporting_company_id = 1;

			}

			$User->first_name = $value['first_name'];
			$User->last_name = $value['last_name'];
			$User->email = $value['email']['emailId'];
			$User->dialing_code = $value['phone_no']['ccode'];
			$User->phone_number = $value['phone_no']['mobile'];
			$User->ctc = $value['ctc'];
			$User->address_line1 = $value['regAddress']['line1'];
			$User->address_line2 = $value['regAddress']['line2'];
			$User->pincode = $value['regAddress']['pincode'];
			$User->country_id = $countryObject->id;
			$User->state_id = $stateObject->id;
			$User->city_id = $cityObject->id;
			$User->status = $Status;
			$User->save();

			if ($userType == 2) {

				$SalePerson->user_id = $User->id;
			}

			if ($userType == 2) {

				$SalePerson->type = $saleHierarchyiObject->id;
				$SalePerson->reporting_manager_id = $reporting_manager_id;
				$SalePerson->states = implode(",", $stateIds);
				$SalePerson->cities = implode(",", $cityIds);
				$SalePerson->save();

				$User->reference_id = $SalePerson->id;
				$User->save();
			}

		}

		echo 'migrationUsers->Done';

	}
	public function migrationProductImages() {

		$data = $this->dataFromCollection('products.json');

		foreach ($data as $key => $value) {

			// if ($key == 25) {
			// 	break;
			// }

			if ($key < 125) {
				continue;
			}

			$brekRecord = 150;

			if ($key == $brekRecord) {
				break;
			}

			$uploadedFile1 = "default.png";

			if ($value['icon'] && $value['icon'] != "") {

				// print_r($value['icon']);
				// die;

				$folderPathImage = '/s/product';
				$piecesOfIcon = explode("/", $value['icon']);
				$fileName1 = end($piecesOfIcon);
				$destinationPath = public_path($folderPathImage);

				$alreadyImage = array("1627978070998_yl9uu_qEUX6pRRHrY.png", "1627035901577_pkkbh_kWMpNnRJX0N.png", "1627035822381_bnltz_34xCPWreEB.png", "1627034977391_vhyw2_TyCM6YUQ-d.png", "1627036044754_rle6q_j5sUi3wjXu.png");
				if (!in_array($fileName1, $alreadyImage)) {
					file_put_contents(public_path($folderPathImage . "/" . $fileName1), file_get_contents($value['icon']));

					if (File::exists(public_path($folderPathImage . "/" . $fileName1))) {

						createThumbs(public_path($folderPathImage . "/" . $fileName1), public_path($folderPathImage . "/thumb-" . $fileName1), 200);

						$uploadedFile1 = $fileName1;

					}

				}

				print_r("DONE :" . $key);
			}
		}

		echo 'migrationProductImages->Done';

	}

	public function migrationProducts() {
		$data = $this->dataFromCollection('products.json');

		foreach ($data as $key => $value) {

			$uploadedFile1 = "default.png";

			if ($value['icon'] && $value['icon'] != "") {

				$folderPathImage = '/s/product';
				$piecesOfIcon = explode("/", $value['icon']);
				$fileName1 = end($piecesOfIcon);
				$destinationPath = public_path($folderPathImage);
				//file_put_contents(public_path($folderPathImage . "/" . $fileName1), file_get_contents($value['icon']));

				if (File::exists(public_path($folderPathImage . "/" . $fileName1))) {

					//createThumbs(public_path($folderPathImage . "/" . $fileName1), public_path($folderPathImage . "/thumb-" . $fileName1), 200);

					$uploadedFile1 = $fileName1;

				}
			}

			$quantity = 0;
			$Status = ($value['status'] == "active") ? 1 : 0;
			if ($Status == 0) {

				continue;

			}

			if (isset($value['stockistStock']) && is_array($value['stockistStock'])) {

				$arrayColumn = array_column($value['stockistStock'], 'stock');
				$quantity = array_sum($arrayColumn);

			}

			$brandObject = $this->findIdFromCollection('submasters.json', 'data_master', $value['productBrand']['$oid'], 'code', 'code');
			$codeOBject = $this->findIdFromCollection('submasters.json', 'data_master', $value['productType']['$oid'], 'code', 'code');

			$ProductInventoryA = ProductInventory::where('product_brand_id', $brandObject->id)->where('product_code_id', $codeOBject->id)->first();
			if ($ProductInventoryA) {
				continue;

			}

			$ProductInventory = new ProductInventory();
			$ProductInventory->product_brand_id = $brandObject->id;
			$ProductInventory->product_code_id = $codeOBject->id;
			$ProductInventory->description = $value['description'];
			$ProductInventory->image = $uploadedFile1;
			$ProductInventory->quantity = $quantity;
			$ProductInventory->price = $value['price'];
			$ProductInventory->weight = $value['weight'];

			$ProductInventory->status = $Status;
			$ProductInventory->save();
			if ($ProductInventory) {

				$debugLog = array();
				$debugLog['name'] = "product-new";
				$debugLog['product_inventory_id'] = $ProductInventory->id;
				$debugLog['request_quantity'] = $quantity;
				$debugLog['quantity'] = $quantity;
				$debugLog['description'] = "Deployed from old DB";

				$DebugLog = new ProductLog();
				$DebugLog->product_inventory_id = $debugLog['product_inventory_id'];
				$DebugLog->request_quantity = $debugLog['request_quantity'];
				$DebugLog->quantity = $debugLog['quantity'];
				$DebugLog->user_id = 1;
				$DebugLog->name = $debugLog['name'];
				$DebugLog->description = $debugLog['description'];
				$DebugLog->save();

			} else {
				echo "something went wrong";
			}

		}

		echo 'migrationProducts->Done';

	}

	function migrationChannelPartner() {

		$data = $this->dataFromCollection('channelpartners.json');
		foreach ($data as $key => $value) {

			$user_company_id = 1;
			$reporting_manager_id = 0;

			if ($value['type'] == "stockist") {
				$userType = 101;

			} else if ($value['type'] == "adm") {
				$userType = 102;

			} else if ($value['type'] == "apm") {
				$userType = 103;

			} else if ($value['type'] == "dealer") {

				$userType = 104;
			} else {
				echo '<pre>';
				print_r($value);
				die;
				continue;

			}

			if ($value['paymentMode'] == "credit") {
				$paymentMode = 2;

			} else if ($value['paymentMode'] == "advance") {

				$paymentMode = 1;

			} else if ($value['paymentMode'] == "pdc") {

				$paymentMode = 0;

			} else {

				echo '<pre>';
				print_r($value['paymentMode']);
				die;

			}

			// echo '<pre>';
			// print_r($value['assignedDiscount']);
			// die;

			if (isset($value['higherAuthorityId']) && isset($value['higherAuthorityId']['$oid']) && $value['higherAuthorityId']['$oid'] != "" && $value['higherAuthorityId']['$oid'] != $value['bussinessEntityId']['$oid']) {

				$userObject = $this->findIdFromCollection1('channelpartners.json', 'users', $value['higherAuthorityId']['$oid'], 'email', 'emailId', 'email');

				if ($userObject) {
					$reporting_manager_id = $userObject->id;
				} else {

					echo 'parent not added :' . $value['email']['emailId'];
					echo '<br>';
					continue;
				}

			}

			$paymentMode = 0;

			$isCreditUpdate = 0;

			$assignedSalesPerson = $value['assignedSalesPerson'];

			$salePersons = array();

			foreach ($assignedSalesPerson as $keyS => $valueS) {

				$userObject = $this->findIdFromCollection1('users.json', 'users', $valueS['$oid'], 'email', 'emailId', 'email');
				$salePersons[] = $userObject->id;

			}

			$countryObject = $this->findIdFromCollection('countries.json', 'country_list', $value['regAddress']['countryId']['$oid'], 'country', 'name');

			$stateObject = $this->findIdFromCollection('states.json', 'state_list', $value['regAddress']['stateId']['$oid'], 'name', 'name');

			$cityObject = $this->findIdFromCollection('cities.json', 'city_list', $value['regAddress']['cityId']['$oid'], 'city', 'name');

			$dcountryObject = $this->findIdFromCollection('countries.json', 'country_list', $value['deliveryAddress']['countryId']['$oid'], 'country', 'name');

			$dstateObject = $this->findIdFromCollection('states.json', 'state_list', $value['deliveryAddress']['stateId']['$oid'], 'name', 'name');

			$dcityObject = $this->findIdFromCollection('cities.json', 'city_list', $value['deliveryAddress']['cityId']['$oid'], 'city', 'name');

			$Status = ($value['status'] == "active") ? 1 : 0;

			$User = new User();
			$User->password = Hash::make("111111");
			$User->last_active_date_time = date('Y-m-d H:i:s');
			$User->last_login_date_time = date('Y-m-d H:i:s');
			$User->avatar = "default.png";

			$ChannelPartner = new ChannelPartner();
			$ChannelPartner->credit_limit = 0;
			$ChannelPartner->pending_credit = 0;
			$isCreditUpdate = 1;

			$User->first_name = $value['first_name'];
			$User->last_name = $value['last_name'];
			$User->email = $value['email']['emailId'];
			$User->dialing_code = $value['phone_no']['ccode'];
			$User->phone_number = $value['phone_no']['mobile'];
			$User->ctc = 0;
			$User->address_line1 = $value['regAddress']['line1'];
			$User->address_line2 = $value['regAddress']['line2'];
			$User->pincode = $value['regAddress']['pincode'];
			$User->country_id = $countryObject->id;
			$User->state_id = $stateObject->id;
			$User->city_id = $cityObject->id;
			$User->status = $Status;
			$User->company_id = $user_company_id;
			$User->type = $userType;
			$User->reference_type = "";
			$User->reference_id = 0;
			$User->save();

			$ChannelPartner->user_id = $User->id;
			$ChannelPartner->type = $userType;
			$ChannelPartner->firm_name = $value['firm_name'];
			$ChannelPartner->reporting_manager_id = $reporting_manager_id;
			$ChannelPartner->reporting_company_id = $user_company_id;
			$ChannelPartner->sale_persons = implode(",", $salePersons);

			$ChannelPartner->payment_mode = $paymentMode;
			$ChannelPartner->credit_days = 0;

			$ChannelPartner->gst_number = $value['gstNumber'];
			$ChannelPartner->shipping_limit = $value['shippingLimit'];
			$ChannelPartner->shipping_cost = $value['shippingCharge'];
			$ChannelPartner->d_address_line1 = $value['deliveryAddress']['line1'];
			$ChannelPartner->d_address_line2 = $value['deliveryAddress']['line2'];
			$ChannelPartner->d_pincode = $value['deliveryAddress']['pincode'];
			$ChannelPartner->d_country_id = $dcountryObject->id;
			$ChannelPartner->d_state_id = $dstateObject->id;
			$ChannelPartner->d_city_id = $dcityObject->id;

			$ChannelPartner->save();

			$User->reference_type = getChannelPartners()[$User->type]['lable'];
			$User->reference_id = $ChannelPartner->id;
			$User->save();

			if ($isCreditUpdate == 1) {

				$CreditTranscationLog = new CreditTranscationLog();
				$CreditTranscationLog->user_id = $User->id;
				$CreditTranscationLog->type = 1;
				$CreditTranscationLog->request_amount = 0;
				$CreditTranscationLog->amount = 0;
				$CreditTranscationLog->description = "intial";
				$CreditTranscationLog->save();

			}

		}

		echo 'migrationChannelPartner->Done';

	}

	function migrationUserDiscount() {

		$data = $this->dataFromCollection('channelpartners.json');
		foreach ($data as $key => $value) {

			// if ($key == 25) {
			// 	break;
			// }

			if ($key < 75) {
				continue;
			}
			if ($key == 100) {
				break;
			}

			if (isset($value['assignedDiscount'])) {

				$userObject = $this->findIdFromCollection1('channelpartners.json', 'users', $value['_id']['$oid'], 'email', 'emailId', 'email');

				if ($userObject) {

					foreach ($value['assignedDiscount'] as $keyD => $valueD) {

						$dataProducts = $this->dataFromCollection('products.json');
						$product_inventory_id = 0;

						foreach ($dataProducts as $keyP => $valueP) {

							if ($valueP['_id']['$oid'] == $valueD['productId']['$oid']) {

								$brandObject = $this->findIdFromCollection('submasters.json', 'data_master', $valueP['productBrand']['$oid'], 'code', 'code');
								$codeOBject = $this->findIdFromCollection('submasters.json', 'data_master', $valueP['productType']['$oid'], 'code', 'code');

								$ProductInventory = ProductInventory::where('product_brand_id', $brandObject->id)->where('product_code_id', $codeOBject->id)->first();
								if ($ProductInventory) {
									$product_inventory_id = $ProductInventory->id;

								} else {
									continue;
								}

							}

						}

						if ($product_inventory_id != 0) {

							$UserDiscountObject = UserDiscount::where('user_id', $userObject->id)->where('product_inventory_id', $product_inventory_id)->first();

							if (!$UserDiscountObject) {

								$UserDiscount = new UserDiscount();
								$UserDiscount->user_id = $userObject->id;
								$UserDiscount->product_inventory_id = $product_inventory_id;
								$UserDiscount->discount_percentage = $valueD['discount'];
								$UserDiscount->save();

							} else {
								echo "Discount already there";
								echo "<br>";
							}

						}

					}
				}
			}

		}

	}
}