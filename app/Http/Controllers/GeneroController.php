<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Genero;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Faker\Factory as Faker;

class GeneroController extends Controller
{
    public function index(){

        try {
            $generos = Genero::all();
    
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
            ])->get('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/resenas');
    
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400); 
            }
    
            return response()->json([
                'msg' => 'Listado de géneros',
                'generos' => $generos,
                'reseñas' => $dataResponse->json() 
            ], 200); 
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa'
            ], 500); 
        }
    }


    public function show($id)
    {
        $genero = Genero::find($id);
        if (!$genero) 
        {
            return response()->json([
                'msg' => 'No se encontró el género'
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
            ])->get('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/resenas/' . $id);
   
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400); 
            }
   
            return response()->json([
                'msg' => 'Género encontrado',
                'genero' => $genero,
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
            ]);
    
            $faker = Faker::create();
    
            $genero = new Genero();
            $genero->nombre = $request->input('nombre'); 
            $genero->save();
    
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
    
            $resenadata = [
                'resena'=>$faker->sentence,
                'fecha'=>$faker->date,
                'calificacion'=>$faker ->numberBetween(1, 10),
                'user_id'=>$faker ->numberBetween(85, 90),
                'cancion_id'=>$faker ->numberBetween(20, 90),
            ];
    
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token}"
            ])->post("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/resenas", $resenadata);
        
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al crear la resena en la API externa',
                    'details' => $dataResponse->json() 
                ], 400);
            }
    
            $resena = $dataResponse->json();
            \Log::info('Respuesta de la API externa:', $resena);
    
            return response()->json([
                'msg' => 'género creado',
                'genero' => $genero,
                'data' => $resena 
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

        ]);

        $faker = Faker::create();

        $genero = Genero::find($id);
    
        if (!$genero) {
            return response()->json([
                'msg' => 'No se encontró el género'
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

        $genero->nombre = $request->input('nombre'); 
        $genero->save(); 
    
        $resenadata = [
            'resena'=>$faker->word,
            'fecha'=>$faker->date,
            'calificacion'=>$faker ->numberBetween(1, 10),
            'user_id'=>$faker ->numberBetween(85, 90),
            'cancion_id'=>$faker ->numberBetween(20, 90),
        ];

       
        $dataResponse = Http::withHeaders([
            'Authorization' => "Bearer {$token}"
        ])->put("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/resenas/". $id , $resenasData);
    
        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al actualizar la resena en la API externa'
            ], 400);
        }
    
        return response()->json([
            'msg' => 'Libro actualizado con éxito',
            'genero' => $genero,
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
        ])->delete("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/resenas/". $id );

        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al eliminar la resena en la API externa'
            ], 400); 
        }

        return response()->json([
            'msg' => 'genero eliminado con éxito'
        ], 200); 
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al comunicarse con la API externa'
        ], 500);
    }
    }
}
