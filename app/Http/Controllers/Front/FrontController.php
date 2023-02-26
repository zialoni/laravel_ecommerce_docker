<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use Crypt;


class FrontController extends Controller
{
    public function index(Request $request)
    {
     
        $result['home_categories']=DB::table('categories')
        ->where(['status'=>1])
        ->where(['is_home'=>1])
        ->get();

      


foreach($result['home_categories'] as $list){
    $result['home_categories_product'][$list->id]=
        DB::table('products')
        ->where(['status'=>1])
        ->where(['category_id'=>$list->id])
        ->get();

      

        foreach($result['home_categories_product'][$list->id] as $list1){
            $result['home_product_attr'][$list1->id]=
                DB::table('products_attr')
                ->leftJoin('sizes','sizes.id','=','products_attr.size_id')
                ->leftJoin('colors','colors.id','=','products_attr.color_id')
                ->where(['products_attr.products_id'=>$list1->id])
                ->get();

      
            
        }
}

$result['home_brand']=DB::table('brands')
->where(['status'=>1])
->where(['is_home'=>1])
->get();

$result['home_featured_product'][$list->id]=
DB::table('products')
->where(['status'=>1])
->where(['is_featured'=>1])
->get();

foreach($result['home_featured_product'][$list->id] as $list1){
$result['home_featured_product_attr'][$list1->id]=
DB::table('products_attr')
->leftJoin('sizes','sizes.id','=','products_attr.size_id')
->leftJoin('colors','colors.id','=','products_attr.color_id')
->where(['products_attr.products_id'=>$list1->id])
->get();

}

$result['home_tranding_product'][$list->id]=
DB::table('products')
->where(['status'=>1])
->where(['is_tranding'=>1])
->get();

foreach($result['home_tranding_product'][$list->id] as $list1){
$result['home_tranding_product_attr'][$list1->id]=
DB::table('products_attr')
->leftJoin('sizes','sizes.id','=','products_attr.size_id')
->leftJoin('colors','colors.id','=','products_attr.color_id')
->where(['products_attr.products_id'=>$list1->id])
->get();

}

$result['home_discounted_product'][$list->id]=
DB::table('products')
->where(['status'=>1])
->where(['is_discounted'=>1])
->get();

foreach($result['home_discounted_product'][$list->id] as $list1){
$result['home_discounted_product_attr'][$list1->id]=
DB::table('products_attr')
->leftJoin('sizes','sizes.id','=','products_attr.size_id')
->leftJoin('colors','colors.id','=','products_attr.color_id')
->where(['products_attr.products_id'=>$list1->id])
->get();

}

$result['home_banner']=DB::table('home_banners')
->where(['status'=>1])
->get();

return view('front.index',$result);
    }

    public function category(Request $request,$slug)
    {
        $sort="";
        $sort_txt="";
        $filter_price_start="";
        $filter_price_end="";
        $color_filter="";
        $colorFilterArr=[];
        if($request->get('sort')!==null){
            $sort=$request->get('sort');
        }    
        
        $query=DB::table('products');
        $query=$query->leftJoin('categories','categories.id','=','products.category_id');
        $query=$query->leftJoin('products_attr','products.id','=','products_attr.products_id');
        $query=$query->where(['products.status'=>1]);
        $query=$query->where(['categories.category_slug'=>$slug]);
        if($sort=='name'){
            $query=$query->orderBy('products.name','asc');
            $sort_txt="Product Name";
        }
        if($sort=='date'){
            $query=$query->orderBy('products.id','desc');
            $sort_txt="Date";
        }
        if($sort=='price_desc'){
            $query=$query->orderBy('products_attr.price','desc');
            $sort_txt="Price - DESC";
        }if($sort=='price_asc'){
            $query=$query->orderBy('products_attr.price','asc');
            $sort_txt="Price - ASC";
        }
        if($request->get('filter_price_start')!==null && $request->get('filter_price_end')!==null){
            $filter_price_start=$request->get('filter_price_start');
            $filter_price_end=$request->get('filter_price_end');

            if($filter_price_start>0 && $filter_price_end>0){
                $query=$query->whereBetween('products_attr.price',[$filter_price_start,$filter_price_end]);
            }

        } 
        if($request->get('color_filter')!==null){
            $color_filter=$request->get('color_filter');        
            $colorFilterArr=explode(":",$color_filter);
            $colorFilterArr=array_filter($colorFilterArr);
           
            $query=$query->where(['products_attr.color_id'=>$request->get('color_filter')]);
            
        }
        $query=$query->distinct()->select('products.*');
        $query=$query->get();
        $result['product']=$query;
        
        foreach($result['product'] as $list1){
           
            $query1=DB::table('products_attr');
            $query1=$query1->leftJoin('sizes','sizes.id','=','products_attr.size_id');
            $query1=$query1->leftJoin('colors','colors.id','=','products_attr.color_id');
            $query1=$query1->where(['products_attr.products_id'=>$list1->id]);
            $query1=$query1->get();

            $result['product_attr'][$list1->id]=$query1;

        }
        $result['colors']=DB::table('colors')
        ->where(['status'=>1])
        ->get();

        $result['categories_left']=DB::table('categories')
        ->where(['status'=>1])
        ->get();
        
        $result['slug']=$slug;
        $result['sort']=$sort;
        $result['sort_txt']=$sort_txt;
        $result['filter_price_start']=$filter_price_start;
        $result['filter_price_end']=$filter_price_end;
        $result['color_filter']=$color_filter;
        $result['colorFilterArr']=$colorFilterArr;
        
        return view('front.category',$result);
    }

public function product(Request $request,$slug)
    {
        $result['product']=
            DB::table('products')
            ->where(['status'=>1])
            ->where(['slug'=>$slug])
            ->get();

        foreach($result['product'] as $list1){
            $result['product_attr'][$list1->id]=
                DB::table('products_attr')
                ->leftJoin('sizes','sizes.id','=','products_attr.size_id')
                ->leftJoin('colors','colors.id','=','products_attr.color_id')
                ->where(['products_attr.products_id'=>$list1->id])
                ->get();
        }
        foreach($result['product'] as $list1){
            $result['product_images'][$list1->id]=
                DB::table('product_images')
                ->where(['product_images.products_id'=>$list1->id])
                ->get();
        }
        $result['related_product']=
            DB::table('products')
            ->where(['status'=>1])
            ->where('slug','!=',$slug)
            ->where(['category_id'=>$result['product'][0]->category_id])
            ->get();
        foreach($result['related_product'] as $list1){
            $result['related_product_attr'][$list1->id]=
                DB::table('products_attr')
                ->leftJoin('sizes','sizes.id','=','products_attr.size_id')
                ->leftJoin('colors','colors.id','=','products_attr.color_id')
                ->where(['products_attr.products_id'=>$list1->id])
                ->get();
        }
        
        return view('front.product',$result);
    }

    public function add_to_cart(Request $request)
    {
        if($request->session()->has('FRONT_USER_LOGIN')){
            $uid=$request->session()->get('FRONT_USER_ID');
            $user_type="Reg";
        }else{
            $uid=getUserTempId();
            $user_type="Not-Reg";
        }
        
        $size_id=$request->post('size_id');
        $color_id=$request->post('color_id');
        $pqty=$request->post('pqty');
        $product_id=$request->post('product_id');

        $result=DB::table('products_attr')
            ->select('products_attr.id')
            ->leftJoin('sizes','sizes.id','=','products_attr.size_id')
            ->leftJoin('colors','colors.id','=','products_attr.color_id')
            ->where(['products_id'=>$product_id])
            ->where(['sizes.size'=>$size_id])
            ->where(['colors.color'=>$color_id])
            ->get();
        $product_attr_id=$result[0]->id;


        $check=DB::table('cart')
            ->where(['user_id'=>$uid])
            ->where(['user_type'=>$user_type])
            ->where(['product_id'=>$product_id])
            ->where(['product_attr_id'=>$product_attr_id])
            ->get();
            if(isset($check[0])){
                $update_id=$check[0]->id;
                if($pqty==0){
                    DB::table('cart')
                        ->where(['id'=>$update_id])
                        ->delete();
                    $msg="removed";
                }else{
                    DB::table('cart')
                        ->where(['id'=>$update_id])
                        ->update(['qty'=>$pqty]);
                    $msg="updated";
                }
                
            }else{
                $id=DB::table('cart')->insertGetId([
                    'user_id'=>$uid,
                    'user_type'=>$user_type,
                    'product_id'=>$product_id,
                    'product_attr_id'=>$product_attr_id,
                    'qty'=>$pqty,
                    'added_on'=>date('Y-m-d h:i:s')
                ]);
                $msg="added";
            }

            $result=DB::table('cart')
            ->leftJoin('products','products.id','=','cart.product_id')
            ->leftJoin('products_attr','products_attr.id','=','cart.product_attr_id')
            ->leftJoin('sizes','sizes.id','=','products_attr.size_id')
            ->leftJoin('colors','colors.id','=','products_attr.color_id')
            ->where(['user_id'=>$uid])
            ->where(['user_type'=>$user_type])
            ->select('cart.qty','products.name','products.image','sizes.size','colors.color','products_attr.price','products.slug','products.id as pid','products_attr.id as attr_id')
            ->get();    
        return response()->json(['msg'=>$msg,'data'=>$result,'totalItem'=>count($result)]);
    }

    public function cart(Request $request)
    {
        if($request->session()->has('FRONT_USER_LOGIN')){
            $uid=$request->session()->get('FRONT_USER_ID');
            $user_type="Reg";
        }else{
            $uid=getUserTempId();
            $user_type="Not-Reg";
        }
        $result['list']=DB::table('cart')
            ->leftJoin('products','products.id','=','cart.product_id')
            ->leftJoin('products_attr','products_attr.id','=','cart.product_attr_id')
            ->leftJoin('sizes','sizes.id','=','products_attr.size_id')
            ->leftJoin('colors','colors.id','=','products_attr.color_id')
            ->where(['user_id'=>$uid])
            ->where(['user_type'=>$user_type])
            ->select('cart.qty','products.name','products.image','sizes.size','colors.color','products_attr.price','products.slug','products.id as pid','products_attr.id as attr_id')
            ->get();
        return view('front.cart',$result);
    }
    public function search(Request $request,$str)
    {
        $result['product']=
            $query=DB::table('products');
            $query=$query->leftJoin('categories','categories.id','=','products.category_id');
            $query=$query->leftJoin('products_attr','products.id','=','products_attr.products_id');
            $query=$query->where(['products.status'=>1]);
            $query=$query->where('name','like',"%$str%");
            $query=$query->orwhere('model','like',"%$str%");
            $query=$query->orwhere('short_desc','like',"%$str%");
            $query=$query->orwhere('desc','like',"%$str%");
            $query=$query->orwhere('keywords','like',"%$str%");
            $query=$query->orwhere('technical_specification','like',"%$str%") ;
            $query=$query->distinct()->select('products.*');
            $query=$query->get();
            $result['product']=$query;
            
            foreach($result['product'] as $list1){
               
                $query1=DB::table('products_attr');
                $query1=$query1->leftJoin('sizes','sizes.id','=','products_attr.size_id');
                $query1=$query1->leftJoin('colors','colors.id','=','products_attr.color_id');
                $query1=$query1->where(['products_attr.products_id'=>$list1->id]);
                $query1=$query1->get();
                $result['product_attr'][$list1->id]=$query1;
            }
        
        return view('front.search',$result);
    }

    public function registration(Request $request)
    {
        if($request->session()->has('FRONT_USER_LOGIN')!=null){
            return redirect('/');
        }
        $result=[];
        return view('front.registration',$result);
    }
    
    public function registration_process(Request $request)
    {
       $valid=Validator::make($request->all(),[
            "name"=>'required',
            "email"=>'required|email|unique:customers,email',
            "password"=>'required',
            "mobile"=>'required|numeric|digits:10',
       ]);

       if(!$valid->passes()){
            return response()->json(['status'=>'error','error'=>$valid->errors()->toArray()]);
       }else{
            $arr=[
                "name"=>$request->name,
                "email"=>$request->email,
                "password"=>Crypt::encrypt($request->password),
                "mobile"=>$request->mobile,
                "status"=>1,
                "created_at"=>date('Y-m-d h:i:s'),
                "updated_at"=>date('Y-m-d h:i:s')
            ];
            $query=DB::table('customers')->insert($arr);
            if($query){
                return response()->json(['status'=>'success','msg'=>"Registration successfully"]);
            }

       }
    }

    public function login_process(Request $request)
    {

        $result=DB::table('customers')  
            ->where(['email'=>$request->str_login_email])
            ->get(); 
        
        if(isset($result[0])){
            $db_pwd=Crypt::decrypt($result[0]->password);
            if($db_pwd==$request->str_login_password){
                if($request->rememberme===null){
                    setcookie('login_email',$request->str_login_email,100);
                    setcookie('login_pwd',$request->str_login_password,100);
                }else{
                   setcookie('login_email',$request->str_login_email,time()+60*60*24*100);
                   setcookie('login_pwd',$request->str_login_password,time()+60*60*24*100);
                }
                $request->session()->put('FRONT_USER_LOGIN',true);
                $request->session()->put('FRONT_USER_ID',$result[0]->id);
                $request->session()->put('FRONT_USER_NAME',$result[0]->name);
                $status="success";
                $msg="";
                $getUserTempId=getUserTempId();
                DB::table('cart')  
                    ->where(['user_id'=>$getUserTempId,'user_type'=>'Not-Reg'])
                    ->update(['user_id'=>$result[0]->id,'user_type'=>'Reg']);
            }else{
                $status="error";
                $msg="Please enter valid password";
            }
        }else{
            $status="error";
            $msg="Please enter valid email id";
        }
       return response()->json(['status'=>$status,'msg'=>$msg]); 
       //$request->password
    }
    public function checkout(Request $request)
    {
        $result['cart_data']=getAddToCartTotalItem();

        if(isset($result['cart_data'][0])){

            if($request->session()->has('FRONT_USER_LOGIN')){
                $uid=$request->session()->get('FRONT_USER_ID');
                $customer_info=DB::table('customers')  
                    ->where(['id'=> $uid])
                     ->get(); 
                $result['customers']['name']=$customer_info[0]->name;
                $result['customers']['email']=$customer_info[0]->email;
                $result['customers']['mobile']=$customer_info[0]->mobile;
                $result['customers']['address']=$customer_info[0]->address;
                $result['customers']['city']=$customer_info[0]->city;
                $result['customers']['state']=$customer_info[0]->state;
                $result['customers']['zip']=$customer_info[0]->zip;
            }else{
                $result['customers']['name']='';
                $result['customers']['email']='';
                $result['customers']['mobile']='';
                $result['customers']['address']='';
                $result['customers']['city']='';
                $result['customers']['state']='';
                $result['customers']['zip']='';
            }

            return view('front.checkout',$result);
        }else{
            return redirect('/');
        }
    }

    public function apply_coupon_code(Request $request)
    {
        $totalPrice=0;
        $result=DB::table('coupons')  
            ->where(['code'=>$request->coupon_code])
            ->get(); 
        
        if(isset($result[0])){
            $value=$result[0]->value;
            $type=$result[0]->type;
            $getAddToCartTotalItem=getAddToCartTotalItem();
            
            foreach($getAddToCartTotalItem as $list){
                $totalPrice=$totalPrice+($list->qty*$list->price);
            }  
            if($result[0]->status==1){
                if($result[0]->is_one_time==1){
                    $status="error";
                    $msg="Coupon code already used";    
                }else{
                    $min_order_amt=$result[0]->min_order_amt;
                    if($min_order_amt>0){
                         
                        if($min_order_amt<$totalPrice){
                            $status="success";
                            $msg="Coupon code applied";
                        }else{
                            $status="error";
                            $msg="Cart amount must be greater then $min_order_amt";
                        }
                    }else{
                         $status="success";
                         $msg="Coupon code applied";
                    }
                }
            }else{
                $status="error";
                $msg="Coupon code deactivated";   
            }
            
        }else{
           $status="error";
           $msg="Please enter valid coupon code";
        }
        
       
        if($status=='success'){
            if($type=='Value'){
                $totalPrice=$totalPrice-$value;
            }if($type=='Per'){
                $newPrice=($value/100)*$totalPrice;
                $totalPrice=round($totalPrice-$newPrice);
            }
        }

        return response()->json(['status'=>$status,'msg'=>$msg,'totalPrice'=>$totalPrice]); 
    }
    
    public function remove_coupon_code(Request $request)
    {
        $totalPrice=0;
        $result=DB::table('coupons')  
        ->where(['code'=>$request->coupon_code])
        ->get(); 
        $getAddToCartTotalItem=getAddToCartTotalItem();
        $totalPrice=0;
        foreach($getAddToCartTotalItem as $list){
            $totalPrice=$totalPrice+($list->qty*$list->price);
        }  
        
        return response()->json(['status'=>'success','msg'=>'Coupon code removed','totalPrice'=>$totalPrice]); 
    }
    
}
