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
    { name: 'Profil', to: '/profile', icon: 'User' },
  ],
  SEKRETARIS: [
    { name: 'Dashboard', to: '/dashboard', icon: 'Home' },
    { name: 'Presensi', to: '/presensi', icon: 'ClipboardCheck' },
    { name: 'Jurnal Kelas', to: '/jurnal-kelas', icon: 'FileText' },
    { name: 'Surat Izin', to: '/surat-izin', icon: 'FileSpreadsheet' },
    { name: 'Jurnal Laporan Materi', to: '/laporan-jurnal', icon: 'FolderOpen' },
    { name: 'Jurnal Pelajaran', to: '/jurnal-pelajaran', icon: 'BookOpen' },
  ],
  GURU_PENGAJAR: [
    { name: 'Dashboard', to: '/dashboard', icon: 'Home' },
    { name: 'Jurnal Materi', to: '/jurnal-materi', icon: 'Edit3' },
    { name: 'Rekap Otomatis', to: '/rekap-semester', icon: 'BarChart2' },
    { name: 'Jurnal Kelas', to: '/jurnal-kelas', icon: 'FileText' },
    { name: 'Laporan Jurnal', to: '/laporan-jurnal', icon: 'FolderOpen' },
  ],
  GURU_PEMBIMBING: [
    { name: 'Dashboard', to: '/dashboard', icon: 'Home' },
    { name: 'Persetujuan Surat Izin', to: '/persetujuan-izin', icon: 'MailCheck' },
    { name: 'Laporan Surat Izin', to: '/laporan-izin', icon: 'FileSpreadsheet' },
    { name: 'Manajemen Murid Kelas', to: '/manajemen-murid', icon: 'GraduationCap' },
    { name: 'Laporan Jurnal', to: '/laporan-jurnal', icon: 'FolderOpen' },
    { name: 'Rekap Otomatis', to: '/rekap-semester', icon: 'BarChart2' },
  ],
  SUPERADMIN: [
    { name: 'Dashboard', to: '/dashboard', icon: 'Home' },
    { name: 'Master Data & Jadwal', to: '/master-data', icon: 'Layers' },
    { name: 'Manajemen Pengguna', to: '/manajemen-user', icon: 'Users' },
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
  GURU_PEMBIMBING: 'Guru Pembimbing',
  SUPERADMIN: 'Super Administrator',
}
