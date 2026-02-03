<template>
    <section v-if="modelValue" @click.self="closeModal" class="absolute inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg h-auto bg-white shadow-2xl rounded-2xl p-6 transition-all transform animate-in fade-in zoom-in duration-200">
            <header class="flex items-center justify-end mb-6">
                <button class="w-8 h-8 bg-black/5 flex items-center justify-center rounded-full backdrop-blur-md hover:bg-black/10 transition-colors" @click="closeModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
                </button>
            </header>

            <div class="h-auto max-h-52 overflow-y-auto">
                <p class="sticky top-0 font-semibold bg-white mb-6">
                    Asunto: 
                    <span class="font-normal text-gray-700">
                        {{ email?.subject || 'Sin asunto' }}
                    </span>
                </p>
                <div class="font-semibold">
                    <p class="mb-2 text-sm text-gray-500 uppercase tracking-wider">Contenido del correo:</p>
                    <div class="p-4 bg-gray-50 rounded-xl min-h-[120px] border border-gray-100">
                        <p v-if="decryptedContent && !isDecrypting" class="font-normal text-gray-800 break-words whitespace-pre-wrap leading-relaxed">
                            {{ decryptedContent }}
                        </p>
                        
                        <div v-else class="flex flex-col items-center justify-center h-20 text-gray-400">
                            <svg class="animate-spin h-5 w-5 mb-2" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="italic text-sm">Desencriptando mensaje seguro...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    props: {
        modelValue: {
            type: Boolean,
            required: true
        },
        email: {
            type: Object,
            required: true
        },
        privateKey: {
            type: String,
            required: false,
            default: ''
        }
    },
    data() {
        return {
            decryptedContent: null,
            isDecrypting: false
        }
    },
    emits: ["update:modelValue"],
    watch: {
        modelValue(val) {
            if (val && this.email?.content) {
                this.handleDecryption(this.email.content);
            } else {
                this.decryptedContent = null;
            }
        }
    },
    methods: {
        closeModal() {
            this.$emit('update:modelValue', false);
            setTimeout(() => { this.decryptedContent = null; }, 300);
        },

        async handleDecryption(encryptedContent) {
            this.isDecrypting = true;
            
            // Prioridad de llaves: Prop > sessionStorage
            const keyToUse = this.privateKey || sessionStorage.getItem('private_key');
            console.log(keyToUse);


            if (!keyToUse) {
                this.decryptedContent = "⚠️ Error: No se encontró la llave privada para desencriptar este mensaje.";
                this.isDecrypting = false;
                return;
            }

            // Simulación de delay para feedback visual de seguridad
            setTimeout(() => {
                try {
                    let ciphertext = encryptedContent;

                    // --- LÓGICA DE PARSEO DUAL ---
                    try {
                        // Verificamos si es un JSON (formato {sender: '...', recipient: '...'})
                        if (typeof encryptedContent === 'string' && encryptedContent.trim().startsWith('{')) {
                            const payload = JSON.parse(encryptedContent);
                            
                            if (payload.sender && payload.recipient) {
                                // Si yo envié el correo, uso la copia cifrada con MI llave pública
                                // Si yo recibí el correo, uso la copia cifrada con MI llave pública (del destinatario)
                                ciphertext = this.email.is_sent ? payload.sender : payload.recipient;
                            }
                        }
                    } catch (parseError) {
                        console.log("Mensaje en formato simple (Legacy)");
                    }

                    // --- PROCESO DE DESENCRIPTACIÓN ---
                    if (window.decryptMessage) {
                        const decrypted = window.decryptMessage(ciphertext, keyToUse);
                        
                        if (decrypted) {
                            this.decryptedContent = decrypted;
                        } else {
                            // Fallo en la función de desencriptación
                            this.decryptedContent = this.email.is_sent 
                                ? "No se pudo desencriptar tu copia de seguridad de este mensaje." 
                                : "Error de autenticidad: La llave privada no corresponde a este mensaje.";
                        }
                    } else {
                        throw new Error("Librería de desencriptación no disponible.");
                    }

                } catch (error) {
                    console.error("Error crítico en el componente:", error);
                    this.decryptedContent = "Error técnico: " + error.message;
                } finally {
                    this.isDecrypting = false;
                }
            }, 600);
        }
    }
}
</script>