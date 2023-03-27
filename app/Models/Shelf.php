<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shelf extends Model
{
	use HasFactory;

	protected $table = 'shelf';
	protected $fillable = ['shelf_id','warehouse_id','shelf_name']; 
}
