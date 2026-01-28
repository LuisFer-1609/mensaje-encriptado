<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use phpseclib3\Crypt\RSA;

class EncryptionController extends Controller
{
    public function createKey()
    {
        // Luis desde aqui se crea la llave RSA solo soporta 2048 bits asi que aguas.
        $key = RSA::createKey(2048);

        $privateKey = $key->toString('PKCS1');
        $publicKey = $key->getPublicKey()->toString('PKCS1');

        return response()->json([
            'publicKey' => $publicKey,
            'privateKey' => $privateKey,
        ]);
    }

    public function desencriptar(Request $request)
    {
        $mensajeCifrado = $request->input('mensaje_cifrado');
        $privateKeyString = session('private_key');

        if (!$privateKeyString || !$mensajeCifrado) {
            return "Error: Sesión expirada o mensaje vacío. Regresa a /encriptar";
        }

        try {
            //Luis aqui se carga la privateKey PKCS1
            $privateKey = RSA::loadFormat('PKCS1', $privateKeyString)
                ->withPadding(RSA::ENCRYPTION_PKCS1); // Formato obligatorio para JSEncrypt no lo quites

            //Aqui decodificamos el mensaje que viene desde el front
            $datosBinarios = base64_decode($mensajeCifrado);

            //Con este desencriptamos el mensaje que ya viene dentro de $mensajeCifrado
            $desencriptado = $privateKey->decrypt($datosBinarios);

            if ($desencriptado === false) {
                throw new \Exception("No se pudo desencriptar (posible desajuste de llaves)");
            }

            return "<h3>Mensaje recuperado:</h3> <p>" . htmlspecialchars($desencriptado) . "</p>";

        } catch (\Exception $e) {
            return "Ocurrio un error: " . $e->getMessage();
        }
    }
}