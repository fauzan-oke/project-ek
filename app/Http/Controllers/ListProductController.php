<?php

namespace App\Http\Controllers;

use App\Models\ListProduct;
use App\Models\StationPrinter;
use App\Models\Promo;
use Illuminate\Http\Request;

class ListProductController extends Controller
{
    //
    public function destroy()
    {
        ListProduct::truncate();
    }
    public function index()
    {
        try {
            return ListProduct::all();
            // return response()->json([
            //     'data' => $menu,
            //     'message' => 'berhasil get data!'
            // ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e
            ], 400);
        }
    }

    public function store()
    {
        try {
            // $items = ['Jeruk Dingin' => 12000, 'Jeruk Panas' => 10000, 'Teh Manis' => 8000, 'Teh Tawar' => 5000, 'Kopi Panas' => 6000, 'Kopi Dingin' => 8000, 'Xtra Es Batu' => 2000];
            $items = ['Mie Goreng' => 15000, 'Mie Kuah' => 15000, 'Nasi Goreng' => 15000];
            foreach ($items as $nama => $harga) {
                $product = new ListProduct;
                $product->nama = $nama;
                $product->harga = $harga;
                $product->kategori = 'makanan';
                $product->save();
            }
            return response()->json([
                'message' => 'berhasil store data!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e,
            ], 409);
        }
    }

    public function delete($id)
    {
        try {
            $product = ListProduct::find($id)->delete();
            return response()->json([
                'message' => 'berhasil delete data!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e,
            ], 409);
        }
    }


    public function indexP()
    {
        return Promo::all();
    }

    public function storeP()
    {
        try {

            $sp = new Promo;
            $sp->list_product_ids = '10,1';
            $sp->nama = "Nasi Goreng + Jeruk Dingin";
            $sp->harga = 23000;
            $sp->save();

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
