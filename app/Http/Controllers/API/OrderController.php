<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Order;
use Validator;
use App\Http\Resources\Order as OrderResource;
use App\Models\Menu;
use App\Models\OrderDetail;

class OrderController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Order::all();

        return $this->sendResponse(OrderResource::collection($orders), 'Orders retrieved successfully.');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'customer_name' => 'required',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $order = [
            'customer_name' => $request->customer_name,
            'status' => $request->status,
        ];

        $saveOrder = Order::create($order);

        foreach ($request->menus as $item) {

            $menuPrice = Menu::find($item['menu_id'])->price;
            $subtotal = $menuPrice * $item['qty'];

            $orderDetail = [
                'order_id' => $saveOrder->id,
                'menu_id' => $item['menu_id'],
                'qty' => $item['qty'],
                'subtotal' => $subtotal
            ];

            OrderDetail::create($orderDetail);
        }

        return $this->sendResponse(new OrderResource($saveOrder), 'Order created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order::find($id);

        if (is_null($order)) {
            return $this->sendError('Order not found.');
        }

        return $this->sendResponse(new OrderResource($order), 'Order retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'customer_name' => 'required',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $order->customer_name = $input['customer_name'];
        $order->status = $input['status'];
        $order->save();

        return $this->sendResponse(new OrderResource($order), 'Order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        $order->delete();

        return $this->sendResponse([], 'Order deleted successfully.');
    }
}
