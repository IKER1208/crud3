<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Cliente;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Faker\Factory as Faker;



class ClienteController extends Controller
{
    public function index(){
        
        try {
            $clientes = Cliente::all();    

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
            ])->get('http://192.168.127.1:52387/canciones');
    
            // Verificar si la solicitud de datos fue exitosa
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al obtener datos de la API externa'
                ], 400); // Código 400 para errores de la API externa
            }
    
            // Paso 4: Devolver la respuesta al cliente con los libros y los artistas
            return response()->json([
                'msg' => 'clientes encontrados',
                'clientes' => $clientes,
                'canciones' => $dataResponse->json() // Cambié 'data' a 'artistas' para mayor claridad
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
        try{
        $cliente = Cliente::find($id);
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }
        return response()->json($cliente, 200);
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
    ])->get('http://192.168.127.1:52387/canciones');

    // Verificar si la solicitud de datos fue exitosa
    if ($dataResponse->failed()) {
        return response()->json([
            'error' => 'Error al obtener datos de la API externa'
        ], 400); // Código 400 para errores de la API externa
    }

    // Paso 4: Devolver la respuesta al cliente con los libros y los artistas
    return response()->json([
        'msg' => 'clientes encontrados',
        'cliente' => $cliente,
        'artistas' => $dataResponse->json() // Cambié 'data' a 'artistas' para mayor claridad
    ], 200); // Código 200 para indicar éxito
}    catch (\Exception $e) {
    // Manejo de errores generales
    return response()->json([
        'error' => 'Error al comunicarse con la API externa'
    ], 500); // Código 500 para errores de servidor
    }
    }
    public function store(Request $request)
    {
        try {
        $validate = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:clientes,email',
        ]);
        $faker = Faker::create();

        if ($validate->fails()) {
            return response()->json(['error' => $validate->messages()], 400);
        }

        $cliente = new Cliente();
        $cliente->nombre = $request->nombre;
        $cliente->email = $request->email;
        $cliente->save();

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
        $cancionesData = [
            'nombre' => $faker->name,
            'duracion' => $faker ->numberBetween(1, 90),
            'album_id' => $faker->numberBetween(1, 90),
            'genero_id' => $faker->numberBetween(1, 90),
            'artista_id' => $faker->numberBetween(1, 90),
        ];

        // Paso 5: Crear el artista
        $dataResponse = Http::withHeaders([
            'Authorization' => "Bearer {$token}"
        ])->post("http://192.168.127.1:52387/canciones", $cancionesData);
    
        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al crear el artista en la API externa',
                'details' => $dataResponse->json() // Agrega detalles de la respuesta
            ], 400);
        }

        // Imprimir la respuesta para verificar su estructura
        $canciones = $dataResponse->json();
        \Log::info('Respuesta de la API externa:', $canciones); // Registra la respuesta

        // Paso 7: Devolver la respuesta confirmando la creación
        return response()->json([
            'msg' => 'cliente creado con éxito',
            'cliente' => $cliente,
            'cancion' => $canciones // Devolver el artista creado
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
            'email' => 'required|email|max:255|unique:clientes,email', // Asegúrate de que el editorial_id existe
        ]);
        $faker = Faker::create();

        // Buscar el cliente
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json([
                'error' => 'Cliente no encontrado'
            ], 404);
        }

        // Autenticación con la API externa
        $tokenResponse = Http::post('http://192.168.127.1:52387/login', [
            "email" => "noe@juadsdaaaaaaaaaaazn.iker",
            "password" => "password"
        ]);

        // Verificar si la solicitud para el token fue exitosa
        if ($tokenResponse->failed()) {
            return response()->json([
                'error' => 'Error al autenticar con la API externa'
            ], 400);
        }

        $token = $tokenResponse->json('token');

        // Actualizar datos del cliente
        $cliente->nombre = $request->input('nombre');
        $cliente->email = $request->input('email');
        $cliente->save();

        // Datos para actualizar la canción en la API externa
        $cancionesData = [
            'nombre' => $faker->name,
            'duracion' => $faker->numberBetween(1, 90),
            'album_id' => $faker->numberBetween(8, 90),
            'genero_id' => $faker->numberBetween(8, 90),
            'artista_id' => $faker->numberBetween(8, 90),
        ];

        // Hacer la solicitud PUT a la API protegida
        $dataResponse = Http::withHeaders([
            'Authorization' => "Bearer {$token}"
        ])->put("http://192.168.127.1:52387/canciones/" . $id, $cancionesData);

        // Verificar si la solicitud de actualización fue exitosa
        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al actualizar el artista en la API externa',
                'response' => $dataResponse->json() // Mostrar detalles de la respuesta
            ], 400);
        }
        

        // Devolver la respuesta al cliente confirmando la actualización
        return response()->json([
            'msg' => 'Libro actualizado con éxito',
            'cliente' => $cliente,
            'cancion' => $dataResponse->json(), 
        ], 200);
    } catch (\Exception $e) {
        // Manejo de errores generales
        return response()->json([
            'error' => 'Error al comunicarse con la API externa: ' . $e->getMessage()
        ], 500);
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
            ])->delete("http://192.168.127.1:52387/canciones/". $id );
    
            // Verificar si la solicitud de eliminación fue exitosa
            if ($dataResponse->failed()) {
                return response()->json([
                    'error' => 'Error al eliminar el cancion en la API externa'
                ], 400); // Código 400 para errores de la API externa
            }
    
            // Paso 3: Devolver la respuesta al cliente confirmando la eliminación
            return response()->json([
                'msg' => 'cancion eliminado con éxito'
            ], 200); // Código 200 para indicar éxito
        } catch (\Exception $e) {
            // Manejo de errores generales
            return response()->json([
                'error' => 'Error al comunicarse con la API externa'
            ], 500); // Código 500 para errores de servidor
        }
    }
    
    }