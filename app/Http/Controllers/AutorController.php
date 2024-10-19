<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Autor;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Faker\Factory as Faker;

class AutorController extends Controller
{
    public function index(Request $request)
{
    try {
        // Paso 1: Obtener todos los autores de tu base de datos local
        $autores = Autor::all();

        // Paso 2: Obtener el token externo desde los encabezados de la solicitud
        $externalToken = $request->header('Authorization');

        // Verificar si el token externo fue proporcionado
        if (!$externalToken) {
            return response()->json([
                'error' => 'Token externo no proporcionado'
            ], 401); // Código 401 para falta de autenticación
        }

        // Paso 3: Usar el token para hacer la solicitud GET a la API protegida
        $dataResponse = Http::withHeaders([
            'Authorization' => "Bearer {$externalToken}"
        ])->get('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/albums');

        // Verificar si la solicitud de datos fue exitosa
        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al obtener datos de la API externa'
            ], 400); // Código 400 para errores en la API externa
        }

        // Paso 4: Devolver la respuesta al cliente con los autores y los albums
        return response()->json([
            'msg' => 'Autores y albums encontrados',
            'autores' => $autores,
            'albums' => $dataResponse->json()
        ], 200); // Código 200 para indicar éxito
    } catch (\Exception $e) {
        // Manejo de errores generales
        return response()->json([
            'error' => 'Error al comunicarse con la API externa'
        ], 500); // Código 500 para errores del servidor
    }
}

    public function show($id)
    {
       // Buscar al cocinero por ID
       $autores = Autor::find($id);

       // Verificar si el cocinero existe
       if (!$autores) 
       {
           return response()->json([
               'msg' => 'No se encontró el autor'
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
           ])->get('https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/albums/' . $id);

           // Verificar si la solicitud de datos fue exitosa
           if ($dataResponse->failed()) {
               return response()->json([
                   'error' => 'Error al obtener datos de la API externa'
               ], 400); // Código 400 para errores de la API externa
           }

           // Paso 3: Devolver la respuesta al cliente con los datos del cocinero y la API
           return response()->json([
               'msg' => 'Libro encontrado',
               '------'=>'-------',
               'autores' => $autores,
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
                'bio' => 'required|string|max:255',
            ]);
    
            // Crear una instancia de Faker
            $faker = Faker::create();
    
            // Paso 2: Crear un nuevo libro
            $autor = new Autor();
            $autor->nombre = $request->input('nombre'); 
            $autor->bio = $request->input('bio'); 
            $autor->save();
    
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
            $albumData = [
                'nombre' => $faker->firstName,
                'fecha_lanzamiento'=> $faker->date($format = 'Y-m-d', $max = 'now'),
                'artista_id' => $faker->numberBetween(80,85),
            
            ];
    
            // Paso 5: Crear el artista
            $dataResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token}"
            ])->post("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/albums", $albumData);
        
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al crear el artista en la API externa',
                    'details' => $dataResponse->json() // Agrega detalles de la respuesta
                ], 400);
            }
    
            // Imprimir la respuesta para verificar su estructura
            $albumData = $dataResponse->json();
            \Log::info('Respuesta de la API externa:', $albumData); // Registra la respuesta
    
            // Paso 7: Devolver la respuesta confirmando la creación
            return response()->json([
                'msg' => 'Autor creado con éxito',
                'libro' => $autor,
                'artista' => $albumData // Devolver el artista creado
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
            'bio' => 'string|nullable', // Asegúrate de validar la bio si es necesaria
        ]);

        // Paso 2: Buscar el autor por ID
        $autores = Autor::find($id);
        $faker = Faker::create();

        // Verificar si el autor existe
        if (!$autores) {
            return response()->json([
                'msg' => 'No se encontró el autor'
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
        
        // Paso 4: Actualizar el autor en tu base de datos local
        $autores->nombre = $request->input('nombre');
        $autores->bio = $request->input('bio', $autores->bio); // Mantener la bio anterior si no se proporciona
        $autores->save();
    
        // Paso 5: Preparar los datos para la API externa
        // Si no necesitas Faker, puedes reemplazarlo con valores reales o eliminarlos
        $albumData = [
            'nombre' => "hishdoash",
            'fecha_lanzamiento'=> $faker->date($format = 'Y-m-d', $max = 'now'),
            'artista_id' => $faker->numberBetween(80,85),
        
        ];

        // Paso 6: Hacer la solicitud PUT a la API protegida para actualizar el artista
        $dataResponse = Http::withHeaders([
            'Authorization' => "Bearer {$token}"
        ])->put("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/albums/". $id, $albumData);
    
        // Verificar si la solicitud de actualización fue exitosa
        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al actualizar la album en la API externa'
            ], 400); // Código 400 para errores de la API externa
        }
    
        // Paso 7: Devolver la respuesta al cliente confirmando la actualización
        return response()->json([
            'msg' => 'Autor actualizado con éxito',
            'autores' => $autores,
            'album' => $dataResponse->json() // Devolver el artista actualizado si es necesario
        ], 200); // Código 200 para indicar éxito
    } catch (\Exception $e) {
        // Manejo de errores generales
        return response()->json([
            'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
        ], 500); // Código 500 para errores de servidor
    }
}


    public function destroy($id)
    {try {
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
        ])->delete("https://7902-2806-101e-b-2c16-794d-213b-c523-e874.ngrok-free.app/albums/". $id );

        // Verificar si la solicitud de eliminación fue exitosa
        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al eliminar el album en la API externa'
            ], 400); // Código 400 para errores de la API externa
        }

        // Paso 3: Devolver la respuesta al cliente confirmando la eliminación
        return response()->json([
            'msg' => 'autor eliminado con éxito'
        ], 200); // Código 200 para indicar éxito
    } catch (\Exception $e) {
        // Manejo de errores generales
        return response()->json([
            'error' => 'Error al comunicarse con la API externa'
        ], 500); // Código 500 para errores de servidor
    }
    }
}
