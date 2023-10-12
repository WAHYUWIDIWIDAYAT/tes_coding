<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    //

    public function index()
    {
        try {
            if (Product::count() > 0) {
                $products = Product::all();
                return response()->json([
                    'status' => 'success',
                    'data' => $products
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product tidak ditemukan'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e
            ] , 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::find($id);
            if ($product) {
                return response()->json([
                    'status' => 'success',
                    'data' => $product
                ]);
            } else {
            
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product tidak ditemukan'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e
            ] , 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $product = Product::create([
                'name' => $request->name,
                'price' => $request->price,
                'discount' => $request->discount
            ]);
            return response()->json([
                'status' => 'success',
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e
            ] , 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product tidak ditemukan'
                ], 404);
            }
            if ($request->has('name')) {
                $product->name = $request->name;
            }
            if ($request->has('price')) {
                $product->price = $request->price;
            }
            if ($request->has('discount')) {
                $product->discount = $request->discount;
            }
            $product->save();
            return response()->json([
                'status' => 'success',
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::find($id);
            if ($product) {
                $product->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Product berhasil dihapus'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product tidak ditemukan'
                ]);
            }
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Gagal menampilkan invoice',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}
