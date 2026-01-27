<template>
    <section v-if="modelValue" @click.self="closeModal" class="absolute inset-0 flex items-center justify-center bg-gray-400/50">
        <div class="w-96 h-auto bg-white shadow-xl rounded-xl p-5">
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
                <p class="font-semibold">
                    Contenido del correo
                    <span class="font-normal">
                        {{  email?.content || 'No se encontró el contenido del correo'  }}
                    </span>
                </p>
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
    },
    data() {
        return {
            dumpEmail: {
                subject: 'Apple support',
                content: 'Esto es el contenido dump del correo electrónico cifrado.'
            }
        }
    },
    emits: ["update:modelValue"],
    methods: {
        closeModal() {
            this.$emit('update:modelValue', false);
        }
    }
}
</script>