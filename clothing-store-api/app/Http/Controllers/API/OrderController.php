<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['customer', 'items.product'])->get();
        return response()->json(['data' => $orders], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order = Order::create([
            'customer_id' => $request->customer_id,
            'total_amount' => 0,
            'status' => 'pending'
        ]);

        $totalAmount = 0;

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $product->price
            ]);

            $totalAmount += $product->price * $item['quantity'];
            
            // Update product quantity
            $product->decrement('quantity', $item['quantity']);
        }

        $order->update(['total_amount' => $totalAmount]);

        return response()->json(['data' => $order->load('items.product')], 201);
    }

    public function show($id)
    {
        $order = Order::with(['customer', 'items.product'])->find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        return response()->json(['data' => $order], 200);
    }

    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|in:pending,processing,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order->update($request->only('status'));
        return response()->json(['data' => $order->load('items.product')], 200);
    }
}