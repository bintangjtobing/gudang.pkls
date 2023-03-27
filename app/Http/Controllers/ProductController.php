<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductsWip;
use App\Models\Shelf;
use DNS1D;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class ProductController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');

		if(!Session::has('selected_warehouse_id')){
			$warehouse = DB::table('warehouse')->first();
			Session::put('selected_warehouse_id', $warehouse->warehouse_id);
			Session::put('selected_warehouse_name', $warehouse->warehouse_name);
		}
	}

	public function products(Request $req){
		$sort           = $req->sort;
		$search         = $req->q;
		$cat            = $req->category;

		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}

		$products = DB::table('products')
		->leftJoin("categories", "products.category_id", "=", "categories.category_id")
		->leftJoin("shelf", "products.Shelf_id", "=", "shelf.shelf_id")
		->select("products.*", "categories.*", "shelf.*")
		->where('products.status', 1);
		
		if(!empty($cat)){
			$products = $products->orWhere([["categories.category_id", $cat], ["products.warehouse_id", $warehouse_id]]);
		}
		
		if(!empty($search)){
			$products = $products->orWhere([["products.product_name", "LIKE", "%".$search."%"], ["products.warehouse_id", $warehouse_id]])
			->orWhere([["products.product_code", "LIKE", "%".$search."%"], ["products.warehouse_id", $warehouse_id]]);
		}
		
		if(empty($sort)){
			$products = $products->where("products.warehouse_id", $warehouse_id)->orderBy("products.product_id", "desc")->get();
		} else if($sort == "desc"){
			$products = $products->where("products.warehouse_id", $warehouse_id)->orderBy("products.product_code", "desc")->get();
		} else {
			$products = $products->where("products.warehouse_id", $warehouse_id)->orderBy("products.product_code", "asc")->get();
		}

		foreach($products as $p){
			$totalStockIn   = DB::table('stock')->where([["product_id", $p->product_id], ["type", 1]])->sum("product_amount");
			$totalStockOut  = DB::table('stock')->where([["product_id", $p->product_id], ["type", 0]])->sum("product_amount");
			$availableStock = $totalStockIn-$totalStockOut;
			$p->product_amount = $availableStock;
		}

		$warehouse = $this->getWarehouse();
		$shelf = Shelf::where('warehouse_id',$warehouse_id)->pluck('shelf_name','shelf_id');
		return View::make("products")->with(compact("products", "warehouse","shelf"));
	}

	public function stockIn(){
		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}

		$category = Category::where('warehouse_id',$warehouse_id)->pluck('category_name','category_id');
		$shelf = Shelf::where('warehouse_id',$warehouse_id)->pluck('shelf_name','shelf_id');
		return view('scan_in', compact('category','shelf'));
	}

	public function stockInStore(Request $request){
		
		DB::beginTransaction();
		try {
	
			if(Session::has('selected_warehouse_id')){
				$warehouse_id = Session::get('selected_warehouse_id');
			} else {
				$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
			}
			$product = Product::create([
				'product_name' => $request->product_name,
				'product_code' => $request->product_code,
				'ukuran' => $request->ukuran,
				'category_id' => $request->category,
				'shelf_id' => $request->shelf,
				'warehouse_id' => $warehouse_id,
				'user_id' => auth()->user()->id,
				'kilogram' => $request->kilogram,
			]);
	
			$product_id = $product->product_id;
			$shelf = $product->shelf_id;

			
			$amount = $request->pamount;
			if(!empty($request->pamount)){
				$data = [
					"user_id"           => Auth::user()->id,
					"warehouse_id"      => $warehouse_id,
					"product_id"        => $product->product_id,
					"product_amount"    => $amount,
					"shelf_id"          => $request->shelf,
					"type"              => 1,
					"datetime"          => date("Y-m-d H:i:s"),
					"kilogram"          => $request->kilogram,
					"ukuran"          => $request->ukuran,
				];

				$totalStockIn   = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["shelf_id", $shelf], ["type", 1]])->sum("product_amount");
				$totalStockOut  = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["shelf_id", $shelf], ["type", 0]])->sum("product_amount");
				$availableStock = $totalStockIn-$totalStockOut;

				$endingTotalStockIn   = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["type", 1]])->sum("product_amount");
				$endingTotalStockOut  = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["type", 0]])->sum("product_amount");
				$endingAmount = $endingTotalStockIn-$endingTotalStockOut;


				$data["ending_amount"] = $endingAmount+$amount;
				
				// if($shelf != $product->Shelf_id && $availableStock == 0 || $product->Shelf_id == 0){
				// 	$product->update([
				// 		'shelf_id' => $request->shelf_id
				// 	]);
				// }

				$updateStock = DB::table('stock')->insertGetId($data);

			} else {

				session()->flash('error', "Amount belum diisi!");
				return back();

			}
		} catch (\Exception $e) {
			DB::rollback();
			session()->flash('error', $e->getMessage());
			dd($e->getMessage());
			return back();
		}catch (\Throwable $e) {
			DB::rollback();
			dd($e->getMessage());
			session()->flash('error', $e->getMessage());
			throw $e;
		}

		DB::commit();
		session()->flash('success', "Product berhasil ditambahkan.");

		return back();
	}

	public function stockOut(){
		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}

		$category = Category::where('warehouse_id',$warehouse_id)->pluck('category_name','category_id');
		$shelf = Shelf::where('warehouse_id',$warehouse_id)->pluck('shelf_name','shelf_id');
		return view('scan_out', compact('category','shelf'));
	}

	public function stockOutStore(Request $request){
		DB::beginTransaction();
		try {


			if(Session::has('selected_warehouse_id')){
				$warehouse_id = Session::get('selected_warehouse_id');
			} else {
				$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
			}
			$product = DB::table('products')
			->leftJoin("categories", "products.category_id", "=", "categories.category_id")
			->leftJoin("stock", "products.product_id", "=", "stock.product_id")
                    // ->select("products.*", "categories.*", "products.product_id as product_id")
			->where('products.product_id',$request->product_id)
			->selectRaw('products.*, categories.*, stock.*')
			->first();

			$product_id = $product->product_id;
			$shelf = $product->shelf_id;
			$amount = $request->pamount;
			if(!empty($request->pamount)){
				$data = [
					"user_id"           => Auth::user()->id,
					"warehouse_id"      => $warehouse_id,
					"product_id"        => $product->product_id,
					"product_amount"    => $request->pamount,
					"shelf_id"          => $shelf,
					"type"              => 0,
					"datetime"          => date("Y-m-d H:i:s"),
					"pembeli"          => $request->pembeli,
					"kilogram"          => $product->kilogram,
					"ukuran"          => $request->ukuran,
				];

				$totalStockIn   = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["shelf_id", $shelf], ["type", 1]])->sum("product_amount");
				$totalStockOut  = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["shelf_id", $shelf], ["type", 0]])->sum("product_amount");
				$availableStock = $totalStockIn-$totalStockOut;

				$endingTotalStockIn   = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["type", 1]])->sum("product_amount");
				$endingTotalStockOut  = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["type", 0]])->sum("product_amount");
				$endingAmount = $endingTotalStockIn-$endingTotalStockOut;


				if($amount > $availableStock){
					session()->flash('error', "Jumlah stock out melebihi jumlah stock yang tersedia di shelf yang dipilih!!");
					return back();
				} else {
					$data["ending_amount"] = $endingAmount-$amount;
				}
				
				$updateStock = DB::table('stock')->insertGetId($data);
				
				if($data["ending_amount"] == 0 ){
					Product::where('product_id', $product->product_id)->update(['status' => 0]);	
				}

			} else {

				session()->flash('error', "Amount belum diisi!");
				return back();

			}
		} catch (\Exception $e) {
			DB::rollback();
			session()->flash('error', $e->getMessage());
			dd($e->getMessage());
			return back();
		}catch (\Throwable $e) {
			DB::rollback();
			dd($e->getMessage());
			session()->flash('error', $e->getMessage());
			throw $e;
		}

		DB::commit();
		session()->flash('success', "Stok berhasil diupdate !");

		return back();
	}

	public function products_wip(Request $req){
		$search = $req->q;
		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}

		$products = DB::table('products_wip')
		// ->leftJoin("products", "products_wip.product_id", "=", "products.product_id")
		->select("products_wip.*");
				
		$products = $products->where([["products_wip.status", 0], ["products_wip.warehouse_id", $warehouse_id]])->orderBy("products_wip.product_wip_id", "desc")->paginate(50);

		$warehouse = $this->getWarehouse();
		$shelf = Shelf::where('warehouse_id', $warehouse_id)->pluck('shelf_name','shelf_id');
		$category = Category::where('warehouse_id',$warehouse_id)->pluck('category_name','category_id');
		return View::make("products_wip")->with(compact("products", "warehouse", 'shelf','category'));

	}

	public function products_wip_history(Request $req){
		$search = $req->q;
		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}

		$products = DB::table('products_wip')
		->leftJoin("products", "products_wip.product_id", "=", "products.product_id")
		->leftJoin("shelf", "shelf.shelf_id", "=", "products.Shelf_id")
		->select("products_wip.*", "products.*","shelf.*");
		
		if(!empty($search)){
			$products = $products->orWhere([["products.product_name", "LIKE", "%".$search."%"], ["status", 1]])
			->orWhere([["products.product_code", "LIKE", "%".$search."%"], ["status", 1]]);
		}
		
		$products = $products->where([["products_wip.status", 1], ["products_wip.warehouse_id", $warehouse_id]])->orderBy("products_wip.date_out", "desc")->paginate(50);

		$warehouse = $this->getWarehouse();
		return View::make("products_wip_history")->with(compact("products", "warehouse"));
	}

	public function product_check(Request $req){
		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}

		$product = DB::table('products')->where([["product_code", $req->pcode], ["warehouse_id", $warehouse_id], ["status", 1]])->first();
		
		$result = ["status" => 0, "data" => null];

		if(!empty($product)){
			$result = ["status" => 1, "data" => $product];
		}
		
		return response()->json($result);
	}

	public function product_save(Request $req){
		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}

		$req->validate([
			'product_code'      => 'required|unique:products,product_code,'.$req->id.',product_id,warehouse_id,'.$warehouse_id.'|numeric',
			'product_name'      => 'required',
			'category'          => 'required|exists:categories,category_id',
			
		],
		[
			'product_code.required'     => 'Product Code belum diisi!',
			'product_code.numeric'      => 'Product Code harus berupa angka!',
			'product_code.unique'       => 'Product Code telah digunakan!',
			'product_name.required'     => 'Product Name belum diisi!',
			'category.required'         => 'Kategori belum dipilih!',
			'category.exists'           => 'Kategori tidak tersedia!',
		]);

		$data = [
			"user_id"           => Auth::user()->id,
			"warehouse_id"      => $warehouse_id,
			"product_code"      => $req->product_code,
			"product_name"      => $req->product_name,
			"category_id"       => $req->category,
			"kilogram"       => $req->kilogram,
			"Shelf_id"       => $req->shelf,
		];

		if(empty($req->id)){
			$add = DB::table('products')->insertGetId($data);

			if($add){
				$req->session()->flash('success', "Product berhasil ditambahkan.");
			} else {
				$req->session()->flash('error', "Product gagal ditambahkan!");
			}
		} else {
			$update = DB::table('products')->where("product_id", $req->id)->update($data);

			if($update){
				$req->session()->flash('success', "Product berhasil diubah.");
			} else {
				$req->session()->flash('error', "Product gagal diubah!");
			}
		}
		
		return redirect()->back();
	}

	public function product_wip_save(Request $req){
		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}
		// dd($req);
		// $req->validate([
		// 	'product_code'      => 'required|exists:products,product_code|numeric',
		// 	'product_amount'    => 'required|numeric',
			
		// ],
		// [
		// 	'product_code.required'     => 'Product Code belum diisi!',
		// 	'product_code.numeric'      => 'Product Code harus berupa angka!',
		// 	'product_code.exists'       => 'Product Code tidak ditemukan!',
		// 	'product_amount.required'   => 'Product Amount belum diisi!',
		// 	'product_amount.numeric'    => 'Product Amount harus berupa angka!',
		// ]);
		$products = ProductsWip::create([
			'warehouse_id' => $warehouse_id,
			'product_code' => $req->product_code,
			'product_name' => $req->product_name,
			'product_amount' => $req->product_amount,
			'date_in' => date("Y-m-d H:i:s"),
		]);

		// $product_id = DB::table('products')
		// ->where([["product_code", $req->product_code], ["warehouse_id", $warehouse_id]])
		// ->select("product_id")
		// ->first();

		// if(!empty($product_id)){
		// 	$product_id = $product_id->product_id;
		// 	$data = [
		// 		"product_id"        => $product_id,
		// 		"warehouse_id"      => $warehouse_id,
		// 		"product_amount"    => $req->product_amount,
		// 		"date_in"           => date("Y-m-d H:i:s"),
		// 	];

		// 	if(empty($req->id)){
		// 		$add = DB::table('products_wip')->insertGetId($data);

		// 		if($add){
		// 			$req->session()->flash('success', "Product berhasil ditambahkan.");
		// 		} else {
		// 			$req->session()->flash('error', "Product gagal ditambahkan!");
		// 		}
		// 	} else {
		// 		$update = DB::table('products_wip')->where([["product_wip_id", $req->id], ["warehouse_id", $warehouse_id]])->update($data);

		// 		if($update){
		// 			$req->session()->flash('success', "Product berhasil diubah.");
		// 		} else {
		// 			$req->session()->flash('error', "Product gagal diubah!");
		// 		}
		// 	}
		// } else {
		// 	$req->session()->flash('error', "Product tidak ditemukan!");
		// }
		$req->session()->flash('success', "Product berhasil ditambahkan.");
		return redirect()->back();
	}

	public function product_delete(Request $req){
		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}

		$del = DB::table('products')->where([["product_id", $req->id], ["warehouse_id", $warehouse_id]])->delete();

		if($del){
			$stock_id = DB::table('stock')->where([["product_id", $req->id], ["warehouse_id", $warehouse_id]])->first();
			if(!empty($stock_id)){
				$stock_id = $stock_id->stock_id;
				DB::table('stock')->where([["product_id", $req->id], ["warehouse_id", $warehouse_id]])->delete();
				DB::table('history')->where([["stock_id", $stock_id], ["warehouse_id", $warehouse_id]])->delete();
			}
			$req->session()->flash('success', "Product berhasil dihapus.");
		} else {
			$req->session()->flash('error', "Product gagal dihapus!");
		}

		return redirect()->back();
	}

	public function product_wip_delete(Request $req){
		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}

		$del = DB::table('products_wip')->where([["product_wip_id", $req->id], ["warehouse_id", $warehouse_id]])->delete();

		if($del){
			$req->session()->flash('success', "Product berhasil dihapus.");
		} else {
			$req->session()->flash('error', "Product gagal dihapus!");
		}

		return redirect()->back();
	}

	public function product_wip_complete(Request $req){
		$wip_id     = $req->wip_id;
		$amount     = $req->amount;
		$kilogram     = $req->kilogram;
		$category     = $req->category;
		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}

		$req->validate([
			'wip_id'      => 'required|exists:products_wip,product_wip_id',
			'amount'    => 'required|numeric',
			
		],
		[
			'wip_id.required'   => 'WIP ID tidak ditemukan!',
			'wip_id.exists'     => 'WIP ID tidak ditemukan!',
			'amount.required'   => 'Amount belum diisi!',
			'amount.numeric'    => 'Amount harus berupa angka!',
		]);
		$wip        = DB::table('products_wip')->select("*")->where([["product_wip_id", $wip_id], ["warehouse_id", $warehouse_id]])->first();
		// dd($req, $wip);

		if($amount <= $wip->product_amount){
            // $shelf      = DB::table('shelf')->where("warehouse_id", $warehouse_id)->select("shelf_id")->first();
			$shelf      = $req->shelf;
			if(!empty($shelf)){
                // $shelf = $shelf->shelf_id;
				$wipComplete = null;

				if(count(array($wip)) > 0){
					$product = Product::create([
						'product_name' => $wip->product_name,
						'product_code' => $wip->product_code,
						// 'ukuran' => $request->ukuran,
						'category_id' => $req->category,
						'shelf_id' => $req->shelf,
						'warehouse_id' => $warehouse_id,
						'user_id' => auth()->user()->id,
						'kilogram' => $req->kilogram,
					]);
					$data = new Request([
						"product_id"    => $product->product_id,
						"warehouse_id"  => $warehouse_id,
						"amount"        => $amount,
						"shelf"         => $shelf,
						"type"          => 1,
						"kilogram"      => $kilogram,
					]);

					$wipComplete = $this->product_stock($data);
				}

				if($wipComplete){
					if($amount == $wip->product_amount){
						$data = [
							"product_id"  => $product->product_id,
							"date_out"  => date('Y-m-d H:i:s'),
							"kilogram"  => $kilogram,
							"status"    => 1,
						];
						DB::table('products_wip')->where([["product_wip_id", $wip_id], ["warehouse_id", $warehouse_id]])->update($data);
					} else {
						$data = [
							"product_id"        => $wip->product_id,
							"warehouse_id"      => $warehouse_id,
							"product_amount"    => $amount,
							"date_in"           => $wip->date_in,
							"date_out"          => date('Y-m-d H:i:s'),
							"status"            => 1,
						];
						$insertNew = DB::table('products_wip')->insertGetId($data);

						if($insertNew){
							$curAmount = $wip->product_amount - $amount;
							DB::table('products_wip')->where([["product_wip_id", $wip_id], ["warehouse_id", $warehouse_id]])->update(["product_amount" => $curAmount]);
						}
					}
					$req->session()->flash('success', "Product telah dipindahkan ke Products List.");
				} else {
					$req->session()->flash('error', "Terjadi kesalahan! Mohon coba kembali!");
				}
			} else {
				$req->session()->flash('error', "Shelf belum dibuat!");
			}
		} else {
			$req->session()->flash('error', "Amount tidak tersedia!");
		}
		return redirect()->back();
	}

	public function product_stock(Request $req){
		$product_id = $req->product_id;
		$amount     = $req->amount;
		$shelf      = $req->shelf;
		$type       = $req->type;
		$kilogram       = $req->kilogram;

		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}
		$prod = DB::table('products')
		->leftJoin("categories", "products.category_id", "=", "categories.category_id")
		->leftJoin("stock", "products.product_id", "=", "stock.product_id")
                    // ->select("products.*", "categories.*", "products.product_id as product_id")
		->where('products.product_id',$product_id)
		->first();
		// dd($req, $prod);
		
		// if($req->type == 1){
		// 	$shelf      = $prod->shelf_id;
		// }else{
		// 	$shelf      = $prod->shelf_id;
		// }

		if(!empty($amount)){
            // if(!empty($req->shelf)){
			$data = [
				"user_id"           => Auth::user()->id,
				"warehouse_id"      => $warehouse_id,
				"product_id"        => $product_id,
				"product_amount"    => $amount,
				"shelf_id"          => $shelf,
				"type"              => $type,
				"datetime"          => date("Y-m-d H:i:s"),
				"kilogram"              => $kilogram,
			];

			$totalStockIn   = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["shelf_id", $shelf], ["type", 1]])->sum("product_amount");
			$totalStockOut  = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["shelf_id", $shelf], ["type", 0]])->sum("product_amount");
			$availableStock = $totalStockIn-$totalStockOut;

			$endingTotalStockIn   = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["type", 1]])->sum("product_amount");
			$endingTotalStockOut  = DB::table('stock')->where([["warehouse_id", $warehouse_id], ["product_id", $product_id], ["type", 0]])->sum("product_amount");
			$endingAmount = $endingTotalStockIn-$endingTotalStockOut;

			if($type == 0){
				if($amount > $availableStock){
					$result = ["status" => 0, "message" => "Jumlah stock out melebihi jumlah stock yang tersedia di shelf yang dipilih!"];
					goto resp;
				} else {
					$data["ending_amount"] = $endingAmount-$amount;
				}
			} else {
				$data["ending_amount"] = $endingAmount+$amount;
			}

			$updateStock = DB::table('stock')->insertGetId($data);

			if($updateStock){
				$result = ["status" => 1, "message" => "Stok berhasil diupdate."];
			} else {
				$result = ["status" => 0, "message" => "Stok gagal diupdate! Mohon coba kembali!"];
			}
            // } else {
            //     $result = ["status" => 0, "message" => "Shelf belum dipilih!"];
            // }
		} else {
			$result = ["status" => 0, "message" => "Amount belum diisi!"];
		}
		
		resp:
		return response()->json($result);
	}

	public function product_stock_history(Request $req){
		$search = $req->search;
		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}

		$history = DB::table('stock')
		->leftJoin("products", "stock.product_id", "=", "products.product_id")
		->leftJoin("shelf", "stock.shelf_id", "=", "shelf.shelf_id")
		->leftJoin("users", "stock.user_id", "=", "users.id")
		->select("stock.*", "products.product_code", "products.product_name", "shelf.shelf_name", "users.name")
		->where("stock.warehouse_id", $warehouse_id)
		->orderBy("stock.stock_id", "desc");

		if(!empty($search)){
			$history = $history->orWhere([["products.product_code", "LIKE", "%".$search."%"], ["products.warehouse_id", $warehouse_id]])
			->orWhere([["products.product_name", "LIKE", "%".$search."%"], ["products.warehouse_id", $warehouse_id]])
			->orWhere([["shelf.shelf_name", "LIKE", "%".$search."%"], ["products.warehouse_id", $warehouse_id]]);
		}

		$history = $history->get();

		$warehouse = $this->getWarehouse();
		return View::make("stock_history")->with(compact("history", "warehouse"));
	}

	public function categories(Request $req){
		$search = $req->q;
		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}

		$categories = DB::table('categories')->select("*");

		if(!empty($search)){
			$categories = $categories->where([["category_name", "LIKE", "%".$search."%"], ["warehouse_id", $warehouse_id]]);
		}

		if($req->format == "json"){
			$categories = $categories->where("warehouse_id", $warehouse_id)->get();

			return response()->json($categories);
		} else {
			$categories = $categories->where("warehouse_id", $warehouse_id)->paginate(50);
			$warehouse = $this->getWarehouse();
			return View::make("categories")->with(compact("categories", "warehouse"));
		}
	}

	public function categories_save(Request $req){
		$category_id = $req->category_id;
		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}

		$req->validate([
			'category_name'      => ['required']
			
		],
		[
			'category_name.required'     => 'Nama Kategori belum diisi!',
		]);

		$data = [
			"warehouse_id"       => $warehouse_id,
			"category_name"      => $req->category_name
		];

		if(empty($category_id)){
			$add = DB::table('categories')->insertGetId($data);

			if($add){
				$req->session()->flash('success', "Kategori baru berhasil ditambahkan.");
			} else {
				$req->session()->flash('error', "Kategori baru gagal ditambahkan!");
			}
		} else {
			$edit = DB::table('categories')->where([["category_id", $category_id], ["warehouse_id", $warehouse_id]])->update($data);

			if($edit){
				$req->session()->flash('success', "Kategori berhasil diubah.");
			} else {
				$req->session()->flash('error', "Kategori gagal diubah!");
			}
		}
		
		return redirect()->back();
	}

	public function categories_delete(Request $req){
		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}

		$del = DB::table('categories')->where([["category_id", $req->delete_id], ["warehouse_id", $warehouse_id]])->delete();

		if($del){
			DB::table('products')->where([["category_id", $req->delete_id], ["warehouse_id", $warehouse_id]])->update(["category_id" => null]);
			$req->session()->flash('success', "Kategori berhasil dihapus.");
		} else {
			$req->session()->flash('error', "Kategori gagal dihapus!");
		}

		return redirect()->back();
	}

	public function shelf(Request $req){
		$product_id = $req->product_id;
		$shelf = DB::table('shelf');
		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}

		if($req->format == "json"){
			if(!empty($product_id)){
				$shelf = $shelf->join("stock", "shelf.shelf_id", "stock.shelf_id")
				->where([["stock.product_id", $product_id], ["stock.warehouse_id", $warehouse_id]])->groupBy("shelf_id");
				$result = [];
				$shelf = $shelf->select("shelf.*", "stock.product_amount")->get();
				foreach($shelf as $s){
					$totalStockIn   = DB::table('stock')->where([["stock.warehouse_id", $warehouse_id], ["product_id", $product_id], ["shelf_id", $s->shelf_id], ["type", 1]])->sum("product_amount");
					$totalStockOut  = DB::table('stock')->where([["stock.warehouse_id", $warehouse_id], ["product_id", $product_id], ["shelf_id", $s->shelf_id], ["type", 0]])->sum("product_amount");
					$availableStock = $totalStockIn-$totalStockOut;
					if($availableStock > 0){
						$s->product_amount = $availableStock;
						$result[] = $s;
					}
				}
			} else {
				$result = $shelf->select("shelf.*")->where('warehouse_id', $warehouse_id)->get();
			}
			return response()->json($result);
		} else {
			$shelf = $shelf->where("warehouse_id", $warehouse_id)->paginate(50);
			if(Auth::user()->role == 0){
				$warehouse = $this->getWarehouse();
				return View::make("shelf")->with(compact("shelf", "warehouse"));
			} else {
				abort(403);
			}
		}
	}

	public function shelf_save(Request $req){
		$shelf_id = $req->shelf_id;
		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}

		$req->validate([
			'shelf_name'      => ['required']
			
		],
		[
			'shelf_name.required'     => 'Shelf Name belum diisi!',
		]);

		$data = [
			"warehouse_id"    => $warehouse_id,
			"shelf_name"      => $req->shelf_name
		];

		if(empty($shelf_id)){
			$add = DB::table('shelf')->insertGetId($data);

			if($add){
				$req->session()->flash('success', "Shelf baru berhasil ditambahkan.");
			} else {
				$req->session()->flash('error', "Shelf baru gagal ditambahkan!");
			}
		} else {
			$edit = DB::table('shelf')->where([["shelf_id", $shelf_id], ["warehouse_id", $warehouse_id]])->update($data);

			if($edit){
				$req->session()->flash('success', "Shelf berhasil diubah.");
			} else {
				$req->session()->flash('error', "Shelf gagal diubah!");
			}
		}
		
		return redirect()->back();
	}

	public function shelf_delete(Request $req){
		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}

		$del = DB::table('shelf')->where([["shelf_id", $req->delete_id], ["warehouse_id", $warehouse_id]])->delete();

		if($del){
			DB::table('stock')->where([["shelf_id", $req->delete_id], ["warehouse_id", $warehouse_id]])->delete();
			$req->session()->flash('success', "Shelf berhasil dihapus.");
		} else {
			$req->session()->flash('error', "Shelf gagal dihapus!");
		}

		return redirect()->back();
	}

	public function generateBarcode(Request $req){
		$code       = $req->code;
		$print      = $req->print;
		$barcodeB64 = DNS1D::getBarcodePNG("".$code."", 'C128', 2, 81, array(0,0,0), true);

		if(!empty($print) && $print == true){
			return View::make("barcode_print")->with("barcode", $barcodeB64);
		} else {
			$barcode    = base64_decode($barcodeB64);
			$image      = imagecreatefromstring($barcode);
			$barcode    = imagepng($image);
			imagedestroy($image);

			return response($barcode)->header('Content-type','image/png');
		}
	}

	public function warehouse(Request $req){
		$search = $req->q;

		$warehouse = DB::table('warehouse')->select("*");

		if(!empty($search)){
			$warehouse = $warehouse->where("username", "LIKE", "%".$search."%")
			->orWhere("name", "LIKE", "%".$search."%");
		}

		if($req->format == "json"){
			$warehouse = $warehouse->get();

			return response()->json($warehouse);
		} else {
			$warehouse = $warehouse->paginate(50);

			return View::make("warehouse")->with(compact("warehouse"));
		}
	}

	public function getWarehouse(){
		$warehouse = DB::table('warehouse')->select("*")->get();
		return $warehouse;
	}

	public function warehouse_select(Request $req){
		$req->validate([
			'warehouse_id'      => 'exists:warehouse,warehouse_id',
			
		],
		[
			'warehouse_id.exists'     => 'Warehouse tidak ditemukan!',
		]);

		$warehouse = DB::table('warehouse')->where("warehouse_id", $req->warehouse_id)->first();
		if(!empty($warehouse)){
			$req->session()->put('selected_warehouse_id', $req->warehouse_id);
			$req->session()->put('selected_warehouse_name', $warehouse->warehouse_name);
		}
		return redirect()->back();
	}

	public function warehouse_save(Request $req){
		$warehouse_id = $req->warehouse_id;

		$req->validate([
			'name'      => 'required',
			
		],
		[
			'name.required'     => 'Fullname belum diisi!',
		]);

		$data = [
			"warehouse_name"  => $req->name,
		];

		if(empty($warehouse_id)){
			$add = DB::table('warehouse')->insertGetId($data);

			if($add){
				$req->session()->flash('success', "Warehouse baru berhasil ditambahkan.");
			} else {
				$req->session()->flash('error', "warehouse baru gagal ditambahkan!");
			}
		} else {
			$edit = DB::table('warehouse')->where("warehouse_id", $warehouse_id)->update($data);

			if($edit){
				$req->session()->flash('success', "Warehouse berhasil diubah.");
			} else {
				$req->session()->flash('error', "Warehouse gagal diubah!");
			}
		}
		
		return redirect()->back();
	}

	public function warehouse_delete(Request $req){
		$del = DB::table('warehouse')->where("warehouse_id", $req->delete_id)->delete();

		if($del){
			$req->session()->flash('success', "Warehouse berhasil dihapus.");
		} else {
			$req->session()->flash('error', "Warehouse gagal dihapus!");
		}

		return redirect()->back();
	}

	public function denah(Request $request){

		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}


		$shelf = Shelf::where('warehouse_id', $warehouse_id)->get();
		return view('denah', compact('shelf'));
	}

	public function denah_detail(Request $req, $code){
		$sort           = $req->sort;
		$search         = $req->q;
		$cat            = $req->category;

		if(Session::has('selected_warehouse_id')){
			$warehouse_id = Session::get('selected_warehouse_id');
		} else {
			$warehouse_id = DB::table('warehouse')->first()->warehouse_id;
		}

		$products = DB::table('products')
		->leftJoin("categories", "products.category_id", "=", "categories.category_id")
		->leftJoin("stock", "products.product_id", "=", "stock.product_id")
                    // ->select("products.*", "categories.*", "products.product_id as product_id")
		->where('stock.shelf_id',$code);
		

		if(!empty($cat)){
			$products = $products->orWhere([["categories.category_id", $cat], ["products.warehouse_id", $warehouse_id]]);
		}
		
		if(!empty($search)){
			$products = $products->where(function($q) use ($search,$warehouse_id){
				$q->orWhere([["products.product_name", "LIKE", "%".$search."%"], ["products.warehouse_id", $warehouse_id]])
				->orWhere([["products.product_code", "LIKE", "%".$search."%"], ["products.warehouse_id", $warehouse_id]]);
			});
            // $products = $products->Where([["products.product_name", "LIKE", "%".$search."%"], ["products.warehouse_id", $warehouse_id]]);
            //             ->Where([["products.product_code", "LIKE", "%".$search."%"], ["products.warehouse_id", $warehouse_id]]);

		}
		
		if(empty($sort)){
			$products = $products->where("products.warehouse_id", $warehouse_id)->orderBy("products.product_id", "desc")->groupBy('products.product_id')->paginate(50);
		} else if($sort == "desc"){
			$products = $products->where("products.warehouse_id", $warehouse_id)->orderBy("products.product_code", "desc")->groupBy('products.product_id')->paginate(50);
		} else {
			$products = $products->where("products.warehouse_id", $warehouse_id)->orderBy("products.product_code", "asc")->groupBy('products.product_id')->paginate(50);
		}

		foreach($products as $p){
			$totalStockIn   = DB::table('stock')->where([["product_id", $p->product_id], ["type", 1]])->sum("product_amount");
			$totalStockOut  = DB::table('stock')->where([["product_id", $p->product_id], ["type", 0]])->sum("product_amount");
			$availableStock = $totalStockIn-$totalStockOut;
			$p->product_amount = $availableStock;
		}
		
		$warehouse = $this->getWarehouse();
		return View::make("denah_detail")->with(compact("products", "warehouse"));
	}
}