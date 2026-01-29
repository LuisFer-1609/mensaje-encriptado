import JSEncrypt from 'jsencrypt';

class RSAService {
    /**
     * Desencriptar un mensaje usando una llave privada
     * @param {string} encryptedData - Mensaje encriptado
     * @param {string} privateKey - Llave privada
     * @returns {string|false} - Mensaje desencriptado o false si falla
     */
    static decrypt(encryptedData, privateKey) {
        if (!encryptedData || !privateKey) {
            console.error('RSAService: Faltan datos para desencriptar.');
            return false;
        }

        try {
            const decryptor = new JSEncrypt();
            decryptor.setPrivateKey(privateKey);
            const decrypted = decryptor.decrypt(encryptedData);

            if (!decrypted) {
                console.error('RSAService: Falló la desencriptación (posible llave incorrecta).');
                return false;
            }

            return decrypted;
        } catch (error) {
            console.error('RSAService Error:', error);
            return false;
        }
    }

    /**
     * Encripta un mensaje usando una llave pública
     * @param {string} data - Mensaje a encriptar
     * @param {string} publicKey - Llave pública
     * @returns {string|false} - Mensaje encriptado o false si falla
     */
    static encrypt(data, publicKey) {
        if (!data || !publicKey) {
            console.error('RSAService: Faltan datos para encriptar.');
            return false;
        }

        try {
            const encryptor = new JSEncrypt();
            encryptor.setPublicKey(publicKey);
            const encrypted = encryptor.encrypt(data);

            return encrypted;
        } catch (error) {
            console.error('RSAService Error:', error);
            return false;
        }
    }
}

export default RSAService;
