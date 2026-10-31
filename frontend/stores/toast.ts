import { defineStore } from 'pinia'

export type ToastType = 'success' | 'error' | 'warning' | 'info'

export interface Toast {
  id: string
  type: ToastType
  message: string
  duration?: number
}

export const useToastStore = defineStore('toast', () => {
  const toasts = ref<Toast[]>([])

  function add(message: string, type: ToastType = 'info', duration = 3500) {
    const id = `toast-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`
    toasts.value.push({ id, type, message, duration })
    setTimeout(() => remove(id), duration)
    return id
  }

  function remove(id: string) {
    const idx = toasts.value.findIndex(t => t.id === id)
    if (idx !== -1) toasts.value.splice(idx, 1)
  }

  function success(message: string, duration?: number) {
    return add(message, 'success', duration)
  }

  function error(message: string, duration?: number) {
    return add(message, 'error', duration ?? 5000)
  }

  function warning(message: string, duration?: number) {
    return add(message, 'warning', duration)
  }

  function info(message: string, duration?: number) {
    return add(message, 'info', duration)
  }

  return { toasts, add, remove, success, error, warning, info }
})
