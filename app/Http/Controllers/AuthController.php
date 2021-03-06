<?php
//
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Personas;
use App\personal_access_tokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\MessageBag;

class AuthController extends Controller
{
  public function Validacion(Request $request)
  {
      //Valida al usuario
  if($request->user()->tokenCan('Administrador')){
  return response()->json(["Usuario administrador"=>Persona::all()],200);}
  if($request->user()->tokenCan('Supervisor')){
  return response()->json(["Usuario Supervisor"=>$request->user()],200);}
  abort(401,"Invalido");
  } 
  //Ingreso de admin y creacion de su token
  
  public function Login(Request $request)
  {
      //Solicitud de correo y password
    $request->validate([
        'Correo'=>'required', 'password'=>'required',
    ]);
    //Va guardar en la variable lo que encuentre
    $Personas= Personas::where('Correo',$request->Correo)->first();
    if(!$Personas|| !Hash::check($request->password,$Personas->password))
    {
    throw ValidationException::withMessages([
        'Correo|password'=>["Datos erroneos"],
    ]);
    }
    $token=$Personas->createToken($request->Correo,['Administrador','Supervisor'])->plainTextToken;
    return response()->json(["token"=>$token],201);
    
  }  
  public function Loginadmin(Request $request)
  {
    //Genera el Token para el Admin
    $Personas= Personas::find(4)->first();
    $token=$Personas->createToken($request->Correo,['Administradores'])->plainTextToken;
    return response()->json(["Token"=>$token],201);
  }
  public function Logout(Request $request)
  {
      //Rompe los token creados
    return response()->json(["Salir"=>$request->user()->tokens()->delete()],200);
  }

  public function registro(Request $request)
  {
      //Registro de un nuevo usuario
    $request->validate([
        'Correo'=>'required|email',
        'password'=>'required',
        'Nombre'=>'required',
        'Edad'=>'required',
    ]);
        $Personas = new Personas();
        $Personas->Nombre=$request->Nombre;
        $Personas->Edad=$request->Edad;
        $Personas->Correo=$request->Correo;
        $Personas->password=Hash::make($request->password);
        if($Personas->save())
        {
        return response()->json($Personas,200);}
        return abort(422,"Fallo registro");
  } 
  public function Update(Request $request)
    {
        $Personas= personal_access_tokens::find($request->id);  
        $Personas->abilities=$request->Permiso;  
        if($Personas->save())
        return response()->json(["Se modifico el permiso al usuario"=>$Personas]);   
        return response()->json(null,400);
        //Route::put('Modificarpermiso','AuthController@Update');
    }
  }
