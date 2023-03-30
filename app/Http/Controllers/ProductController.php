<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productQuery = Product::query()->get();

        foreach ($productQuery as $product) {
            $exp_date = $product['exp_date'];
            if (now() > $exp_date){
                $product->delete();
            }
        }
        $productQuery = Product::query()->select('name' , 'photo')->get();
        return response()->json([
            'success' => true,
            'message' => 'Indexed successfuly!',
            'data'=> $productQuery,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $rule = [
        'name'=> ['required','string','max:255'],
        'price'=> ['required','numeric','min:1'],
        'description'=> ['required','string','max:255'],
        'exp_date' => ['required','date','date_format:Y-m-d','after:tomorrow'],
        'quantity' => ['required','numeric','min:1'],
        'category_id' => ['required','numeric','min:1'],
        'list_discounts' =>['array','nullable'],
       ]; 

       $user = User::find(auth()->id());

       $input['name'] = $request->input('name');
       $input['price'] = $request->input('price');
       $input['description'] = $request->input('description');
       $input['exp_date'] = $request->input('exp_date');
       $input['quantity'] = $request->input('quantity');
       $input['category_id'] = $request->input('category_id');
       $user_id = $user['id'];
       $input['list_discounts'] = $request->input('list_discounts');


       $validator = validator::make($input,$rule);

       if($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->all(),
        ], 422);
       }

       $image_name = 'default.png';

       if ($request->hasFile('image')) {
           $destination_path = 'public/images/users';
           $image = $request->file('image');

           $image_name = implode('.', [
               md5_file($image->getPathname()),
               $image->getClientOriginalExtension()
           ]);

           $path = $request->file('image')->storeAs($destination_path, $image_name);
       }

  

       $product = $user->products()->create([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'exp_date' => $request->exp_date,
            'photo' => $image_name,
            'quantity' => $request->quantity,
            'category_id' => $request->category_id,

       ]);
       if(!empty($list_discounts)){
       foreach($list_discounts as $discount){
        $product->discounts()->create([
            'disc_date' => $discount['disc_date'],
            'disc_val' => $discount['disc_val']
        ]);
        }
    }
       $product_id = $product['id'];
       $product = $product->with('user' , 'discounts')->find($product_id);

       return response()->json([
        'success' => true,
        'message' => 'Created successfuly!',
        'data' => $product,
       ], 200);
       
    }

    public function search(Request $request){
        $rule = [
            'name'=> ['required','string','max:255','nullable'],
            'exp_date'=> ['required','date','nullable'],
            'category_id' => ['required','numeric','min:1','nullable'],
        ];

        $validator = Validator::make($request->query(),$rule);

        if($validator->fails()){
            return response()->json([
                'success' =>false,
                'message' => $validator->errors()->all(),
            ],422);
        }

        $name = $request->query('name');
        $exp_date = $request->query('exp_date');
        $category_id = $request->query('category_id');

        $productQuery = Product::query();

        if($name){
            $productQuery = $productQuery->where('name','LIKE','%'.$name.'%');
        }

        if($category_id){
            $productQuery = $productQuery->where('category_id', $category_id);
        }

        if($exp_date){
            $productQuery = $productQuery->where('exp_date', $exp_date);
       
        }

        $productQuery = $productQuery->get();

        return response()->json([
            'success' => true,
            'message' =>  $productQuery,
        ], 200);
    }
    /**
     * Display the specified resource.
     */
    public function show(Product $product , $id)
    {
        $product = Product::query()->find($id);

        if(!$product){
            return response()->json([
                'success' => false,
                'message' => 'product not found',
            ],404);
        }

        $discounts = $product->discounts()->get();
        $maxDiscount=null;

        foreach($discounts as $discount){
            if($discount['date']<= now()){
                $maxDiscount = $discount;
            }
        }

        if(!is_null($maxDiscount)){
            $discount_val = ($product->price * $maxDiscount['disc_val'])/100;
            $product->current_price = $product->price - $discount_val;
            $product->current_discount = $maxDiscount['disc_val'];
            $product->save();
        }
        else{
            $product->current_price = $product->price;
            $product->current_discount = 0;
            $product->save();
        }

        $product = $product->with('user' , 'category' ,'discounts')->find($id);

        return response()->json([
            'success' => true,
            'message' => 'Showed successfuly!',
            'data' => $product,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product , $id)
    {
        $product = Product::query()->find($id);

        if(auth()->id() != $product->user_id){
            return response()->json([
                'message' => 'Access Denied !'
            ],403);
        }

        if(is_null($product)){
            return response()->json([
                'success' =>false,
                'message' =>'product not found !',
            ],404);
        }

        $rule = [
            'name'=> ['string','max:255','nullable'],
            'price'=> ['numeric','min:1','nullable'],
            'description'=> ['string','max:255','nullable'],
            'quantity' => ['numeric','min:1','nullable'],
            'photo' => ['url','nullable']
        ];

          
        $name = $request->input('name');
        $description = $request->input('description');
        $price = $request->input('price');
        $quantity = $request->input('quantity');
        $photo = $request->input('photo');
        
       
        $validator = Validator::make($request->input() , $rule);

        
        if ($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->all(),
            ], 422);
        }

        if ($name){
            $product->update([
                'name' => $name,
            ]);
        }
        if ($description){
            $product->update([
                'description' => $description,
            ]);
        }
        if ($price){
            $product->update([
                'price' => $price,
            ]);
        }
        if ($photo){
            $product->update([
                'photo' => $photo,
            ]);
        }
       if ($quantity){
            $product->update([
                'quantity' => $quantity,
            ]);
        }
 
        return response()->json([
            'success' => true,
            'message' => 'Updated successfuly!',
            'data' => $product,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product , $id)
    {
        $product = Product::query()->find($id);

        if(Auth::id()!= $product->user_id){
            return response()->json([
                'message' => 'Access Denied !'
            ],403);
        }
        
        if($product){
            $product->delete();

            return response()->json([
                'success'=>true,
                'message'=>'Destroyed successfuly !',
            ],200);
        }
        else{
            return response()->json([
                'success'=>false,
                'message'=>'product not exist !',
            ],404);
        }

    }

    public function myProducts(Request $request)
    {
        $user = User::find(auth()->id());

        $products = $user->products()->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ], 200);
    }
}
