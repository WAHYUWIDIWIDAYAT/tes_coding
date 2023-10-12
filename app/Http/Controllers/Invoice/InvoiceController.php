<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;

class InvoiceController extends Controller
{

    public function index()
    {
        try{
            $orders = Order::with('details', 'details.product')->get();
            return response()->json([
                'success' => true,
                'message' => 'List Semua Invoice',
                'invoice' => $orders->map(function ($order) {
                    return [
                        'invoice' => $order->invoice,
                        'customer_name' => $order->customer_name,
                        'total' => $order->total,
                        'created_at' => $order->created_at->tz('Asia/Jakarta')->format('d-m-Y H:i:s'),
                        'updated_at' => $order->updated_at->tz('Asia/Jakarta')->format('d-m-Y H:i:s'),
                        'detail' => $order->details->map(function ($detail) {
                            return [
                                'product_name' => $detail->product_name,
                                'qty' => $detail->product_qty,
                                'price' => $detail->product_price,
                                'discount' => $detail->product_discount . '%',
                                'total' => $detail->total,
                            ];
                        }),
                    ];
                })
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Gagal menampilkan invoice',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    private function generateInvoiceNumber()
    {
        //get time now 
        $date = now();
        //format date to string , add INV and add 3 digit number from max id + 1 
        $invoiceNumber = $date->format('ymd') . '/INV/' . str_pad(Order::max('id') + 1, 3, '0', STR_PAD_LEFT);
        //return invoice number
        return $invoiceNumber;
    }

    private function getProduct($productData)
    {
        $products = [];
        foreach ($productData as $data) {
            $product_id = $data['product_id'];
            $quantity = $data['quantity'];
            $product = Product::find($product_id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product dengan id ' . $product_id . ' tidak ditemukan'
                ], 404);
            }
            $product->quantity = $quantity;
            $products[] = $product;
        }

        return $products;
    }

    public function store(Request $request)
    {
        $customer_name = $request->input('customer_name');
        $productData = $request->input('products');
        $products = $this->getProduct($productData);
        try {
            $invoiceNumber = $this->generateInvoiceNumber();
            $order = Order::create([
                'invoice' => $invoiceNumber,
                'customer_name' => $customer_name,
                'total' => 0
            ]);
            $total = 0;
            foreach ($products as $product) {
                $subtotal = $product->price * $product->quantity - ($product->price * $product->quantity * $product->discount / 100);
                OrderDetails::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_qty' => $product->quantity, 
                    'product_name' => $product->name,
                    'product_price' => $product->price,
                    'product_discount' => $product->discount,
                    'total' => $subtotal
                ]);
            }
            $order->total = OrderDetails::where('order_id', $order->id)->sum('total');
            $order->save();
            return response()->json([
                'success' => true,
                'message' => 'Invoice berhasil dibuat',
                'data' => [
                    'invoice' => $order->invoice,
                    'customer_name' => $order->customer_name,
                    'total' => $order->total,
                    'created_at' => $order->created_at->tz('Asia/Jakarta')->format('d-m-Y H:i:s'),
                ]
                
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice gagal dibuat',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function show(){
        $invoice = request()->query('invoice');
        try{
            $order = Order::where('invoice', $invoice)->with('details', 'details.product')->first();
            if($order){
                return response()->json([
                    'success' => true,
                    'invoice' => $order->invoice,
                    'customer_name' => $order->customer_name,
                    'total' => $order->total,
                    'created_at' => $order->created_at->tz('Asia/Jakarta')->format('d-m-Y H:i:s'),
                    'updated_at' => $order->updated_at->tz('Asia/Jakarta')->format('d-m-Y H:i:s'),
                    'detail' => $order->details->map(function ($detail) {
                        return [
                            'product_name' => $detail->product_name,
                            'qty' => $detail->product_qty,
                            'price' => $detail->product_price,
                            'discount' => $detail->product_discount . '%',
                            'total' => $detail->total,
                        ];
                    }),
                
                ], 200);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice tidak ditemukan'
                ], 404);
            }
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Gagal menampilkan invoice',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request){
        $invoice = request()->query('invoice');
        $customer_name = $request->input('customer_name');
        $productData = $request->input('products');
        
        try{
            $order = Order::where('invoice', $invoice)->first();
            if($order){
                $order->customer_name = $customer_name;
                $order->total = 0;
                $order->save();
                $products = $this->getProduct($productData);
                $total = 0;
                foreach ($products as $product) {
                    $subtotal = $product->price * $product->quantity - ($product->price * $product->quantity * $product->discount / 100);
                    OrderDetails::updateOrCreate([
                        'order_id' => $order->id,
                     
                    ],[
                        'product_qty' => $product->quantity, 
                        'product_name' => $product->name,
                        'product_price' => $product->price,
                        'product_discount' => $product->discount,
                        'total' => $subtotal
                    ]);
                }
                $order->total = OrderDetails::where('order_id', $order->id)->sum('total');
                $order->save();
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice berhasil diupdate',
                    'data' => [
                        'invoice' => $order->invoice,
                        'customer_name' => $order->customer_name,
                        'total' => $order->total,
                        'created_at' => $order->created_at->tz('Asia/Jakarta')->format('d-m-Y H:i:s'),
                    ]
                    
                ], 200);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice tidak ditemukan'
                ], 404);
            }
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate invoice',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(){
        $invoice = request()->query('invoice');
        try{
            $order = Order::where('invoice', $invoice)->first();
            if($order){
                OrderDetails::where('order_id', $order->id)->delete();
                $order->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice berhasil dihapus',
                ], 200);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice tidak ditemukan'
                ], 404);
            }
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus invoice',
                'data' => $e->getMessage()
            ], 500);
        }
    }

}
