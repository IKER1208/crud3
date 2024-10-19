<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Prestamo;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\http\Controllers\AuthController;
use Faker\Factory as Faker;
class PrestamoController extends Controller
{
    public function index(){
        try {
            $prestamos = Prestamo::all();
    
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
            ])->get('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/playlists');
    
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400); 
            }
    
            return response()->json([
                'msg' => 'Listado de prestamos',
                'prestamos' => $prestamos,
                'playlist' => $dataResponse->json() 
            ], 200); 
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa'
            ], 500); 
        }
    }
    public function show($id){
        $prestamo = Prestamo::find($id);
        if (!$prestamo) 
        {
            return response()->json([
                'msg' => 'No se encontró el prestamo'
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
            ])->get('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/playlists/' . $id);
   
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400); 
            }
   
            return response()->json([
                'msg' => 'prestamo encontrado',
                'prestamo' => $prestamo,
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
                    'cliente_id' => 'required|exists:clientes,id',
                    'libro_id' => 'required|exists:libros,id',
                    'fecha_prestamo' => 'required|date',
                    'fecha_devolucion' => 'nullable|date',
                ]);
        
                $faker = Faker::create();
        
                $prestamo = new Prestamo();
                $prestamo->cliente_id = $request->cliente_id;
                $prestamo->libro_id = $request->libro_id;
                $prestamo->fecha_prestamo = $request->fecha_prestamo;
                $prestamo->fecha_devolucion = $request->fecha_devolucion;
                $prestamo->save();
        
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
        
                $playlistDats = [
                    'nombre'=> $faker->sentence,
                    'descripcion' => $faker->sentence,
                    'user_id' => $faker->numberBetween(50, 100),
                    
                ];
        
                $dataResponse = Http::withHeaders([
                    'Authorization' => "Bearer {$token}"
                ])->post("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/playlist", $playlistData);
            
                if ($dataResponse->failed()) {
                    return response()->json([
                        'error' => 'Error al crear la playlist en la API externa',
                        'details' => $dataResponse->json() 
                    ], 400);
                }
        
                $playlistt = $dataResponse->json();
                \Log::info('Respuesta de la API externa:', $playlistt); // Registra la respuesta
        
                return response()->json([
                    'msg' => 'empleado con éxito',
                    'prestamo' => $prestamo,
                    'playlist' => $playlistt 
                ], 201);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
                ], 500);
            }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255', 
    
            ]);
    
            $faker = Faker::create();
    
            $prestamo = Prestamo::find($id);
        
            if (!$prestamo) {
                return response()->json([
                    'msg' => 'No se encontró el prestamo'
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
    
            $prestamo->cliente_id = $request->input('cliente_id'); 
            $prestamo->libro_id = $request->input('libro_id');
            $prestamo->fecha_prestamo = $request->input('fecha_prestamo');
            $prestamo_->fecha_devolucion = $request->input('fecha_devolucion');
            $prestamo->save(); 
        
            $playlistDats = [
                'nombre'=> $faker->sentence,
                'descripcion' => $faker->sentence,
                'user_id' => $faker->numberBetween(50, 100),

            ];
    
           
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token}"
            ])->put("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/playlists/". $id , $playlistDats);
        
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al actualizar la playlist en la API externa'
                ], 400);
            }
        
            return response()->json([
                'msg' => 'prestamo actualizado con éxito',
                'prestamo' => $prestamo,
                'data' => $dataResponse->json() 
            ], 200); 
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
            ], 500); 
        }
    }

    public function destroy($id)
    {
        try {
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
            ])->delete("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/playlists/". $id );
    
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al eliminar la playlist en la API externa'
                ], 400); 
            }
    
            return response()->json([
                'msg' => 'prestamo eliminado con éxito',
                'playlist' => $dataResponse->json()
            ], 200); 
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa'
            ], 500);
        }
        }
}
