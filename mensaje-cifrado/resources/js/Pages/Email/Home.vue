<template>
    <div class="flex flex-col min-h-screen bg-gray-100">
        <header class="h-16 w-full flex items-center justify-between p-5">

            <div class="w-full max-w-[500px] flex items-center gap-2 bg-gray-200 rounded-full p-2 ps-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class=" text-gray-600 icon icon-tabler icons-tabler-outline icon-tabler-search"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 10a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                <el-input class="transparent-input text-white" v-model="search" placeholder="Buscar correo"></el-input>
            </div>

            <el-dropdown>
                <div class="flex flex-col gap-2">
                    <img class="w-8 h-8 rounded-full" :src="'img/user-default.png'" alt="Usuario por defecto">
                </div>
                
                <template #dropdown>
                <el-dropdown-menu>
                    <el-dropdown-item>Action 1</el-dropdown-item>
                </el-dropdown-menu>
                </template>
            </el-dropdown>
        </header>
        <section class="flex-1 h-full flex flex-row">
            <aside class="shrink-0 pe-3">
                <div class="p-1 mb-5">
                    <button class="flex items-center gap-2 bg-blue-200 rounded-xl p-5 font-semibold" @click="openCreateEmail">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-pencil"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
                        Crear nuevo correo
                    </button>
                </div>
                <ul class="w-full [&>li]:py-1 cursor-pointer">
                    <li 
                        class="flex items-center gap-2 font-semibold px-4 py-2 rounded-r-xl transition-colors"
                        :class="currentFolder === 'inbox' ? 'bg-indigo-200 text-indigo-900' : 'hover:bg-gray-200'"
                        @click="switchFolder('inbox')"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-inbox"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 6a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2l0 -12" /><path d="M4 13h3l3 3h4l3 -3h3" /></svg>
                        Bandeja de entrada
                    </li>
                    <li 
                        class="flex items-center gap-2 font-semibold px-4 py-2 rounded-r-xl transition-colors"
                        :class="currentFolder === 'sent' ? 'bg-indigo-200 text-indigo-900' : 'hover:bg-gray-200'"
                        @click="switchFolder('sent')"
                    >
                       <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-send"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 14l11 -11" /><path d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5" /></svg>
                        Enviados
                    </li>
                </ul>
            </aside>
            <main class="flex-1 max-h-full w-full bg-white rounded-xl m-2 overflow-auto">
                <div class="p-4 flex justify-between items-center">
                    <h2 class="text-xl font-bold">{{ currentFolder === 'inbox' ? 'Recibidos' : 'Enviados' }}</h2>
                    <button 
                    :disabled="isLoading"
                    :class="isLoading ? 'text-gray-400 spinner' : 'text-gray-900'"
                    @click="fetchEmails"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 icon icon-tabler icons-tabler-outline icon-tabler-reload"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19.933 13.041a8 8 0 1 1 -9.925 -8.788c3.899 -1 7.935 1.007 9.425 4.747" /><path d="M20 4v5h-5" /></svg>
                    </button>
                </div>
                <template v-if="dumpEmails.length > 0">
                    <template v-for="(value, index) in dumpEmails" :key="index">
                        <article class="grid grid-cols-[150px_1fr_100px] p-4 text-gray-900 text-sm border-b border-gray-300 hover:bg-gray-200 hover:cursor-pointer" @click="openEmail(value)">
                            <h6 class="font-semibold">{{ currentFolder === 'sent' ? 'Para: ' : 'De: ' }} {{ value.other_party }}</h6>
                            <div class="flex flex-col">
                                <span class="font-bold mb-1">{{ value.subject }}</span>
                                <p class="text-gray-500 truncate">Haz clic para ver el contenido cifrado...</p>
                            </div>
                            <span class="font-semibold text-end">{{value.timestamp}}</span>
                        </article>
                    </template>
                </template>
                <div v-else-if="!isLoading" class="flex flex-col items-center justify-center h-full text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-mail-off mb-4"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h10a2 2 0 0 1 2 2v10m-2 2h-14a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2" /><path d="M3 7l9 6l9 -6" /><path d="M3 3l18 18" /></svg>
                    <p class="text-lg font-medium">No tienes mensajes {{ currentFolder === 'sent' ? 'enviados' : 'recibidos' }}</p>
                </div>
            </main>
        </section>
    </div>

    <Transition>
        <ContentEmail v-model="openModalEmail" :email="currentEmail" />
    </Transition>
    <Transition>
        <CreateEmail v-model="openModalCreateEmail" @message-sent="onMessageSent" />
    </Transition>
</template>

<script>
import ContentEmail from './ContentEmail.vue';
import CreateEmail from './CreateEmail.vue';
import { ElNotification } from 'element-plus';
    export default {
        components: {
            ContentEmail,
            CreateEmail,
        },
        props: {

        },

        data() {
            return {
                search: '',
                isLoading: false,
                currentFolder: 'inbox', // 'inbox' | 'sent'

                openModalEmail: false,
                currentEmail: {},

                openModalCreateEmail: false,

                dumpEmails: []
            }
        },
        mounted() {
            this.fetchEmails();

            const keys = this.$page.props.flash.keys;

            if (keys) {
                sessionStorage.setItem('public_key', keys.public_key);
                sessionStorage.setItem('private_key', keys.private_key);
                console.log("🔐 Llaves sincronizadas.");
            } else if (!sessionStorage.getItem('private_key')) {
                console.warn("⚠️ No tienes llaves para desencriptar.");
            }

            if (this.$page.props.auth.user) {
                const userId = this.$page.props.auth.user.id;
                
                // Listener de Laravel Echo
                window.Echo.private(`App.Models.User.${userId}`)
                    .listen('MessageSent', (e) => {
                        console.log("¡Evento recibido!", e.message);
                        const msg = e.message;
                        const isSentByMe = msg.sender_id === userId;
                        const isReceivedByMe = msg.recipient_id === userId;

                        // Si estoy en 'inbox' y recibo un correo -> agregarlo.
                        // Si estoy en 'sent' y envié un correo -> agregarlo.
                        
                        if (isReceivedByMe) {
                            ElNotification({
                                title: 'Nuevo Mensaje',
                                message: `De ${msg.sender ? msg.sender.email : 'Alguien'}: ${msg.subject}`,
                                type: 'info',
                                duration: 5000 
                            });

                            if (this.currentFolder === 'inbox') {
                                this.addMessageToList(msg);
                            }
                        } else if (this.currentFolder === 'sent' && isSentByMe) {
                             this.addMessageToList(msg);
                        }
                    });
            }
        },
        methods: {
            switchFolder(folder) {
                this.currentFolder = folder;
                this.fetchEmails();
            },
            async fetchEmails() {
                this.isLoading = true;
                this.dumpEmails = [];
                try {
                    const response = await axios.get('/messages', {
                        params: { folder: this.currentFolder }
                    });
                    this.dumpEmails = response.data;
                } catch (error) {
                    console.error("Error cargando correos:", error);
                } finally {
                    this.isLoading = false;
                }
            },
            addMessageToList(message) {
                 this.dumpEmails.unshift({
                    subject: message.subject,
                    content: message.body, 
                    timestamp: new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                    id: message.id,
                    sender_id: message.sender_id,
                    other_party: this.currentFolder === 'sent' ? (message.recipient ? message.recipient.email : '...') : (message.sender ? message.sender.email : '...'),
                    is_sent: message.sender_id === this.$page.props.auth.user.id
                });
            },
            openEmail(email) {
                this.openModalEmail = true;
                this.currentEmail = email;
            },
            openCreateEmail() {
                this.openModalCreateEmail = true;
            },
            onMessageSent() {
                if (this.currentFolder === 'sent') {
                    this.fetchEmails();
                }
            }    
        }
    }
</script>

<style>
    .spinner {
        animation: spin 1.5s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .v-enter-active,
    .v-leave-active {
    transition: opacity 0.5s ease;
    }

    .v-enter-from,
    .v-leave-to {
    opacity: 0;
    }
</style>

<style scoped>
/* Target al contenedor interno de Element Plus */
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

/* 2. Cambia el color del texto que se escribe */
.transparent-input :deep(.el-input__inner) {
  color: black !important;
}
</style>