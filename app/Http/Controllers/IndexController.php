<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Image;
use Auth;
use Session;
use App\Category;
use App\Products_Attributes;
use App\Product;
use App\SliderProducts;
use App\Products_Images;

class IndexController extends Controller
{
    public function index($id = null){
    	//In Ascending Order  (by Default)
    	$productsAll = Product::get();

    	//In Descending Order 
    	$productsAll = Product::OrderBy('id','DESC')->get();

    	//In Random Order 
    	$productsAll = Product::inRandomOrder()->get();

        //Getting Slider Products In Ascending Order  (by Default)
        $sliderproducts = SliderProducts::get();

        //Getting Slider Products In Descending Order 
        $sliderproducts = SliderProducts::OrderBy('id','DESC')->get();

        //Getting Slider Products In Random Order 
        $sliderproducts = SliderProducts::inRandomOrder()->get();


        //Get Product Details
        $productDetails = Product::where('id',$id)->first();
        $productDetails = json_decode(json_encode($productDetails));

        $relatedProducts = Product::where('id','!=',$id)->get();

    	//Get all categories and subcategories
    	$categories = Category::with('categories')->where(['parent_id'=>0])->get();
        // $categories = json_decode(json_encode($categories));

    	return view('index')->with(compact('productsAll','sliderproducts','categories_menu','productDetails','relatedProducts','categories'));
    }
    public function addSliderImages(Request $request){

    	if ($request->isMethod('post')) {
    		$data = $request->all();
    		// echo "<pre>";print_r($data); die;
    		if(empty($data['category_id'])){
    			return redirect()->back()->with('flash_message_error','Under Category is missing!');	
    		}
 			$product = new SliderProducts;

 			$product->category_id = $data['category_id'];
 			$product->product_name = $data['product_name'];
 			$product->product_code = $data['product_code'];
            if (!empty($data['description'])) {
                $product->description = $data['description'];
            }else{
                $product->description = ''; 
            }
 			$product->price =$data['price'];

 			//Upload Image
 			if ($request->hasFile('image')) {
 				$image_tmp = Input::file('image');
 				if ($image_tmp->isValid()) {
 					$extension = $image_tmp->getClientOriginalExtension();
    				$filename = rand(111,99999).'.'.$extension;
    				$large_image_path = 'images/backend_images/products/large/'.$filename;
    				$medium_image_path = 'images/backend_images/products/medium/'.$filename;
    				$small_image_path = 'images/backend_images/products/small/'.$filename;
    				// Resize Images
    				Image::make($image_tmp)->save($large_image_path);
    				Image::make($image_tmp)->resize(900,680)->save($medium_image_path);
    				Image::make($image_tmp)->resize(300,300)->save($small_image_path);
    				// Store image name in products table
    				$product->image = $filename;
 				}
 			}
            
 			$product->save();
 			return redirect()->back()->with('flash_message_success','Product Added Successfuly!');
    	}
    	$categories = Category::where(['parent_id'=>0])->get();
    	$categories_dropdown = "<option value='' selected disabled>Select</option>";
    	foreach($categories as $cat){
    		$categories_dropdown .= "<option value='".$cat->id."'>".$cat->category_name."</option>";
    		$sub_categories = Category::where(['parent_id'=>$cat->id])->get();
    		foreach ($sub_categories as $sub_cat) {
    			$categories_dropdown .= "<option value = '".$sub_cat->id."'>&nbsp;--&nbsp;".$sub_cat->category_name."</option>";
    		}
    	}

    	return view('admin.products.add_slider_images')->with(compact('categories_dropdown'));
    }
    public function viewProducts(Request $request){

        $products = SliderProducts::get();
        $products = json_decode(json_encode($products));
        // foreach($products as $key => $val){
        //     $category_name = Category::where(['id'=>$val->category_id])->first();
        //     $products[$key]->category_name = $category_name->category_name;
        // }
        // echo "<pre>"; print_r($products); die;
        return view('admin.products.view_slider_products')->with(compact('products'));
    }
}
//