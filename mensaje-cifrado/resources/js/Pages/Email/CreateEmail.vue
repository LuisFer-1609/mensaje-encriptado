<template>
    <section v-if="modelValue" class="absolute bottom-0 right-10 w-96 max-w-96 bg-white shadow-xl overflow-hidden" :class="isMinimized ? 'h-10' : 'h-auto'">
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
        <form class="p-2">
            <div class="flex items-center gap-2 border-b-2 p-2">
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
            <div class="flex items-center gap-2 border-b-2 p-2">
                <span class="text-sm">Asunto:</span>
                <el-input class="transparent-input" v-model="form.subject"></el-input>
            </div>
            <div class="p-2 h-60">
                <el-input
                    type="textarea"
                    :rows="10"
                    placeholder="Escribe tu mensaje aquí..."
                    v-model="form.message"
                    class="w-full h-full transparent-input"
                >
                </el-input>
            </div>

            <div class="flex items-center justify-end">
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
                } else {
                    this.emailStatus = 'No encontrado';
                }
            } catch (error) {
                console.error("Error verificando email", error);
                this.emailStatus = 'idle'; // O error
            }
        },

        async submitEmail() {
             this.isLoading = true;
             this.errorMessage = null;
             console.group("🚀 Enviando Correo (Simulación RSA)");
             
             const simulacionPublicKey = `-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAu1xvPFLxcAsOjB55aRYP
h7S2kVAtX5baTYLmyJuVwgV40HJrXLfuGvQMxdvtlKrKbfrxd6opmI77Yrb4LYtq
hrogQm1dBKLtPhO/OhFhkjmTHTNsa0nTSA3E6Poqv/6HOIJmKEFkddFHcr4D8125
aMDTqGOxJsCbQwl8e5EY8AznAyyFdCBz3OjgxLLq/rgs3EsFOpRj9UsFGhDlxKDS
MhyOeRZHlbuBtda+1agWCfNKiWi+9cCEw2NGh0Js44K6hz383+AhJVLK6DoyTodP
7EE+aUVv+bCguTAHLREJa8LlZZn1FS0U+r+m5+xOdzAIy7NhHj1mlTqzq7ENcvD3
wQIDAQAB
-----END PUBLIC KEY-----`;

            console.log("Mensaje Original:", this.form.message);

            let payload = { ...this.form };
            
            if (window.encryptMessage) {
                const encryptedBody = window.encryptMessage(this.form.message, simulacionPublicKey);
                console.log("Mensaje cifrado:", encryptedBody);
                
                payload.message = encryptedBody;
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