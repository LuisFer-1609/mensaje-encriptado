<!DOCTYPE html>
<html>
<head>
    <title>Encriptación RSA Segura</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsencrypt/3.3.2/jsencrypt.min.js"></script>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; padding-top: 50px; }
        .card { border: 1px solid #ccc; padding: 20px; border-radius: 8px; width: 400px; }
        textarea { width: 100%; height: 100px; margin-bottom: 10px; }
        button { width: 100%; background: #2d3748; color: white; border: none; padding: 10px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="card">
        <h3>Mensaje para el Servidor</h3>
        <form id="formSeguro" action="/desencriptar" method="POST">
            @csrf
            <textarea id="texto_plano" placeholder="Escribe tu secreto aquí..."></textarea>
            
            <input type="hidden" name="mensaje_cifrado" id="mensaje_cifrado">
            
            <button type="submit">Enviar Encriptado (RSA)</button>
        </form>
    </div>

    <script>
        document.getElementById('formSeguro').onsubmit = function(e) {
            e.preventDefault(); // Detenemos el envío un segundo

            const encrypt = new JSEncrypt();
            // Esta llave viene del controlador (PHP)
            const publicKey = `{!! $publicKey !!}`; 
            
            encrypt.setPublicKey(publicKey);
            
            const textoOriginal = document.getElementById('texto_plano').value;
            const cifrado = encrypt.encrypt(textoOriginal);
            
            if (cifrado) {
                document.getElementById('mensaje_cifrado').value = cifrado;
                this.submit(); // Ahora sí lo enviamos
            } else {
                alert("Error al encriptar. Revisa la llave pública.");
            }
        };
    </script>
</body>
</html>