<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\ProductContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Traits\UploadAble;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use UploadAble;

    protected $productRepository;
    public function __construct(ProductContract $productRepository){
        $this->productRepository = $productRepository;
        $this->authorizeResource(Product::class, 'product');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->productRepository->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        $image      = $this->base64ToImage($request->image)['image'];
        $extension  = $this->base64ToImage($request->image)['extension'];

        $FileError = $this->setImageValidationError($extension,'image',['jpg','jpeg','png','svg']);

        if ($FileError) {
             return response()->json([
                'message' => $FileError['error'],
                'errors' => [
                    $FileError['feild'] => [ $FileError['error'] ]
                ]
            ], $FileError['status']);
        }
        $uploadedFile = $this->uploadBase64File($request->image , 'products/','public');
        $attributes = [
            'image' => $uploadedFile['name']
        ];
        $merged = array_merge($request->all(),$attributes);
        return $this->productRepository->create($merged);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return $this->productRepository->show($product->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, Product $product)
    {
        return $this->productRepository->update($request->all(),$request->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        return $this->productRepository->delete($product->id);
    }

    public function bulk_delete(Request $request){
       $selected_item = $request->selected_data;
       return $this->productRepository->bulk_delete($selected_item);
    }
}
