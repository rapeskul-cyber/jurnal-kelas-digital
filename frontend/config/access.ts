export interface MenuItem {
  name: string
  to: string
  icon: string
}

export const ROLE_MENUS: Record<string, MenuItem[]> = {
  SISWA: [
    { name: 'Dashboard Mandiri', to: '/dashboard', icon: 'Home' },
    { name: 'Jurnal Kegiatan', to: '/jurnal-pelajaran', icon: 'BookOpen' },
    { name: 'Pengajuan Surat Izin', to: '/surat-izin', icon: 'Mail' },
    { name: 'Riwayat Kehadiran', to: '/riwayat-kehadiran', icon: 'CalendarCheck' },
  ],
  SEKRETARIS: [
    { name: 'Dashboard', to: '/dashboard', icon: 'Home' },
    { name: 'Presensi Kelas Harian', to: '/presensi', icon: 'ClipboardCheck' },
    { name: 'Agenda KBM Kelas', to: '/jurnal-kelas', icon: 'FileText' },
    { name: 'Catatan Belajar Pribadi', to: '/jurnal-pelajaran', icon: 'BookOpen' },
    { name: 'Surat Izin Saya', to: '/surat-izin', icon: 'Mail' },
    { name: 'Riwayat Kehadiran Saya', to: '/riwayat-kehadiran', icon: 'CalendarCheck' },
  ],
  GURU_PENGAJAR: [
    { name: 'Dashboard', to: '/dashboard', icon: 'Home' },
    { name: 'Jurnal Materi', to: '/jurnal-materi', icon: 'Edit3' },
    { name: 'Rekap Otomatis', to: '/rekap-semester', icon: 'BarChart2' },
    { name: 'Agenda KBM Kelas', to: '/jurnal-kelas', icon: 'FileText' },
    { name: 'Laporan Jurnal', to: '/laporan-jurnal', icon: 'FolderOpen' },
  ],
  GURU_PEMBIMBING: [
    { name: 'Dashboard Wali Kelas', to: '/dashboard', icon: 'Home' },
    { name: 'Persetujuan Surat Izin', to: '/persetujuan-izin', icon: 'MailCheck' },
    { name: 'Laporan Surat Izin', to: '/laporan-izin', icon: 'FileSpreadsheet' },
    { name: 'Manajemen Murid Kelas', to: '/manajemen-murid', icon: 'GraduationCap' },
    { name: 'Agenda KBM Kelas', to: '/jurnal-kelas', icon: 'FileText' },
    { name: 'Laporan Jurnal', to: '/laporan-jurnal', icon: 'FolderOpen' },
    { name: 'Rekap Otomatis', to: '/rekap-semester', icon: 'BarChart2' },
  ],
  ORANG_TUA: [
    { name: 'Pantauan Anak', to: '/dashboard', icon: 'Home' },
    { name: 'Jurnal Kegiatan Anak', to: '/jurnal-pelajaran', icon: 'BookOpen' },
    { name: 'Riwayat Kehadiran Anak', to: '/riwayat-kehadiran', icon: 'CalendarCheck' },
    { name: 'Riwayat Surat Izin', to: '/surat-izin', icon: 'Mail' },
  ],
  SUPERADMIN: [
    { name: 'Dashboard', to: '/dashboard', icon: 'Home' },
    { name: 'Semua Pengguna', to: '/manajemen-user', icon: 'Users' },
    { name: 'Master Data & Jadwal', to: '/master-data', icon: 'Layers' },
    { name: 'Manajemen Guru & Staf', to: '/manajemen-guru', icon: 'GraduationCap' },
    { name: 'Buka Kunci Presensi', to: '/presensi-override', icon: 'ShieldAlert' },
    { name: 'WhatsApp Gateway', to: '/wa-gateway', icon: 'MessageSquare' },
    { name: 'Pengaturan Sistem', to: '/pengaturan-sistem', icon: 'Settings' },
  ],
}

export const ROLE_LABELS: Record<string, string> = {
  SISWA: 'Siswa',
  SEKRETARIS: 'Sekretaris Kelas',
  GURU_PENGAJAR: 'Guru Pengajar',
  GURU_PEMBIMBING: 'Wali Kelas',
  ORANG_TUA: 'Orang Tua / Wali Murid',
  SUPERADMIN: 'Super Administrator',
}
