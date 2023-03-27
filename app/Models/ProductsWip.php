<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsWip extends Model
{
	use HasFactory;
	protected $table = 'products_wip';
	protected $primaryKey = 'product_wip_id';
	public $timestamps = false;

	protected $fillable = ['product_wip_id','warehouse_id','product_code','product_amount','date_in','date_out','status','kilogram','product_name']; 
}
