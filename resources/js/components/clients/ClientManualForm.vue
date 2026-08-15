<template>
  <form class="space-y-8" @submit.prevent="emit('submit')">

    <!-- TIPO -->
    <div class="field-reveal">
      <label class="label">Tipo de cliente *</label>
      <select v-model="form.type" :class="inputClass(form.errors.type) " class="mt-2">
        <option value="fisica">Persona física</option>
        <option value="juridica">Persona jurídica</option>
      </select>
    </div>

    <!-- DATOS PRINCIPALES -->
    <div class="field-reveal grid gap-6 md:grid-cols-2">

      <!-- 👤 PERSONA FISICA -->
      <template v-if="form.type === 'fisica'">
        <div>
          <label class="label">Nombre *</label>
          <input v-model="form.name" :class="inputClass(form.errors.name)"  @input="form.name = form.name?.toUpperCase()" />
        </div>
      </template>

      <!-- 🏢 PERSONA JURIDICA -->
      <template v-else>
        <div>
          <label class="label">Empresa *</label>
          <input v-model="form.company_name" :class="inputClass(form.errors.company_name)" @input="form.company_name = form.company_name?.toUpperCase()" />
        </div>

        <div>
          <label class="label">Persona de contacto</label>
          <input v-model="form.contact_person" :class="inputClass(form.errors.contact_person)" @input="form.contact_person = form.contact_person?.toUpperCase()" />
        </div>
      </template>

      <!-- COMUNES -->
      <div>
        <label class="label">NIF *</label>
        <input v-model="form.nif" :class="inputClass(form.errors.nif)"  @input="form.nif = form.nif?.toUpperCase()" />
         <p v-if="form.errors.nif" class="mt-1 text-sm text-red-500">
    {{ form.errors.nif }}
  </p>
      </div>

      <div>
        <label class="label">Email</label>
        <input v-model="form.email" type="email" :class="inputClass(form.errors.email)" />
      </div>

      <div>
        <label class="label">Teléfono</label>
        <input v-model="form.phone" :class="inputClass(form.errors.phone)" />
      </div>

      <div>
        <label class="label">Teléfono secundario</label>
        <input v-model="form.phone_secondary" :class="inputClass(form.errors.phone_secondary)" />
      </div>
    </div>

    <!-- DIRECCIÓN -->
    <div class="field-reveal">
      <h2 class="title">Dirección</h2>

      <div class="grid gap-6 md:grid-cols-2">
        <input v-model="form.address" placeholder="Dirección" :class="inputClass(form.errors.address)"  @input="form.address = form.address?.toUpperCase()" />
        <input v-model="form.city" placeholder="Ciudad" :class="inputClass(form.errors.city)" @input="form.city = form.city?.toUpperCase()" />
        <input v-model="form.postal_code" placeholder="Código postal" :class="inputClass(form.errors.postal_code)" />
        <input v-model="form.province" placeholder="Provincia" :class="inputClass(form.errors.province)" @input="form.province = form.province?.toUpperCase()" />
        <input v-model="form.country" value="España" readonly placeholder="País" :class="inputClass(form.errors.country)"  />
      </div>
    </div>

    <!-- NOTAS -->
    <div class="field-reveal">
      <label class="label">Notas</label>
      <textarea v-model="form.notes" rows="4" :class="textareaClass(form.errors.notes)" @input="form.notes = form.notes?.toUpperCase()" />
    </div>

    <!-- SUBMIT -->
    <div class="flex justify-end">
      <button
        type="submit"
        :disabled="form.processing"
        class="btn-primary"
      >
        {{ form.processing ? processingLabel : submitLabel }}
      </button>
    </div>

  </form>
</template>

<script setup lang="ts">
import { watch } from 'vue'

interface FormLike {
  [key: string]: any
  errors: Record<string, string | undefined>
  processing: boolean
}

const props = defineProps<{
  form: FormLike
  submitLabel: string
  processingLabel: string
}>()

const form = props.form
const emit = defineEmits<{
  submit: []
}>()

// ================================
// 🧠 CLASES INPUT
// ================================
const inputBaseClass =
  'h-12 w-full rounded-2xl border bg-transparent px-4 py-2.5 text-sm text-slate-800 shadow-theme-xs placeholder:text-slate-400 transition duration-300 focus:-translate-y-0.5 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500'

const textareaBaseClass =
  'w-full rounded-2xl border bg-transparent px-4 py-3 text-sm text-slate-800 shadow-theme-xs placeholder:text-slate-400 transition duration-300 focus:-translate-y-0.5 focus:outline-hidden focus:ring-4 dark:text-white/90 dark:placeholder:text-slate-500'

function inputClass(error?: string) {
  return [
    inputBaseClass,
    error
      ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10 dark:border-red-500/40'
      : 'border-slate-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-brand-800',
  ]
}

function textareaClass(error?: string) {
  return [
    textareaBaseClass,
    error
      ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10 dark:border-red-500/40'
      : 'border-slate-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-slate-700 dark:bg-slate-900 dark:focus:border-brand-800',
  ]
}


// ================================
// 🧠 AUTO-LIMPIEZA CAMPOS
// ================================
watch(
  () => form.type,
  (type) => {
    if (type === 'fisica') {
      form.company_name = null
      form.contact_person = null
    }

    if (type === 'juridica') {
      // si no hay contacto lo copiamos del nombre
      if (!form.contact_person && form.name) {
        form.contact_person = form.name
      }
    }
  }
)
</script>

<style scoped>
.field-reveal {
  animation: fieldReveal 520ms ease-out both;
}

.option-card:hover {
  transform: translateY(-2px);
  border-color: rgb(148 163 184 / 0.55);
  box-shadow: 0 18px 40px -28px rgb(15 23 42 / 0.45);
}

@keyframes fieldReveal {
  from {
    opacity: 0;
    transform: translateY(14px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
