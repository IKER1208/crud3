<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Editorial;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Faker\Factory as Faker;

class EditorialController extends Controller
{
    public function index()
    {
        try {
            // Paso 1: Obtener todos los Edio$editorials de tu base de datos local
            $editoriales = Editorial::all();
    
            // Paso 2: Hacer la solicitud POST para obtener el token desde la API externa
            $tokenResponse = Http::post('http://192.168.127.1:52387/login', [
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
            ])->get('http://192.168.127.1:52387/generos');
    
            // Verificar si la solicitud de datos fue exitosa
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400); // Código 400 para errores de la API externa
            }
    
            // Paso 4: Devolver la respuesta al cliente con los Edio$editorials y los artistas
            return response()->json([
                'msg' => 'Editoriales encontrados',
                'editorial' => $editoriales,
                'generos' => $dataResponse->json() // Cambié 'data' a 'artistas' para mayor claridad
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
        $editorial = Editorial::find($id);

        // Verificar si el cocinero existe
        if (!$editorial) 
        {
            return response()->json([
                'msg' => 'No se encontró el la editorial'
            ], 404); // Código 404 para "No encontrado"
        }

        try 
        {
            // Paso 1: Hacer la solicitud POST para obtener el token desde la API externa
            $tokenResponse = Http::post('http://192.168.127.1:52387/login', [
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
            ])->get('http://192.168.127.1:52387/generos/' . $id);

            // Verificar si la solicitud de datos fue exitosa
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400); // Código 400 para errores de la API externa
            }

            // Paso 3: Devolver la respuesta al cliente con los datos del cocinero y la API
            return response()->json([
                'msg' => 'editorial encontrado',
                '------'=>'-------',
                'Editorial' => $editorial,
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
                'pais' => 'required|string|max:255',
            ]);
    
            // Crear una instancia de Faker
            $faker = Faker::create();
    
            // Paso 2: Crear un nuevo libro
            $editorial = new Editorial();
            $editorial->nombre = $request->input('nombre');
            $editorial->pais = $request->input('pais');
            $editorial->save();
    
            // Paso 3: Obtener el token
            $tokenResponse = Http::post('http://192.168.127.1:52387/login', [
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
            $generoData = [
                'nombre' => $faker->name,
                'descripcion' => $faker->sentence
            ];
    
            // Paso 5: Crear el artista
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token}"
            ])->post("http://192.168.127.1:52387/generos", $generoData);
        
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al crear el genero en la API externa',
                    'details' => $dataResponse->json() // Agrega detalles de la respuesta
                ], 400);
            }
    
            // Imprimir la respuesta para verificar su estructura
            $artista = $dataResponse->json();
            \Log::info('Respuesta de la API externa:', $artista); // Registra la respuesta
    
            // Paso 7: Devolver la respuesta confirmando la creación
            return response()->json([
                'msg' => 'Libro creado con éxito',
                'libro' => $editorial,
                'genero' => $dataResponse->json() // Devolver el artista creado
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {try {
        // Paso 1: Validar la solicitud
        $request->validate([
            'nombre' => 'required|string|max:255',
            'pais' => 'required|string|max:255', // Asegúrate de que el editorial_id existe
        ]);

        // Crear una instancia de Faker
        $faker = Faker::create();

        // Paso 2: Buscar el libro por ID
        $editorial = Editorial::find($id);
    
        // Verificar si el libro existe
        if (!$editorial) {
            return response()->json([
                'msg' => 'No se encontró el editorial'
            ], 404); // Código 404 para "No encontrado"
        }

        // Paso 3: Hacer la solicitud POST para obtener el token desde la API externa
        $tokenResponse = Http::post('http://192.168.127.1:52387/login', [
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
        $editorial->nombre = $request->input('nombre'); 
        $editorial->pais = $request->input('pais'); 
        $editorial->save(); 
    
        // Paso 5: Preparar los datos para la API externa
        $generoData = [
            'nombre' => "holaa",
            'descripcion' => $faker->sentence
        ];
        // Paso 6: Hacer la solicitud PUT a la API protegida para actualizar el artista
        $dataResponse = Http::withHeaders([
            'Authorization' => "Bearer {$token}"
        ])->put("http://192.168.127.1:52387/generos/". $id , $generoData);
    
        // Verificar si la solicitud de actualización fue exitosa
        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al actualizar el genero en la API externa'
            ], 400); // Código 400 para errores de la API externa
        }
    
        // Paso 7: Devolver la respuesta al cliente confirmando la actualización
        return response()->json([
            'msg' => 'editorial actualizado con éxito',
            'editorial' => $editorial,
            'artista' => $dataResponse->json() // Devolver el artista actualizado si es necesario
        ], 200); // Código 200 para indicar éxito
    } catch (\Exception $e) {
        // Manejo de errores generales
        return response()->json([
            'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
        ], 500); // Código 500 para errores de servidor
    }
}


    public function destroy($id)
    {
        try {
            // Paso 1: Hacer la solicitud POST para obtener el token desde la API externa
            $tokenResponse = Http::post('http://192.168.127.1:52387/login', [
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
            ])->delete("http://192.168.127.1:52387/generos/". $id );
    
            // Verificar si la solicitud de eliminación fue exitosa
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al eliminar el genero en la API externa'
                ], 400); // Código 400 para errores de la API externa
            }
    
            // Paso 3: Devolver la respuesta al cliente confirmando la eliminación
            return response()->json([
                'msg' => 'Libro eliminado con éxito',
                'genero' => $dataResponse->json()
            ], 200); // Código 200 para indicar éxito
        } catch (\Exception $e) {
            // Manejo de errores generales
            return response()->json([
                'error' => 'Error al comunicarse con la API externa'
            ], 500); // Código 500 para errores de servidor
        }
    }
    
    }

