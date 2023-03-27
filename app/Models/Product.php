<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
	protected $primaryKey = 'product_id';

	public $timestamps = false;

	protected $fillable = ['product_id','shelf_id','warehouse_id','user_id','product_code','product_name','kilogram','purchase_price', 'sale_price', 'category_id', 'ukuran']; 
}
