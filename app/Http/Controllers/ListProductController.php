<?php

namespace App\Http\Controllers;

use App\Models\ListProduct;
use Illuminate\Http\Request;

class ListProductController extends Controller
{
    //
    public function index()
    {
        return ListProduct::all();
    }

    public function store()
    {
        try {
            $product = new ListProduct;
            $product->metadata = ['nama' => 'jeruk', 'variant' => ['panas' => '10000', 'dingin' => '12000']];
            $product->kategori = 'minuman';
            $product->save();
            return response()->json([
                'message' => 'berhasil store data!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e,
            ], 409);
        }
    }
}
