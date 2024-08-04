<?php

namespace App\Http\Controllers;

use App\Models\ListProduct;
use App\Models\Orders;
use App\Models\Promo;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    //
    public function destroy()
    {
        $orders = Orders::truncate();
    }
    public function create(Request $request)
    {
        try {
            $inputs = $request->all();
            //mengecek apakah meja yang dipesan penuh atau tidak.
            $cekMeja = Orders::where('meja', $inputs['meja'])->where('status_pembayaran', 'pending')->first();
            if ($cekMeja) {
                return response()->json([
                    'message' => 'meja nomor ' . $inputs['meja'] . ' FULL',
                ], 400);
            }
            if (!empty($inputs)) {
                //meggunakan bitwise operation untuk printer    
                $PRINTER_KASIR = 0001;
                $PRINTER_DAPUR = 0010;
                $PRINTER_BAR = 0100;

                //menyiapkan metadata
                $metadata = [
                    'A' => [
                        'keterangan' => 'Printer Kasir',
                        'products' => [],
                        'total_harga' => 0
                    ],
                    'B' => [
                        'keterangan' => 'Printer Dapur',
                        'products' => []
                    ],
                    'C' => [
                        'keterangan' => 'Printer Bar',
                        'products' => []
                    ],
                ];

                //memanipulasi data pesanan reguler 
                foreach ($request->input('reguler', []) as $item) {
                    if (is_array($item)) {
                        $list_product_id = $item['list_product_id'];
                        $harga = $item['harga'];
                        $jumlah = $item['jumlah'];
                        $product = ListProduct::find($list_product_id);
                        //detail untuk kasir
                        $productDetailA = [
                            'list_product_id' => $list_product_id,
                            'nama' => $product->nama,
                            'harga' => $harga,
                            'jumlah' => $jumlah,
                        ];
                        //detail untuk dapur dan bar
                        $productDetail = [
                            'list_product_id' => $list_product_id,
                            'nama' => $product->nama,
                            'jumlah' => $jumlah,
                        ];

                        //menambahkan data kasir pada metadata
                        $metadata['A']['products']['reguler'][] = $productDetailA;
                        $metadata['A']['total_harga'] += $harga * $jumlah;


                        //cek dulu ada ga productnya
                        if ($product) {
                            $kategori = $product->kategori;
                            $printerFlag = $PRINTER_KASIR; //default kasir

                            if ($kategori == 'makanan') {
                                $printerFlag |= $PRINTER_DAPUR; //nambahin Dapur flag
                            } elseif ($kategori == 'minuman') {
                                $printerFlag |= $PRINTER_BAR; //nambahin Bar flag
                            }

                            //nambahin metadata sesuai dengan flag printer
                            if ($printerFlag & $PRINTER_DAPUR) {
                                $metadata['B']['products'][] = $productDetail;
                            }
                            if ($printerFlag & $PRINTER_BAR) {
                                $metadata['C']['products'][] = $productDetail;
                            }
                        } else {
                            //kalau gakada
                            return response()->json([
                                'message' => "Product with ID {$list_product_id} not found.",
                            ], 404);
                        }
                    }
                }

                // manipulas data promo
                foreach ($request->input('promo', []) as $item) {
                    if (is_array($item)) {
                        $promo_id = $item['promo_id'];
                        $harga = $item['harga'];
                        $jumlah = $item['jumlah'];
                        //logic-nya hampir sama dengan manipulasi data reguler
                        $promo = Promo::find($promo_id);
                        if ($promo) {
                            $productDetailA = [
                                'promo_id' => $promo_id,
                                'nama' => $promo->nama,
                                'harga' => $harga,
                                'jumlah' => $jumlah,
                            ];
                            $metadata['A']['products']['promo'][] = $productDetailA;
                            $metadata['A']['total_harga'] += $harga * $jumlah;
                            $list_product_ids = explode(',', $promo->list_product_ids);
                            foreach ($list_product_ids as $list_product_id) {
                                $product = ListProduct::find($list_product_id);
                                $productDetail = [
                                    'list_product_id' => $list_product_id,
                                    'nama' => $product->nama,
                                    'jumlah' => $jumlah,
                                ];
                                $product = ListProduct::find($list_product_id);
                                if ($product) {
                                    $kategori = $product->kategori;
                                    if ($kategori == 'makanan') {
                                        $metadata['B']['products'][] = $productDetail;
                                    } elseif ($kategori == 'minuman') {
                                        $metadata['C']['products'][] = $productDetail;
                                    }
                                } else {
                                    return response()->json([
                                        'message' => "Product with ID {$list_product_id} not found.",
                                    ], 404);
                                }
                            }
                        } else {
                            return response()->json([
                                'message' => "Promo with ID {$promo_id} not found.",
                            ], 404);
                        }
                    }
                }

                // Filter untuk empty array
                $filteredMetadata = array_filter($metadata, function ($printer) {
                    return !empty($printer['products']);
                });

                //store data order
                $order = new Orders;
                $order->metadata = $filteredMetadata;
                $order->meja = $inputs['meja'] ?? '';
                $order->status_pembayaran = 'pending';
                $order->save();

                //isi data response
                $responseData = [
                    'order_id' => $order->id,
                ];
                if (!empty($filteredMetadata['B']['products'])) {
                    $responseData['B'] = $filteredMetadata['B'];
                }
                if (!empty($filteredMetadata['C']['products'])) {
                    $responseData['C'] = $filteredMetadata['C'];
                }
                return response()->json([
                    'message' => 'Order created successfully!',
                    'data' => $responseData
                ], 200);
            } else {
                return response()->json([
                    'message' => 'No input data provided',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 400);
        }
    }

    public function bill($id)
    {
        try {
            //get data order yang pending sesuai dengan no meja
            $bill = Orders::where('meja', $id)->where('status_pembayaran', 'pending')->first();
            if ($bill) {
                return response()->json([
                    'message' => 'Get bill successfully!',
                    'data' => array(
                        'bill' => $bill->metadata['A'],
                        'status_pembayaran' => $bill->status_pembayaran,
                        'no_meja' => $bill->meja
                    )
                ], 200);
            } else {
                return response()->json([
                    'message' => 'not found!',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
