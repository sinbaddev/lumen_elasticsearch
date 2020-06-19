<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order
{
    const ELASTIC_INDEX = 'order_index';
    const ELASTIC_TYPE = 'order_type';

    public function __construct()
    {
    }

    public function getData()
    {
        $qr = 'SELECT orders.*, 
                users.id as relationship_user_id,
                users.menber_id,
                users.brand,
                users.uid,
                users.fullname,
                users.email,
                users.group_code,
                users.active,
                users.created_at as user_created_at,
                users.updated_at as user_updated_at,
                cities.id as cities_id,
                cities.name as city_name,
                cities.date as city_date,
                cities.region as city_region,
                cities.feature,
                cities.url_source,
                cities.url_rss,
                cities.url_minhngoc,
                cities.time_release,
                cities.status as city_status,
                cities.created_at as city_created_at,
                cities.updated_at as city_updated_at,
                categories.id as relationship_category_id,
                categories.name as category_name,
                categories.region as category_region,
                categories.type,
                categories.guide,
                categories.rate,
                categories.pay_number,
                categories.min_amount,
                categories.max_amount,
                categories.multi,
                categories.code,
                categories.max,
                categories.active,
                categories.created_at as category_created_at,
                categories.updated_at as category_updated_at
            FROM orders 
            JOIN users ON users.id = orders.user_id 
            JOIN cities ON cities.id = orders.city_id 
            JOIN categories ON categories.id = orders.category_id
            LIMIT 0,10000';

        $list = DB::select($qr);

        return $list;
    }

    public function createIndex()
    {
        $list = $this->getData();

        $data = [];

        foreach ($list as $key => $order) {
            $data['body'][] = [
                'index' => [
                    '_index' => 'order_index',
                    '_type' => 'order_type',
                    '_id' => $order->id,
                ]
            ];

            $data['body'][] = [
                'id' => $order->id,
                'user_id' => $order->user_id,
                'city_id' => $order->city_id,
                'category_id' => $order->category_id,
                'date' => $order->date,
                'amount' => $order->amount,
                'amount_one' => $order->amount_one,
                'amount_win' => $order->amount_win,
                'rate' => $order->rate,
                'numbers' => $order->numbers,
                'numbers_win' => $order->numbers_win,
                'total_win' => $order->total_win,
                'note' => $order->note,
                'status' => $order->status,
                'checked' => $order->checked,
                'checked_at' => $order->checked_at,
                'checked_by' => $order->checked_by,
                'token' => $order->token,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'relationship_user_id' => $order->relationship_user_id,
                'menber_id' => $order->menber_id,
                'brand' => $order->brand,
                'uid' => $order->uid,
                'fullname' => $order->fullname,
                'email' => $order->email,
                'group_code' => $order->group_code,
                'active' => $order->active,
                'user_created_at' => $order->user_created_at,
                'user_updated_at' => $order->user_updated_at,
                'cities_id' => $order->cities_id,
                'city_name' => $order->city_name,
                'city_date' => $order->city_date,
                'city_region' => $order->city_region,
                'feature' => $order->feature,
                'url_source' => $order->url_source,
                'url_rss' => $order->url_rss,
                'url_minhngoc' => $order->url_minhngoc,
                'time_release' => $order->time_release,
                'city_status' => $order->city_status,
                'city_created_at' => $order->city_created_at,
                'city_updated_at' => $order->city_updated_at,
                'relationship_category_id' => $order->relationship_category_id,
                'category_name' => $order->category_name,
                'category_region' => $order->category_region,
                'type' => $order->type,
                'guide' => $order->guide,
                'pay_number' => $order->pay_number,
                'min_amount' => $order->min_amount,
                'max_amount' => $order->max_amount,
                'multi' => $order->multi,
                'code' => $order->code,
                'max' => $order->max,
                'category_created_at' => $order->category_created_at,
                'category_updated_at' => $order->category_updated_at,
            ];
        }
        
        return $data;
    }
}