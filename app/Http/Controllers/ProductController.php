<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    // Obtener productos API Fixlabs
    public function getProducts()
    {
        $response = Http::get('https://induccion.fixlabsdev.com/api/products');
        return $response->json();
    }

    // Función que crea producto en Jumpseller // retorna ID
    private function createProduct(array $data)
    {
        // Auth Jumpseller API
        $login = env('JUMPSELLER_LOGIN');
        $auth = env('JUMPSELLER_AUTH');

        // Generar estructura Producto Jumpseller

        $product = [
            'product' => [
                'name' => $data['name'],
                'price' => $data['price'],
                'description' => $data['description'],
            ]
        ];

        $response = Http::withBasicAuth($login, $auth)->post('https://api.jumpseller.com/v1/products.json', $product);
        return $response->json()['product']['id'];
    }

    // Crea variante de producto
    public function createVariant(int $product_id, array $data)
    {
        // Auth Jumpseller API
        $login = env('JUMPSELLER_LOGIN');
        $auth = env('JUMPSELLER_AUTH');

        // $response = Http::withBasicAuth($login, $auth)->post('https://api.jumpseller.com/v1/products.json', $data);
    }

    // Función main que tiene toda la lógica.
    public function transferData()
    {
        // Obtenemos productos
        $products = $this->getProducts();

        foreach ($products as $product) {

            // Crea un producto en jumpseller
            $product_id = $this->createProduct($product);
            // Itera por la cantidad de variantes
            foreach ($product['variants'] as $variant_code) {
                $sku_variant = $product['sku'] . '-' . $variant_code;
                
                $variant = [
                    'variant' => [
                        'sku' => $sku_variant,
                        'stock' => 0, //TODO: obtener stock desde el otro endpoint. 
                        // Añadir options si es necesario
                    ]
                ];
                $this->createVariant($product_id,$variant);
                


            }
        }
    }
}
