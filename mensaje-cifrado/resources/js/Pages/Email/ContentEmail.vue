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
    },
    data() {
        return {
            decryptedContent: null,
            isDecrypting: false
        }
    },
    emits: ["update:modelValue"],
    watch: {
        email: {
            handler(newVal) {
                if (newVal && newVal.content) {
                    this.decryptMessage(newVal.content);
                } else {
                    this.decryptedContent = null;
                }
            },
            immediate: true
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
            
            // Simulación: Llave Privada Hardcodeada (Solo para demo)
            const privateKey = `-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC7XG88UvFwCw6M
HnlpFg+HtLaRUC1fltpNgubIm5XCBXjQcmtct+4a9AzF2+2Uqspt+vF3qimYjvti
tvgti2qGuiBCbV0Eou0+E786EWGSOZMdM2xrSdNIDcTo+iq//oc4gmYoQWR10Udy
vgPzXblowNOoY7EmwJtDCXx7kRjwDOcDLIV0IHPc6ODEsur+uCzcSwU6lGP1SwUa
EOXEoNIyHI55FkeVu4G11r7VqBYJ80qJaL71wITDY0aHQmzjgrqHPfzf4CElUsro
OjJOh0/sQT5pRW/5sKC5MActEQlrwuVlmfUVLRT6v6bn7E53MAjLs2EePWaVOrOr
sQ1y8PfBAgMBAAECggEAI4KIXpGFyut9jVb1QdJ5jQnWFEJy5wwLQeeOX/UE9Cnf
VPpqmL8PdLJOfW2KYMPh6+hEvZ5VleF7iaEN9mvW+8Po+04I9CkA9+P8OUA0Euew
lzc41DjAv4ZEjLdlRNAzxqUIQZbrOF06wmd7DjP0QQNS2jZSy/pzMshofG7qoAwu
wKo1/nrF0h7/uhS3MgTLzaM9PsOzJt0O7tTRepjdV1WBaSNx0D25C14nQRb0MoaY
F6BPxdmWnxvqZKX3cOg270ahe2UmCCkf0VDaH5ivgc+5ee1S+Lwb9DrxuKQme4zj
5q4lWFTduPuQffJkRLnMTPe9tqwFeE9OClUf7P3i8QKBgQDqyVvlFh78oMjLTwd5
SwGLbpilxlfK4bGhO1UwrEmCezzdKv61UQQhPfrJ7/X/HWu/kOnWoZvNS0kzoTwu
GfgssRvhh4GHlux66tLZTGcHMH5kpkzGzKhZmdMI6aVB3+VkI7EDIEednulMLYZu
d+Y/oByHTevZpDY4DBRstpudhQKBgQDMSh5QQvMDBAxsvhB9lDiaP0gWk3ulfjDr
wXjHjaXGoaX9OE9dQ+wM/qq0tj8en06gDOmEI46/KbsHkmazDWW0x5ypD4+kuHuK
nfGc46XBBnfycHO0xzySjI75Xd3azXB86uR44dPX5noCu34g+pu7TezSogNFFljq
eRE2+2eYDQKBgA02V52EqSm+Qo5uXBVPSz71clGh377jBIP2Os0KZPiaxsoLaVn6
vSjBvCMxBb7dgPq+xOn3HjVnTy9Am4pcm8KoFNGUNo3SyGwCWusviXy2FebEFP36
9l5UcbPGsi4K03XXLFbosh8EmkxXI4GD7ckW0YQrtmIBA6wTwfJ8NDWhAoGBALgr
3dZH9SNz4+upEd7U4I6WnFvWDDOvZ1Uzb7qfxaMkGiwGNu6DDGzOroEKiC8IOe7q
lR9OmyNV8MorvzCO1HE9P8vePoam1qGqMKdbSBSTy1Ei9f01XTNVSpQffqICcfX8
IYulM8HBg5+w/k2nAPzEjMt7yezf77s6+D0UO1mFAoGBAJXJnnE6iyhLSsnYrJ50
aQRq0/KObvdZ6xp4yQJVOvzouTobE0ZPXClcNdTqcsaGMD+egR4z5u9N6csYakIK
cqMd+Y6FvlEh6/5uL+dMsWT+NV/VchNamn5R27ze8zmOmJA1gyKu5tYfC8Ezn2Jk
1Delbi7AFZEW2ZgDcHOqOx5i
-----END PRIVATE KEY-----`;

            if (window.decryptMessage) {
                 setTimeout(() => {
                     try {
                        const decrypted = window.decryptMessage(encryptedContent, privateKey);
                        this.decryptedContent = decrypted || "Error al desencriptar.";
                     } catch (e) {
                         this.decryptedContent = "Error: " + e.message;
                     }
                     this.isDecrypting = false;
                 }, 500);
            }
        } 
    }
}
</script>