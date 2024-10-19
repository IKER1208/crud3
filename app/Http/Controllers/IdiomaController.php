<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Idioma;

class IdiomaController extends Controller
{
    public function index()
    {
        
        try {
            $idiomas = Idioma::all();
    
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
            ])->get('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/playlistsCanciones');
    
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400); 
            }
    
            return response()->json([
                'msg' => 'Listado de idiomas',
                'idiomas' => $idiomas,
                'data' => $dataResponse->json() 
            ], 200); 
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa'
            ], 500); 
        }
    }

    public function show($id)
    {
       
        $idioma = Idioma::find($id);
        if (!$idioma) 
        {
            return response()->json([
                'msg' => 'No se encontró el idioma'
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
            ])->get('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/playlistsCanciones/' . $id);
   
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400); 
            }
   
            return response()->json([
                'msg' => 'idioma encontrado',
                'idioma' => $idioma,
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
    
            $idioma = new Idioma();
            $idioma->nombre = $request->input('nombre'); 
            $idioma->save();
    
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
    
            $playlistcData = [

            ];
    
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token}"
            ])->post("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/playlistsCanciones", $playlistcData);
        
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al crear la playlistData en la API externa',
                    'details' => $dataResponse->json() 
                ], 400);
            }
    
            $resena = $dataResponse->json();
            \Log::info('Respuesta de la API externa:', $resena); // Registra la respuesta
    
            return response()->json([
                'msg' => 'eidioma creado',
                'idioma' => $idioma,
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

        $idioma = Idioma::find($id);
    
        if (!$idioma) {
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

        $idioma->nombre = $request->input('nombre'); 
        $idioma->save(); 
    
        $playlistcData = [
            'playlist_id'=>$faker ->numberBetween(20, 90),
            'cancion_id'=>$faker ->numberBetween(20, 90),
            

        ];

       
        $dataResponse = Http::withHeaders([
            'Authorization' => "Bearer {$token}"
        ])->put("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/playlistsCanciones/". $id , $playlistcData);
    
        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al actualizar la playlistData en la API externa'
            ], 400);
        }
    
        return response()->json([
            'msg' => 'idioma actualizado con éxito',
            'idioma' => $idioma,
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
        ])->delete("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/playlistsCanciones/". $id );

        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al eliminar la resena en la API externa'
            ], 400); 
        }

        return response()->json([
            'msg' => 'idioma eliminado con éxito',
            'data' => $dataResponse->json()
        ], 200); 
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al comunicarse con la API externa'
        ], 500);
    }
    }
}
