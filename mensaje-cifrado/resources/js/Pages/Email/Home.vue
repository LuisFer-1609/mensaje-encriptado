<template>
    <div class="h-screen bg-gray-100">
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
        <section class="flex flex-row">
            <aside class="p-5">
                <ul>
                    <li>Bandeja de entrada</li>
                </ul>
            </aside>
            <main class="h-full w-full bg-white rounded-xl">
                <div class="p-4">
                    <button 
                    :disabled="isLoading"
                    :class="isLoading ? 'text-gray-400 spinner' : 'text-gray-900'"
                    @click="fetchEmails"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 icon icon-tabler icons-tabler-outline icon-tabler-reload"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19.933 13.041a8 8 0 1 1 -9.925 -8.788c3.899 -1 7.935 1.007 9.425 4.747" /><path d="M20 4v5h-5" /></svg>
                    </button>
                </div>
                <template v-for="(value, index) in dumpEmails" :key="index">
                    <article class="grid grid-cols-[150px_1fr_100px] p-4 text-gray-900 text-sm border-b border-gray-300 hover:bg-gray-200 hover:cursor-pointer" @click="openEmail(value)">
                        <h6 class="font-semibold">{{ value.subject }}</h6>
                        <p class="text-gray-500">Haz clic para ver el contenido del mensaje...</p>
                        <span class="font-semibold">{{value.timestamp}}</span>
                    </article>
                </template>
            </main>
        </section>
    </div>

    <Transition>
        <ContentEmail v-model="openModalEmail" :email="currentEmail" />
    </Transition>
</template>

<script>
import ContentEmail from './ContentEmail.vue';
    export default {
        components: {
            ContentEmail,
        },
        props: {

        },

        data() {
            return {
                search: '',
                isLoading: false,

                openModalEmail: false,
                currentEmail: {},
                dumpEmails: [
                    {
                        subject: 'Apple support',
                        content: 'Esto es el contenido dump del correo electrónico cifrado.',
                        timestamp: '10:30 AM'
                    },
                    {
                        subject: 'Google support',
                        content: 'Esto es el contenido dump del correo electrónico cifrado.',
                        timestamp: '10:30 AM'
                    },
                    {
                        subject: 'Microsoft support',
                        content: 'Esto es el contenido dump del correo electrónico cifrado.',
                        timestamp: '10:30 AM'
                    },
                ]
            }
        },
        methods: {
            async fetchEmails() {
                this.isLoading = true;

            },
            openEmail(email) {
                this.openModalEmail = true;
                this.currentEmail = email;
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