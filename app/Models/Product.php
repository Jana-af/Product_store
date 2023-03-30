<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $fillable = [
        'name',
        'price',
        'current_price',
        'current_discount',
        'description',
        'exp_date',
        'photo',
        'quantity',
        'category_id',
        'user_id',
    ];

    protected $hidden =['category_id','user_id'];

    public function discounts(){
        return $this->hasmany(Discount::class,'product_id')->orderBy('disc_date');
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function category(){
        return $this->belongsTo(Category::class,'category_id');
    }
}
