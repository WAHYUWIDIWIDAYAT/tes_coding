<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;

class InvoiceController extends Controller
{
    
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
        $productQuantities = [];

        foreach ($productData as $data) {
            $product_id = $data['product_id'];
            $quantity = $data['quantity'];

            if (array_key_exists($product_id, $productQuantities)) {
                // If the product id already exists, add to the quantity
                $productQuantities[$product_id] += $quantity;
            } else {
                // If the product id doesn't exist, create a new entry with quantity
                $productQuantities[$product_id] = $quantity;
            }
        }
        $products = [];
        foreach ($productQuantities as $product_id => $quantity) {
            $product = Product::find($product_id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product dengan id ' . $product_id . ' tidak ditemukan'
                ], 404);
            }

            // Set the quantity to the aggregated quantity
            $product->quantity = $quantity;
            $products[] = $product;
        }

        return $products;
    }

    public function index()
    {
        try{
            //get all invoice with details and product
            $orders = Order::with('details', 'details.product')->get();
            return response()->json([
                //map data to new array
                'success' => true,
                'message' => 'List Semua Invoice',
                'invoice' => $orders->map(function ($order) {
                    //return data with new format
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
                                'total_before_discount' => $detail->total_before_discount,
                                'discount' => $detail->product_discount . '%',
                                'total' => $detail->total,
                            ];
                        }),
                    ];
                })
            ], 200);
        }catch(\Exception $e){
            //if error return error message
            return response()->json([
                'success' => false,
                'message' => 'Gagal menampilkan invoice',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        //get customer name and product data from request
        $customer_name = $request->input('customer_name');
        $productData = $request->input('products');
        //get product data from function getProduct
        $products = $this->getProduct($productData);
        try {
            //generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber();
            $order = Order::create([
                'invoice' => $invoiceNumber,
                'customer_name' => $customer_name,
                'total' => 0
            ]);
            //looping product data
            $total = 0;
            foreach ($products as $product) {
                $subtotal = $product->price * $product->quantity - ($product->price * $product->quantity * $product->discount / 100);
                OrderDetails::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_qty' => $product->quantity, 
                    'total_before_discount' => $product->price * $product->quantity,
                    'product_name' => $product->name,
                    'product_price' => $product->price,
                    'product_discount' => $product->discount,
                    'total' => $subtotal
                ]);
            }
            //update total order
            $order->total = OrderDetails::where('order_id', $order->id)->sum('total');
            $order->save();
            //return success message with invoice data
            return response()->json([
                'success' => true,
                'message' => 'Invoice berhasil dibuat',
                'data' => [
                    'invoice' => $order->invoice,
                    'customer_name' => $order->customer_name,
                    'total' => $order->total,
                    'created_at' => $order->created_at->tz('Asia/Jakarta')->format('d-m-Y H:i:s'),
                    'updated_at' => $order->updated_at->tz('Asia/Jakarta')->format('d-m-Y H:i:s'),
                ]
                
            ], 200);
        } catch (\Exception $e) {
            //if error return error message
            return response()->json([
                'success' => false,
                'message' => 'Invoice gagal dibuat',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function show(){
        //get invoice number from query
        $invoice = request()->query('invoice');
        try{
            //get invoice data with details and product
            $order = Order::where('invoice', $invoice)->with('details', 'details.product')->first();
            if($order){
                //return invoice data with new format
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
                            'total_before_discount' => $detail->total_before_discount,
                            'total' => $detail->total,
                        ];
                    }),
                
                ], 200);
            }else{
                //if invoice not found return error message
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice tidak ditemukan'
                ], 404);
            }
        }catch(\Exception $e){
            //if error return error message
            return response()->json([
                'success' => false,
                'message' => 'Gagal menampilkan invoice',
                'data' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request){
        // Get invoice number, customer name, and product data from the request
        $invoice = request()->query('invoice');
        $customer_name = $request->input('customer_name');
        $productData = $request->input('products');
        
        try {
            // Find the invoice by invoice number
            $order = Order::where('invoice', $invoice)->first();
            if ($order) {
                // Check if the customer_name is changed
                if (!is_null($customer_name) && $customer_name !== $order->customer_name) {
                    $order->customer_name = $customer_name;
                    $order->save();
                } else {
                    $customer_name = $order->customer_name; // Use the old customer_name
                }
                
                // Initialize the total
                $total = 0;
                
                // Check if there are products provided
                if (!empty($productData)) {
                    // Delete existing order details
                    OrderDetails::where('order_id', $order->id)->delete();
                    
                    // Loop through the products
                    $products = $this->getProduct($productData);
                    foreach ($products as $product) {
                        // Calculate the subtotal
                        $subtotal = $product->price * $product->quantity - ($product->price * $product->quantity * $product->discount / 100);
                    
                        // Update or create order details for each product
                        OrderDetails::create([
                            'order_id' => $order->id,
                            'product_qty' => $product->quantity,
                            'product_name' => $product->name,
                            'product_price' => $product->price,
                            'product_discount' => $product->discount,
                            'total_before_discount' => $product->price * $product->quantity,
                            'total' => $subtotal
                        ]);
                    
                        // Calculate the total order
                        $total += $subtotal;
                    }
                    
                    // Update the total order if products are changed
                    $order->total = $total;
                    $order->save();
                }
        
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice berhasil diupdate',
                    'data' => [
                        'invoice' => $order->invoice,
                        'customer_name' => $customer_name, // Use the potentially updated customer_name
                        'total' => $order->total,
                        'created_at' => $order->created_at->tz('Asia/Jakarta')->format('d-m-Y H:i:s'),
                    ]
                ], 200);
            } else {
                // If invoice not found, return an error message
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice tidak ditemukan'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate invoice',
                'data' => $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy(){
        //get invoice number from query
        $invoice = request()->query('invoice');
        try{
            //find invoice by invoice number
            $order = Order::where('invoice', $invoice)->first();
            if($order){
                OrderDetails::where('order_id', $order->id)->delete();
                $order->delete();
                //return success message if invoice deleted
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice berhasil dihapus',
                ], 200);
            }else{
                //if invoice not found return error message
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice tidak ditemukan'
                ], 404);
            }
        }catch(\Exception $e){
            //if error return error message
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus invoice',
                'data' => $e->getMessage()
            ], 500);
        }
    }

}
