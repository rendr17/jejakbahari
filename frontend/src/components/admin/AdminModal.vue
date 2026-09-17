<script setup lang="ts">
defineProps<{
  title: string
  open: boolean
}>()

const emit = defineEmits<{
  close: []
}>()
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
      role="dialog"
      aria-modal="true"
      :aria-label="title"
      @click.self="emit('close')"
    >
      <div
        class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-2xl"
      >
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-lg font-bold tracking-tight">{{ title }}</h2>
          <button
            type="button"
            class="rounded-md px-2 py-1 text-sm text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)]"
            data-testid="modal-close"
            @click="emit('close')"
          >
            ✕
          </button>
        </div>
        <slot />
      </div>
    </div>
  </Teleport>
</template>
