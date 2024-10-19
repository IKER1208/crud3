<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Faker\Factory as Faker;
class SucursalController extends Controller
{
    public function index()
    {
        
        try {
            $sucursales = Sucursal::all();
    
            $tokenResponse = Http::post('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/login', [
                "email" => "noe@juadsdaaaaaaaaaaazn.iker",
                "password" => "password"
            ]);
    
            if ($tokenResponse->failed()) {
                return response()->json([
                    'error' => 'Error al autenticar con la API externa'
                ], 400); 
            }
    
            $token = $tokenResponse->json('token');
    
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token}"
            ])->get('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/singles');
    
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400); 
            }
    
            return response()->json([
                'msg' => 'Listado de sucursales',
                'sucursales' => $sucursales,
                'singles' => $dataResponse->json() 
            ], 200); 
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa'
            ], 500); 
        }
    }
    public function show($id){
        $sucursal = Sucursal::find($id);
        if (!$sucursal) 
        {
            return response()->json([
                'msg' => 'No se encontró la sucursal'
            ], 404); 
        }
   
        try 
        {
            $tokenResponse = Http::post('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/login', [
                "email" => "noe@juadsdaaaaaaaaaaazn.iker",
                "password" => "password"
            ]);
   
            
            if ($tokenResponse->failed()) 
            {
                return response()->json([
                    'error' => 'Error al autenticar con la API externa'
                ], 400); 
            }
   
            $token = $tokenResponse->json('token');
   
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token}"
            ])->get('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/singles/' . $id);
   
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400); 
            }
   
            return response()->json([
                'msg' => 'sucursal encontrada',
                'sucursal' => $sucursal,
                'data' => $dataResponse->json()
            ], 200); 
        } catch (\Exception $e) {
           
            return response()->json([
                'error' => 'Error al comunicarse con la API externa'
            ], 500); 
        }
    }
    public function store(Request $request)
    {

            try {
                $request->validate([
                    'nombre' => 'required|string|max:255',
                    'direccion' => 'required|string|max:255',
                ]);
        
                $faker = Faker::create();
        
                $sucursal = new Sucursal();
                $sucursal->nombre = $request->input('nombre'); 
                $sucursal->direccion = $request->input('direccion');
                $sucursal->save();
        
                $tokenResponse = Http::post('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/login', [
                    "email" => "noe@juadsdaaaaaaaaaaazn.iker",
                    "password" => "password"
                ]);
            
                if ($tokenResponse->failed()) {
                    return response()->json([
                        'error' => 'Error al autenticar con la API externa'
                    ], 400);
                }
        
                $token = $tokenResponse->json('token');
        
                $singleData = [
                    'nombre' => $faker->name,
                    'formato' => $faker->randomElement(['CD', 'Vinilo', 'Digital']),
                ];
        
                $dataResponse = Http::withHeaders([
                    'Authorization' => "Bearer {$token}"
                ])->post("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/singles", $singleData);
            
                if ($dataResponse->failed()) {
                    return response()->json([
                        'error' => 'Error al crear el single en la API externa',
                        'details' => $dataResponse->json() 
                    ], 400);
                }
        
                $single = $dataResponse->json();
                \Log::info('Respuesta de la API externa:', $single);
        
                return response()->json([
                    'msg' => 'sucursal creada',
                    'sucursal' => $sucursal,
                    'data' => $single 
                ], 201);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
                ], 500);
            }
    }

    public function update(Request $request, $id)
    {try {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',

        ]);

        $faker = Faker::create();

        $sucursal = Sucursal::find($id);
        if (!$sucursal) {
            return response()->json([
                'msg' => 'No se encontró la sucursal'
            ], 404); 
        }

        $tokenResponse = Http::post('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/login', [
            "email" => "noe@juadsdaaaaaaaaaaazn.iker",
            "password" => "password"
        ]);
    
        if ($tokenResponse->failed()) {
            return response()->json([
                'error' => 'Error al autenticar con la API externa'
            ], 400); 
        }
    
        $token = $tokenResponse->json('token');

        $sucursal->nombre = $request->input('nombre');
        $sucursal->direccion = $request->input('direccion');
        $sucursal->save(); 
    
        $singleData = [
            'nombre' => $faker->name,
            'formato' => $faker->randomElement(['CD', 'Vinilo', 'Digital']),
        ];

       
        $dataResponse = Http::withHeaders([
            'Authorization' => "Bearer {$token}"
        ])->put("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/singles/". $id , $singleData);
    
        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al actualizar el single en la API externa'
            ], 400);
        }
    
        return response()->json([
            'msg' => 'sucursal actualizado con éxito',
            'sucursal' => $sucursal,
            'data' => $dataResponse->json() 
        ], 200); 
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
        ], 500); 
    }
    }

    public function destroy($id)
    {try {
        $tokenResponse = Http::post('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/login', [
            "email" => "noe@juadsdaaaaaaaaaaazn.iker",
            "password" => "password"
        ]);

        if ($tokenResponse->failed()) {
            return response()->json([
                'error' => 'Error al autenticar con la API externa'
            ], 400); 
        }

        $token = $tokenResponse->json('token');

        $dataResponse = Http::withHeaders([
            'Authorization' => "Bearer {$token}"
        ])->delete("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/singles/". $id );

        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al eliminar la resena en la API externa'
            ], 400); 
        }

        return response()->json([
            'msg' => 'sucursal eliminado con éxito',
            'data' => $dataResponse->json()
        ], 200); 
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al comunicarse con la API externa'
        ], 500);
    }
    }
}

