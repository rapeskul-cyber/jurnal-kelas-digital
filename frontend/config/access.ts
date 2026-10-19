export interface MenuItem {
  name: string
  to: string
  icon: string
}

export const ROLE_MENUS: Record<string, MenuItem[]> = {
  SISWA: [
    { name: 'Dashboard', to: '/dashboard', icon: 'Home' },
    { name: 'Jurnal Pelajaran', to: '/jurnal-pelajaran', icon: 'BookOpen' },
    { name: 'Riwayat Kehadiran', to: '/riwayat-kehadiran', icon: 'CalendarCheck' },
    { name: 'Surat Izin', to: '/surat-izin', icon: 'FileSpreadsheet' },
  ],
  SEKRETARIS: [
    { name: 'Dashboard', to: '/dashboard', icon: 'Home' },
    { name: 'Presensi Kehadiran', to: '/presensi', icon: 'ClipboardCheck' },
    { name: 'Jurnal Kelas', to: '/jurnal-kelas', icon: 'FileText' },
    { name: 'Surat Izin', to: '/surat-izin', icon: 'FileSpreadsheet' },
    { name: 'Jurnal Laporan Materi', to: '/laporan-jurnal', icon: 'FolderOpen' },
    { name: 'Jurnal Pelajaran', to: '/jurnal-pelajaran', icon: 'BookOpen' },
  ],
  GURU_PENGAJAR: [
    { name: 'Dashboard', to: '/dashboard', icon: 'Home' },
    { name: 'Jurnal Kelas', to: '/jurnal-kelas', icon: 'FileText' },
    { name: 'Jurnal Materi', to: '/jurnal-materi', icon: 'Edit3' },
    { name: 'Laporan Jurnal', to: '/laporan-jurnal', icon: 'FolderOpen' },
    { name: 'Rekap Otomatis', to: '/rekap-semester', icon: 'BarChart2' },
  ],
  GURU_PEMBIMBING: [
    { name: 'Dashboard', to: '/dashboard', icon: 'Home' },
    { name: 'Persetujuan Surat Izin', to: '/persetujuan-izin', icon: 'MailCheck' },
    { name: 'Laporan Surat Izin', to: '/laporan-izin', icon: 'FileSpreadsheet' },
    { name: 'Manajemen User', to: '/manajemen-user', icon: 'Users' },
    { name: 'Laporan Jurnal', to: '/laporan-jurnal', icon: 'FolderOpen' },
    { name: 'Rekap Otomatis', to: '/rekap-semester', icon: 'BarChart2' },
  ],
}

export const ROLE_LABELS: Record<string, string> = {
  SISWA: 'Siswa',
  SEKRETARIS: 'Sekretaris Kelas',
  GURU_PENGAJAR: 'Guru Pengajar',
  GURU_PEMBIMBING: 'Guru Pembimbing Sekolah',
}
