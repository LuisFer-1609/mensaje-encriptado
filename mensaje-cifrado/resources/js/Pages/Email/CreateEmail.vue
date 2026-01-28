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
                <el-input class="transparent-input" v-model="form.to"></el-input>
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
                <el-button type="primary">Enviar</el-button>
            </div>
        </form>
    </section>
</template>

<script>
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

            form: {
                to: '',
                subject: '',
                message: ''
            }
        }
    },
    emits: ["update:modelValue"],
    mounted() {
        this.isMinimized = false;
    },
    methods: {
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