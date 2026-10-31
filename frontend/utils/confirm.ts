import { useConfirmStore } from '~/stores/confirm'
import type { ConfirmOptions } from '~/stores/confirm'

/**
 * Modern Promise-based confirmation popup to replace native browser confirm()
 * Example usage:
 * const ok = await confirmDialog({
 *   title: 'Hapus Foto Profil?',
 *   message: 'Foto profil akan dihapus dan kembali ke inisial standar.',
 *   confirmText: 'Ya, Hapus Foto',
 *   type: 'danger'
 * })
 * if (!ok) return
 */
export function confirmDialog(options: ConfirmOptions | string): Promise<boolean> {
  const store = useConfirmStore()
  return store.ask(options)
}
