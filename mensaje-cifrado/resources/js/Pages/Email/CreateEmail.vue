<template>
    <section v-if="modelValue" class="fixed inset-x-0 bottom-0 md:right-10 md:left-auto md:w-96 md:max-w-96 w-full bg-white shadow-2xl md:rounded-t-xl z-50 border-t border-gray-200" :class="isMinimized ? 'h-12' : 'h-[80vh] md:h-auto'">
        <header class="flex items-center justify-between h-10 bg-gray-200 p-3">
            <span class="text-sm font-semibold">Nuevo mensaje</span>
            <div class="flex flex-row gap-1">
                <button class="cursor-pointer p-1" @click="minimizeModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-compact-down"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 11l8 3l8 -3" /></svg>
                </button>
                <button class="cursor-pointer p-1" @click="closeModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
                </button>
            </div>
        </header>
        <form class="p-2 flex flex-col h-[calc(100%-2.5rem)]">
            <div class="flex items-center gap-2 border-b-2 p-2 shrink-0">
                <span class="text-sm">Para:</span>
                <el-input class="transparent-input" v-model="form.to" @input="debouncedCheckEmail">
                    <template #suffix>
                        <el-icon v-if="emailStatus === 'validating'" class="is-loading"><Loading /></el-icon>
                        <el-icon v-else-if="emailStatus === 'valid'" style="color: green"><Check /></el-icon>
                        <el-icon v-else-if="emailStatus === 'invalid' || emailStatus === 'not-found'" style="color: red"><Close /></el-icon>
                    </template>
                </el-input>
                <span v-if="emailStatus === 'not-found'" class="text-xs" style="color: red">No encontrado</span>
            </div>
            <div class="flex items-center gap-2 border-b-2 p-2 shrink-0">
                <span class="text-sm">Asunto:</span>
                <el-input class="transparent-input" v-model="form.subject"></el-input>
            </div>
            <div class="p-2 flex-1 min-h-0">
                <el-input
                    type="textarea"
                    placeholder="Escribe tu mensaje aquí..."
                    v-model="form.message"
                    class="w-full h-full transparent-input"
                    resize="none"
                >
                </el-input>
            </div>

            <div class="flex items-center justify-end shrink-0 p-2">
                <el-button type="primary" :loading="isLoading" @click.prevent="submitEmail">Enviar</el-button>
            </div>
            
            <div v-if="errorMessage" class="fixed top-4 right-4 z-[9999] max-w-sm">
                 <el-alert :title="errorMessage" type="error" show-icon />
            </div>
        </form>
    </section>
</template>

<script>
import debounce from 'lodash/debounce';
import { ElNotification } from 'element-plus';

export default {
    props: {
        modelValue: {
            type: Boolean,
            required: true
        }
    },
    data() {
        return {
            isLoading: false,
            isMinimized: false,
            errorMessage: null,
            
            // Estado de validación del email
            emailStatus: 'idle', // idle(En espera), validando, validado, invalidado, no-encontrado
            recipientPublicKey: null,

            form: {
                to: '',
                subject: '',
                message: ''
            }
        }
    },
    emits: ["update:modelValue", "message-sent"],
    watch: {
        'form.to': function() {
            this.debouncedCheckEmail();
        }
    },
    created() {
        // Tiempo de espera
        this.debouncedCheckEmail = debounce(this.checkEmail, 500);
    },
    mounted() {
        this.isMinimized = false;
    },
    methods: {
        async checkEmail() {
            if (!this.form.to) {
                this.emailStatus = 'idle';
                return;
            }
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(this.form.to)) {
                this.emailStatus = 'invalid';
                return;
            }

            this.emailStatus = 'validating';

            try {
                const response = await axios.post('/check-email', { email: this.form.to });
                if (response.data.exists) {
                    this.emailStatus = 'Encontrado';
                    this.recipientPublicKey = response.data.public_key;
                } else {
                    this.emailStatus = 'No encontrado';
                    this.recipientPublicKey = null;
                }
            } catch (error) {
                console.error("Error verificando email", error);
                this.emailStatus = 'idle'; // O error
                this.recipientPublicKey = null;
            }
        },

        async submitEmail() {
             this.isLoading = true;
             this.errorMessage = null;

             if (!this.recipientPublicKey) {
                 this.errorMessage = "No se ha encontrado la llave pública del destinatario. Verifica el correo.";
                 this.isLoading = false;
                 return;
             }
             
            let payload = { ...this.form };
            
            if (window.encryptMessage) {
                // 1. Encriptar para el destinatario
                const encryptedForRecipient = window.encryptMessage(this.form.message, this.recipientPublicKey);
                
                // 2. Encriptar para mí mismo (Remitente)
                // Usamos la llave pública del usuario autenticado
                const myPublicKey = this.$page.props.auth.user.public_key;
                let encryptedForSender = null;

                if (myPublicKey) {
                    encryptedForSender = window.encryptMessage(this.form.message, myPublicKey);
                } else {
                    console.warn("No se encontró la llave pública del remitente (tu usuario). No podrás leer este mensaje en Enviados.");
                    // Fallback: Si no hay llave propia, guardamos null o el mismo del recipient (aunque no servirá)
                    // Preferimos guardar null o manejarlo. Para este caso, guardaremos encryptedForRecipient duplicado 
                    // o un string vacío. Pero lo ideal es que siempre exista.
                    encryptedForSender = encryptedForRecipient; 
                }

                // 3. Crear payload JSON
                const payloadContent = JSON.stringify({
                    recipient: encryptedForRecipient,
                    sender: encryptedForSender
                });

                payload.message = payloadContent;
            } else {
                console.error("Función window.encryptMessage no encontrada.");
                return;
            }
            
            try {
                const response = await axios.post('/messages', payload);
                
                this.closeModal();
                this.isLoading = false;
                
                this.$emit('message-sent');

                // Notificacion de exito
                ElNotification({
                    title: '¡Éxito!',
                    message: 'Correo enviado y guardado correctamente.',
                    type: 'success',
                });

            } catch (error) {
                console.error("Error al enviar:", error);
                this.isLoading = false;
                
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    this.errorMessage = Object.values(errors).flat()[0];
                } else {
                    this.errorMessage = "Ocurrió un error inesperado al enviar el correo.";
                }
            }
        },
        resetForm() {
            this.form = {
                to: '',
                subject: '',
                message: ''
            }
        },
        minimizeModal() {
            this.isMinimized = !this.isMinimized;
        },
        closeModal() {
            this.$emit('update:modelValue', false);
            this.resetForm();
        }
    }
}
</script>

<style scoped>
.transparent-input :deep(.el-input__wrapper) {
  background-color: transparent !important;
  box-shadow: none !important; /* Element Plus usa box-shadow en lugar de border */
  border: none !important;
  padding: 0; /* Opcional: si quieres eliminar el padding interno */
}

/* Opcional: Estilo cuando el input está enfocado */
.transparent-input :deep(.el-input__wrapper.is-focus) {
  box-shadow: none !important;
}

/* Target específico para el textarea */
.transparent-input :deep(.el-textarea__inner) {
  background-color: transparent !important;
  box-shadow: none !important; /* Quita el borde/sombra */
  border: none !important;     /* Asegura que no haya borde */
  resize: none;                /* Opcional: quita el tirador para cambiar tamaño */
}

/* Color del placeholder para el textarea */
.transparent-input :deep(.el-textarea__inner::placeholder) {
  color: rgba(255, 255, 255, 0.6) !important;
}

/* Quitar el borde azul al hacer clic (focus) */
.transparent-input :deep(.el-textarea__inner:focus) {
  box-shadow: none !important;
  outline: none !important;
}
</style>