<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Empleado;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Faker\Factory as Faker;

class EmpleadoController extends Controller
{
    public function index()
    {
       
        try {
            // Paso 1: Obtener todos los libros de tu base de datos local
            $empleados = Empleado::all();
    
            // Paso 2: Hacer la solicitud POST para obtener el token desde la API externa
            $tokenResponse = Http::post('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/login', [
                "email" => "noe@juadsdaaaaaaaaaaazn.iker",
                "password" => "password"
            ]);
    
            // Verificar si la solicitud para el token fue exitosa
            if ($tokenResponse->failed()) {
                return response()->json([
                    'error' => 'Error al autenticar con la API externa'
                ], 400); // Código 400 para errores de autenticación
            }
    
            // Extraer el token de la respuesta
            $token = $tokenResponse->json('token');
    
            // Paso 3: Usar ese token para hacer otra solicitud GET a la API protegida
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token}"
            ])->get('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/comentarios');
    
            // Verificar si la solicitud de datos fue exitosa
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400); // Código 400 para errores de la API externa
            }
    
            // Paso 4: Devolver la respuesta al cliente con los libros y los albums
            return response()->json([
                'msg' => 'Autores y albums encontrados',
                'empleados' => $empleados,
                'comentarios' => $dataResponse->json() // Cambié 'data' a 'artistas' para mayor claridad
            ], 200); // Código 200 para indicar éxito
        } catch (\Exception $e) {
            // Manejo de errores generales
            return response()->json([
                'error' => 'Error al comunicarse con la API externa'
            ], 500); // Código 500 para errores de servidor
        }
    }

    public function show($id)
    {
     // Buscar al cocinero por ID
     $empleado = Empleado::find($id);

     // Verificar si el cocinero existe
     if (!$empleado) 
     {
         return response()->json([
             'msg' => 'No se encontró el empleado'
         ], 404); // Código 404 para "No encontrado"
     }

     try 
     {
         // Paso 1: Hacer la solicitud POST para obtener el token desde la API externa
         $tokenResponse = Http::post('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/login', [
             "email" => "noe@juadsdaaaaaaaaaaazn.iker",
             "password" => "password"
         ]);

         // Verificar si la solicitud para el token fue exitosa
         if ($tokenResponse->failed()) 
         {
             return response()->json([
                 'error' => 'Error al autenticar con la API externa'
             ], 400); // Código 400 para errores de autenticación
         }

         // Extraer el token de la respuesta
         $token = $tokenResponse->json('token');

         // Paso 2: Usar ese token para hacer otra solicitud GET a la API protegida
         $dataResponse = Http::withHeaders([
             'Authorization' => "Bearer {$token}"
         ])->get('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/comentarios/' . $id);

         // Verificar si la solicitud de datos fue exitosa
         if ($dataResponse->failed()) {
             return response()->json([
                 'error' => 'Error al obtener datos de la API externa'
             ], 400); // Código 400 para errores de la API externa
         }

         // Paso 3: Devolver la respuesta al cliente con los datos del cocinero y la API
         return response()->json([
             'msg' => 'empleado encontrado',
             '------'=>'-------',
             'empleado' => $empleado,
             'data' => $dataResponse->json()
         ], 200); // Código 200 para indicar éxito
     } catch (\Exception $e) {
         // Manejo de errores generales
         return response()->json([
             'error' => 'Error al comunicarse con la API externa'
         ], 500); // Código 500 para errores de servidor
     }
    }
    public function store(Request $request)
    {
        try {
            // Paso 1: Validar la solicitud
            $request->validate([
                'nombre' => 'required|string|max:255',
                'sucursal_id' => 'required|exists:sucursales,id',
            ]);
    
            // Crear una instancia de Faker
            $faker = Faker::create();
    
            // Paso 2: Crear un nuevo libro
            $empleado = new Empleado();
            $empleado->nombre = $request->input('nombre'); 
            $empleado->sucursal_id = $request->input('sucursal_id'); 
            $empleado->save();
    
            // Paso 3: Obtener el token
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
    
            // Paso 4: Preparar los datos para la API externa
            $comentariodata = [
                'comentario' => $faker->text(255),
                'user_id' => $faker->numberBetween(50, 100),
                'album_id' => $faker->numberBetween(50, 100),
            ];
    
            // Paso 5: Crear el artista
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token}"
            ])->post("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/comentarios", $comentariodata);
        
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al crear el comentarioa en la API externa',
                    'details' => $dataResponse->json() // Agrega detalles de la respuesta
                ], 400);
            }
    
            // Imprimir la respuesta para verificar su estructura
            $artista = $dataResponse->json();
            \Log::info('Respuesta de la API externa:', $artista); // Registra la respuesta
    
            // Paso 7: Devolver la respuesta confirmando la creación
            return response()->json([
                'msg' => 'empleado con éxito',
                'empleado' => $empleado,
                'artista' => $artista // Devolver el artista creado
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
            // Paso 1: Validar la solicitud
            $request->validate([
                'nombre' => 'required|string|max:255', 
                'sucursal_id' => 'required|exists:sucursales,id', 
                // Asegúrate de que el editorial_id existe
            ]);
    
            // Crear una instancia de Faker
            $faker = Faker::create();
    
            // Paso 2: Buscar el libro por ID
            $empleado = Empleado::find($id);
        
            // Verificar si el libro existe
            if (!$empleado) {
                return response()->json([
                    'msg' => 'No se encontró el libro'
                ], 404); // Código 404 para "No encontrado"
            }
    
            // Paso 3: Hacer la solicitud POST para obtener el token desde la API externa
            $tokenResponse = Http::post('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/login', [
                "email" => "noe@juadsdaaaaaaaaaaazn.iker",
                "password" => "password"
            ]);
        
            // Verificar si la solicitud para el token fue exitosa
            if ($tokenResponse->failed()) {
                return response()->json([
                    'error' => 'Error al autenticar con la API externa'
                ], 400); // Código 400 para errores de autenticación
            }
        
            // Extraer el token de la respuesta
            $token = $tokenResponse->json('token');
    
            // Paso 4: Actualizar el libro en tu base de datos local
            $empleado->nombre = $request->input('nombre'); 
            $empleado->sucursal_id = $request->input('sucursal_id'); 
            $empleado->save(); 
        
            // Paso 5: Preparar los datos para la API externa
            $comentariosData = [
                'nombre' => "HOLS", 
                'nacionalidad' => "OLSKDFSMFS",
            ];
    
            // Suponiendo que tienes el ID del artista almacenado en el libro
            $artistaId = $empleado->artista_id; // Cambia esto si el ID se almacena de otra manera
        
            // Paso 6: Hacer la solicitud PUT a la API protegida para actualizar el artista
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token}"
            ])->put("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/comentarios/". $id , $comentariosData);
        
            // Verificar si la solicitud de actualización fue exitosa
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al actualizar el artista en la API externa'
                ], 400); // Código 400 para errores de la API externa
            }
        
            // Paso 7: Devolver la respuesta al cliente confirmando la actualización
            return response()->json([
                'msg' => 'Libro actualizado con éxito',
                'empleado' => $empleado,
                'data' => $dataResponse->json() // Devolver el artista actualizado si es necesario
            ], 200); // Código 200 para indicar éxito
        } catch (\Exception $e) {
            // Manejo de errores generales
            return response()->json([
                'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
            ], 500); // Código 500 para errores de servidor
        }
    }

    public function destroy($id)
    { try {
        // Paso 1: Hacer la solicitud POST para obtener el token desde la API externa
        $tokenResponse = Http::post('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/login', [
            "email" => "noe@juadsdaaaaaaaaaaazn.iker",
            "password" => "password"
        ]);

        // Verificar si la solicitud para el token fue exitosa
        if ($tokenResponse->failed()) {
            return response()->json([
                'error' => 'Error al autenticar con la API externa'
            ], 400); // Código 400 para errores de autenticación
        }

        // Extraer el token de la respuesta
        $token = $tokenResponse->json('token');

        // Paso 2: Hacer la solicitud DELETE a la API protegida para eliminar el libro
        $dataResponse = Http::withHeaders([
            'Authorization' => "Bearer {$token}"
        ])->delete("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/comentarios/". $id );

        // Verificar si la solicitud de eliminación fue exitosa
        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al eliminar el comentario en la API externa'
            ], 400); // Código 400 para errores de la API externa
        }

        // Paso 3: Devolver la respuesta al cliente confirmando la eliminación
        return response()->json([
            'msg' => 'comentario eliminado con éxito'
        ], 200); // Código 200 para indicar éxito
    } catch (\Exception $e) {
        // Manejo de errores generales
        return response()->json([
            'error' => 'Error al comunicarse con la API externa'
        ], 500); // Código 500 para errores de servidor
    }
}
}