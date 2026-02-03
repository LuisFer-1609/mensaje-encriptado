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
                
                <el-autocomplete
                    v-model="form.to"
                    :fetch-suggestions="querySearchAsync"
                    placeholder="Buscar usuario..."
                    class="transparent-input w-full"
                    :trigger-on-focus="false"
                    @select="handleSelect"
                    clearable
                >
                    <template #suffix>
                        <el-icon v-if="emailStatus === 'validating'" class="is-loading"><Loading /></el-icon>
                        <el-icon v-else-if="emailStatus === 'valid'" style="color: green"><Check /></el-icon>
                        <el-icon v-else-if="emailStatus === 'invalid' || emailStatus === 'not-found'" style="color: red"><Close /></el-icon>
                    </template>
                    
                    <template #default="{ item }">
                        <div class="flex flex-col leading-tight py-1">
                            <span class="font-bold text-gray-800">{{ item.email }}</span>
                            </div>
                    </template>
                </el-autocomplete>

                <span v-if="emailStatus === 'not-found'" class="text-xs shrink-0" style="color: red">No encontrado</span>
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
    mounted() {
        this.isMinimized = false;
    },
    methods: {
        async querySearchAsync(queryString, cb) {
            if (!queryString || queryString.length < 2) {
                cb([]);
                return;
            }

            try {
                const response = await axios.get(route('emails.find'), { 
                    params: { query: queryString } 
                });
                
                const results = response.data.data.map(user => ({
                    value: user.email, 
                    ...user
                }));
                
                cb(results);
            } catch (error) {
                console.error("Error buscando correos:", error);
                cb([]);
            }
        },

        handleSelect(item) {
            this.form.to = item.value;
            this.emailStatus = 'idle';
        },

        async checkEmail() {
            if (!this.form.to) {
                this.emailStatus = 'idle';
                return false;
            }
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(this.form.to)) {
                this.emailStatus = 'invalid';
                this.errorMessage = "Formato de correo inválido.";
                return false;
            }

            this.emailStatus = 'validating';

            try {
                const response = await axios.post('/check-email', { email: this.form.to });

                this.emailStatus = 'valid'; 
                this.recipientPublicKey = response.data.public_key;
                return true; 

            } catch (error) {
                console.error("Error verificando email", error);
                this.emailStatus = 'not-found'; 
                this.recipientPublicKey = null;
                this.errorMessage = "El usuario no existe o no tiene llaves configuradas.";
                return false; 
            }
        },

        async submitEmail() {
             this.isLoading = true;
             this.errorMessage = null;

             // Validar el correo antes de hacer nada
             const isEmailValid = await this.checkEmail();

             if (!isEmailValid) {
                 this.isLoading = false;
                 return; 
             }
             
            let payload = { ...this.form };
            
            if (window.encryptMessage) {
                try {
                    // Encriptar para el destinatario
                    const encryptedForRecipient = window.encryptMessage(this.form.message, this.recipientPublicKey);
                    
                    // Encriptar para mí (Remitente)
                    const myPublicKey = this.$page.props.auth.user.public_key;
                    let encryptedForSender = null;

                    if (myPublicKey) {
                        encryptedForSender = window.encryptMessage(this.form.message, myPublicKey);
                    } else {
                        encryptedForSender = encryptedForRecipient; 
                    }

                    const payloadContent = JSON.stringify({
                        recipient: encryptedForRecipient,
                        sender: encryptedForSender
                    });

                    payload.message = payloadContent;

                } catch (encryptError) {
                    this.errorMessage = "Error al encriptar: " + encryptError.message;
                    this.isLoading = false;
                    return;
                }
            } else {
                this.errorMessage = "Error crítico: Librería de encriptación no cargada.";
                this.isLoading = false;
                return;
            }
            
            try {
                await axios.post('/messages', payload);
                
                this.closeModal(); // Esto también resetea el form
                this.$emit('message-sent');

                ElNotification({
                    title: '¡Enviado!',
                    message: 'Mensaje seguro enviado correctamente.',
                    type: 'success',
                });

            } catch (error) {
                console.error("Error al enviar:", error);
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    this.errorMessage = Object.values(errors).flat()[0];
                } else {
                    this.errorMessage = "Ocurrió un error inesperado al enviar.";
                }
            } finally {
                this.isLoading = false;
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