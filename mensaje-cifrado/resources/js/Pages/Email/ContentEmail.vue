<template>
    <section v-if="modelValue" @click.self="closeModal" class="absolute inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg h-auto bg-white shadow-2xl rounded-2xl p-6 transition-all transform animate-in fade-in zoom-in duration-200">
            <header class="flex items-center justify-end mb-6">
                <button class="w-8 h-8 bg-black/5 flex items-center justify-center rounded-full backdrop-blur-md" @click="closeModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
                </button>
            </header>

            <div class="h-auto max-h-52 overflow-y-auto">
                <p class=" sticky top-0 font-semibold bg-white mb-6">
                    Asunto: 
                    <span class="font-normal">
                        {{  email?.subject || 'No se encontró el asunto del correo'  }}
                    </span>
                </p>
                <div class="font-semibold">
                    <p class="mb-2">Contenido del correo:</p>
                    <div class="p-3 bg-gray-50 rounded-lg min-h-[100px]">
                        <p v-if="decryptedContent" class="font-normal break-words whitespace-pre-wrap">{{ decryptedContent }}</p>
                        <p v-else class="text-gray-400 italic">Desencriptando mensaje seguro...</p>
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
            required: true
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
            if (val && this.email && this.email.content) {
                this.decryptMessage(this.email.content);
            } else {
                this.decryptedContent = null;
            }
        }
    },
    methods: {
        closeModal() {
            this.$emit('update:modelValue', false);
            this.decryptedContent = null;
        },
        decryptMessage(encryptedContent) {
            this.isDecrypting = true;
            this.decryptedContent = "Desencriptando...";
            
            if (window.decryptMessage) {
                 setTimeout(() => {
                     try {
                        let ciphertext = encryptedContent;
                        
                        // Intentar parsear si es JSON (Dual Encryption)
                        try {
                            if (encryptedContent.trim().startsWith('{')) {
                                const payload = JSON.parse(encryptedContent);
                                if (payload.sender && payload.recipient) {
                                    // Determinar si soy el remitente o el destinatario
                                    // Usamos la propiedad is_sent que viene del backend
                                    if (this.email.is_sent) {
                                        ciphertext = payload.sender; // Desencriptar copia del remitente
                                    } else {
                                        ciphertext = payload.recipient; // Desencriptar copia del destinatario
                                    }
                                }
                            }
                        } catch (parseError) {
                            // No es JSON, continuar con el contenido original (Legacy)
                            console.warn("No es un payload dual, intentando como legacy string...", parseError);
                        }

                        const decrypted = window.decryptMessage(ciphertext, this.privateKey);
                        
                        if (decrypted) {
                            this.decryptedContent = decrypted;
                        } else {
                            // Si falla la desencriptación (retorna null/false)
                             if (this.email.is_sent) {
                                 this.decryptedContent = "Este mensaje no se puede leer (posiblemente antiguo o sin copia para remitente).";
                             } else {
                                 this.decryptedContent = "Error al desencriptar. Verifica tus llaves.";
                             }
                        }
                     } catch (e) {
                         console.error("Critical error:", e);
                         this.decryptedContent = "Error inesperado: " + e.message;
                     }
                     this.isDecrypting = false;
                 }, 500);
            }
        } 
    }
}
</script>