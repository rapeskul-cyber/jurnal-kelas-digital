/**
 * Mengembalikan tanggal hari ini dalam format YYYY-MM-DD sesuai waktu lokal pengguna (bukan UTC)
 */
export function getLocalTodayYMD(d: Date = new Date()): string {
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

/**
 * Memformat string YYYY-MM-DD menjadi DD-MM-YYYY (contoh: 2026-10-31 -> 31-10-2026)
 */
export function formatToIndoDate(dateStr: string): string {
  if (!dateStr) return ''
  const parts = dateStr.split('-')
  if (parts.length === 3) {
    return `${parts[2]}-${parts[1]}-${parts[0]}`
  }
  return dateStr
}
