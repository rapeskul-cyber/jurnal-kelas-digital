<template>
  <div
    class="relative w-full rounded-3xl overflow-hidden border transition-all duration-300 select-none shadow-2xl"
    :class="[
      isDark
        ? 'border-slate-800 bg-gradient-to-b from-slate-900 via-slate-950 to-blue-950/20 shadow-blue-950/20'
        : 'border-slate-200/90 bg-gradient-to-b from-slate-50/80 via-white to-blue-50/30 shadow-blue-950/5'
    ]"
  >
    <!-- Top Control Bar -->
    <div class="absolute top-4 left-4 right-4 z-20 flex flex-wrap items-center justify-between gap-3 pointer-events-none">
      <!-- Title & Floor Indicator -->
      <div
        class="pointer-events-auto backdrop-blur-md px-4 py-2.5 rounded-2xl border shadow-sm flex items-center gap-3 transition-colors"
        :class="isDark ? 'bg-slate-900/90 border-slate-700/80 text-white' : 'bg-white/90 border-slate-200/90 text-slate-900'"
      >
        <div class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-ping"></div>
        <div>
          <div class="flex items-center gap-2">
            <span class="text-xs font-extrabold tracking-tight" :class="isDark ? 'text-white' : 'text-slate-900'">Virtual Campus 3D</span>
            <span
              class="text-[10px] font-bold px-2 py-0.5 rounded-md border"
              :class="isDark ? 'bg-blue-500/20 text-blue-300 border-blue-500/40' : 'bg-blue-50 text-[#0f47ad] border-blue-200'"
            >
              SMK Digital Terpadu
            </span>
          </div>
          <p class="text-[11px] font-medium" :class="isDark ? 'text-slate-400' : 'text-slate-500'">
            {{ activeFloorLabel }}
          </p>
        </div>
      </div>

      <!-- Floor Selector Buttons & Exploded View Toggle -->
      <div
        class="pointer-events-auto flex flex-wrap items-center gap-1.5 backdrop-blur-md p-1.5 rounded-2xl border shadow-sm transition-colors"
        :class="isDark ? 'bg-slate-900/90 border-slate-700/80' : 'bg-white/90 border-slate-200/90'"
      >
        <button
          type="button"
          @click="selectFloor('all')"
          :class="[
            'px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer',
            selectedFloor === 'all' && !isExploded
              ? 'bg-[#0f47ad] text-white shadow-xs'
              : (isDark ? 'text-slate-300 hover:text-white hover:bg-slate-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100')
          ]"
        >
          Semua (3D)
        </button>

        <button
          v-for="fl in [1, 2, 3, 4]"
          :key="fl"
          type="button"
          @click="selectFloor(fl)"
          :class="[
            'px-2.5 sm:px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer',
            selectedFloor === fl && !isExploded
              ? 'bg-[#0f47ad] text-white shadow-xs'
              : (isDark ? 'text-slate-300 hover:text-white hover:bg-slate-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100')
          ]"
        >
          Lt. {{ fl }}
        </button>

        <button
          type="button"
          @click="toggleExplodedView"
          :class="[
            'px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer border',
            isExploded
              ? (isDark ? 'bg-blue-900/40 border-blue-500 text-blue-300 shadow-xs' : 'bg-blue-50 border-blue-300 text-[#0f47ad] shadow-xs')
              : (isDark ? 'border-transparent text-slate-300 hover:text-white hover:bg-slate-800' : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100')
          ]"
          title="Tampilkan irisan antar lantai terpisah secara vertikal"
        >
          <Layers class="w-3.5 h-3.5" />
          <span class="hidden sm:inline">Exploded View</span>
        </button>

        <button
          type="button"
          @click="resetCamera"
          class="p-2 rounded-xl transition-colors cursor-pointer"
          :class="isDark ? 'text-slate-400 hover:text-white hover:bg-slate-800' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100'"
          title="Reset Sudut Pandang Kamera"
        >
          <RotateCcw class="w-3.5 h-3.5" />
        </button>
      </div>
    </div>

    <!-- 3D WebGL Canvas Container -->
    <div
      ref="containerRef"
      class="w-full h-[520px] sm:h-[600px] lg:h-[660px] cursor-grab active:cursor-grabbing outline-none"
    ></div>

    <!-- Interactive Room Details Drawer / Card (Bottom Right Floating) -->
    <div
      v-if="selectedRoomInfo"
      class="absolute bottom-4 left-4 right-4 sm:left-auto sm:right-4 sm:w-96 z-20 backdrop-blur-md rounded-2xl p-4 sm:p-5 border shadow-xl shadow-blue-900/10 space-y-3 transition-all animate-fade-in-up"
      :class="isDark ? 'bg-slate-900/95 border-slate-700 text-white' : 'bg-white/95 border-slate-200/90 text-slate-900'"
    >
      <div class="flex items-start justify-between gap-3 border-b pb-3" :class="isDark ? 'border-slate-800' : 'border-slate-100'">
        <div>
          <div class="flex items-center gap-2">
            <span
              class="px-2 py-0.5 rounded-md text-[10px] font-bold border"
              :class="isDark ? 'bg-blue-500/20 text-blue-300 border-blue-500/40' : 'bg-blue-50 text-[#0f47ad] border-blue-200'"
            >
              Lantai {{ selectedRoomInfo.floor }}
            </span>
            <span class="text-[10px] text-slate-400 font-medium font-mono">{{ selectedRoomInfo.code }}</span>
          </div>
          <h4 class="text-sm sm:text-base font-bold mt-1" :class="isDark ? 'text-white' : 'text-slate-900'">{{ selectedRoomInfo.name }}</h4>
        </div>
        <button
          type="button"
          @click="selectedRoomInfo = null"
          class="p-1 rounded-lg transition-colors cursor-pointer"
          :class="isDark ? 'text-slate-400 hover:text-white hover:bg-slate-800' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100'"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <div class="space-y-2 text-xs" :class="isDark ? 'text-slate-300' : 'text-slate-600'">
        <p class="leading-relaxed">{{ selectedRoomInfo.desc }}</p>
        <div class="grid grid-cols-2 gap-2 pt-1 text-[11px]">
          <div class="p-2 rounded-xl border" :class="isDark ? 'bg-slate-800/70 border-slate-700/80' : 'bg-slate-50 border-slate-150'">
            <span class="text-slate-400 block text-[10px]">Kapasitas:</span>
            <span class="font-bold" :class="isDark ? 'text-white' : 'text-slate-800'">{{ selectedRoomInfo.capacity }}</span>
          </div>
          <div class="p-2 rounded-xl border" :class="isDark ? 'bg-slate-800/70 border-slate-700/80' : 'bg-slate-50 border-slate-150'">
            <span class="text-slate-400 block text-[10px]">Status KBM:</span>
            <span class="font-bold text-emerald-500 flex items-center gap-1">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
              {{ selectedRoomInfo.status }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Instruction Helper Pill (Bottom Left) -->
    <div
      class="absolute bottom-4 left-4 z-10 hidden sm:flex items-center gap-2.5 px-3 py-2 backdrop-blur-md rounded-xl border shadow-xs text-[11px] font-medium pointer-events-none transition-colors"
      :class="isDark ? 'bg-slate-900/85 border-slate-700/80 text-slate-300' : 'bg-white/85 border-slate-200/80 text-slate-500'"
    >
      <MousePointerClick class="w-3.5 h-3.5 text-[#0f47ad]" />
      <span>Klik lantai/ruangan untuk inspeksi • Drag mouse untuk putar 3D • Scroll untuk zoom</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  Layers,
  RotateCcw,
  MousePointerClick,
  X,
} from 'lucide-vue-next'
import * as THREE from 'three'
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js'

interface RoomData {
  id: string
  floor: number
  code: string
  name: string
  desc: string
  capacity: string
  status: string
  box: [number, number, number] // width, height, depth
  pos: [number, number, number] // x, y, z
  color: string
}

const props = withDefaults(
  defineProps<{
    isDark?: boolean
  }>(),
  {
    isDark: false,
  }
)

const containerRef = ref<HTMLDivElement | null>(null)
const selectedFloor = ref<number | 'all'>('all')
const isExploded = ref(false)
const selectedRoomInfo = ref<RoomData | null>(null)

// Room metadata for SMK Digital Terpadu (Lantai 1 s/d Lantai 4)
const roomsCatalog: RoomData[] = [
  // LANTAI 1 (Ground Floor: Administrasi, Lobi, & Lab Dasar)
  {
    id: 'f1_lobi',
    floor: 1,
    code: 'R.101',
    name: 'Lobi Utama & Pusat Informasi',
    desc: 'Area penyambutan kedatangan siswa, absensi RFID gate mandiri, dan layanan terpadu satu pintu.',
    capacity: '50 Orang',
    status: 'Aktif Terbuka',
    box: [5, 2.2, 4],
    pos: [0, 1.1, 4.5],
    color: '#0f47ad',
  },
  {
    id: 'f1_tu',
    floor: 1,
    code: 'R.102',
    name: 'Ruang Tata Usaha & Kepala Sekolah',
    desc: 'Pusat administrasi kepegawaian, legalisir ijazah, surat menyurat, dan arsip akademik sekolah.',
    capacity: '20 Staf',
    status: 'Jam Pelayanan',
    box: [4.5, 2.2, 4],
    pos: [-5, 1.1, 4.5],
    color: '#2563eb',
  },
  {
    id: 'f1_lab_dasar',
    floor: 1,
    code: 'R.103',
    name: 'Lab Komputer Dasar & CBT',
    desc: 'Laboratorium untuk asesmen nasional berbasis komputer dan pengenalan algoritma pemrograman dasar.',
    capacity: '36 Workstation',
    status: 'Tersedia KBM',
    box: [9.5, 2.2, 4],
    pos: [0, 1.1, -1],
    color: '#3b82f6',
  },
  {
    id: 'f1_kantin',
    floor: 1,
    code: 'R.104',
    name: 'Kantin Edukasi & Koridor Lapangan',
    desc: 'Area istirahat siswa berstandar higienis dan terhubung langsung ke lapangan upacara/olahraga.',
    capacity: '150 Siswa',
    status: 'Jam Istirahat',
    box: [4.5, 2.2, 4],
    pos: [5, 1.1, 4.5],
    color: '#60a5fa',
  },

  // LANTAI 2 (Ruang Guru, Server WA Bot, & Lab Animasi)
  {
    id: 'f2_guru',
    floor: 2,
    code: 'R.201',
    name: 'Ruang Guru & Musyawarah Wali Kelas',
    desc: 'Tempat kerja guru mata pelajaran umum & kejuruan, verifikasi surat izin siswa, dan konsultasi wali kelas.',
    capacity: '40 Dewan Guru',
    status: 'Jam Kerja',
    box: [7, 2.2, 4],
    pos: [-3.5, 3.7, 4.5],
    color: '#0f47ad',
  },
  {
    id: 'f2_server',
    floor: 2,
    code: 'R.202',
    name: 'Data Center & WhatsApp Gateway Server',
    desc: 'Ruang rack server berpendingin khusus yang menjalankan backend sistem presensi dan automasi notifikasi WA.',
    capacity: 'Server Rack IoT',
    status: 'Online 24/7',
    box: [2.5, 2.2, 4],
    pos: [1.5, 3.7, 4.5],
    color: '#1d4ed8',
  },
  {
    id: 'f2_lab_animasi',
    floor: 2,
    code: 'R.203',
    name: 'Studio Animasi 2D & Render Farm',
    desc: 'Fasilitas produksi motion capture, drawing tablet Wacom, dan mesin render untuk siswa keahlian Animasi.',
    capacity: '36 Workstation',
    status: 'KBM Praktikum',
    box: [9.5, 2.2, 4],
    pos: [0, 3.7, -1],
    color: '#3b82f6',
  },
  {
    id: 'f2_kelas_x',
    floor: 2,
    code: 'R.204',
    name: 'Ruang Teori X RPL 1 & X DKV',
    desc: 'Ruang kelas teori interaktif dilengkapi proyektor smart board dan speaker terintegrasi.',
    capacity: '36 Meja Siswa',
    status: 'Sesi Belajar',
    box: [4, 2.2, 4],
    pos: [5, 3.7, 4.5],
    color: '#60a5fa',
  },

  // LANTAI 3 (Studio DKV, Fotografi, & Lab iMac)
  {
    id: 'f3_studio_dkv',
    floor: 3,
    code: 'R.301',
    name: 'Studio Desain Komunikasi Visual (DKV)',
    desc: 'Laboratorium perancangan identitas visual, digital packaging, dan studio pameran karya poster siswa.',
    capacity: '36 Siswa',
    status: 'Pameran Desain',
    box: [6.5, 2.2, 4],
    pos: [-4, 6.3, 4.5],
    color: '#0f47ad',
  },
  {
    id: 'f3_studio_foto',
    floor: 3,
    code: 'R.302',
    name: 'Studio Fotografi & Green Screen Video',
    desc: 'Studio kedap suara dengan tata lampu profesional dan background green screen untuk produksi media.',
    capacity: '20 Siswa',
    status: 'Produksi Media',
    box: [3, 2.2, 4],
    pos: [1, 6.3, 4.5],
    color: '#2563eb',
  },
  {
    id: 'f3_imac_lab',
    floor: 3,
    code: 'R.303',
    name: 'Lab iMac & Creative Workstation',
    desc: 'Unit komputer Apple iMac untuk editing video 4K, color grading, dan pengolahan audio digital.',
    capacity: '32 iMac Retina',
    status: 'Praktik Editing',
    box: [9.5, 2.2, 4],
    pos: [0, 6.3, -1],
    color: '#3b82f6',
  },
  {
    id: 'f3_kelas_xi',
    floor: 3,
    code: 'R.304',
    name: 'Ruang Teori XI RPL & XI Animasi',
    desc: 'Ruang KBM tatap muka mata pelajaran konsentrasi kejuruan dan diskusi kelompok.',
    capacity: '36 Siswa',
    status: 'Diskusi Proyek',
    box: [4.5, 2.2, 4],
    pos: [5, 6.3, 4.5],
    color: '#60a5fa',
  },

  // LANTAI 4 (Lab RPL Industri R.409, Aula Digital, & Library)
  {
    id: 'f4_lab_industri',
    floor: 4,
    code: 'R.409',
    name: 'Lab RPL Industri (Web & Mobile Apps)',
    desc: 'Ruang inkubasi pengembangan web modern Nuxt 3 & REST API Laravel — Tempat sesi KBM Jimmy Fadhilah & kawan-kawan.',
    capacity: '36 High-End PC',
    status: 'KBM Aktif',
    box: [7, 2.2, 4],
    pos: [-3.5, 8.9, 4.5],
    color: '#0f47ad',
  },
  {
    id: 'f4_perpustakaan',
    floor: 4,
    code: 'R.402',
    name: 'Perpustakaan Digital & e-Reading Corner',
    desc: 'Ruang baca tenang dengan akses ribuan e-book, jurnal kejuruan, dan repositori modul materi guru.',
    capacity: '60 Siswa',
    status: 'Buka Harian',
    box: [6.5, 2.2, 4],
    pos: [3.5, 8.9, 4.5],
    color: '#2563eb',
  },
  {
    id: 'f4_aula',
    floor: 4,
    code: 'R.403',
    name: 'Aula Serbaguna & Conference Hall',
    desc: 'Auditorium akustik digital untuk seminar teknologi industri, wisuda, dan pertemuan akbar wali murid.',
    capacity: '250 Kursi',
    status: 'Siap Pakai',
    box: [14.5, 2.2, 4],
    pos: [0, 8.9, -1],
    color: '#1d4ed8',
  },
]

const activeFloorLabel = computed(() => {
  if (isExploded.value) return 'Mode Irisan Vertikal (Exploded 4 Lantai)'
  if (selectedFloor.value === 'all') return 'Seluruh Bangunan Kampus (Lantai 1 - 4)'
  return `Fokus Inspeksi Lantai ${selectedFloor.value}`
})

let scene: THREE.Scene
let camera: THREE.PerspectiveCamera
let renderer: THREE.WebGLRenderer
let controls: OrbitControls
let animationFrameId: number
const floorGroups: { [key: number]: THREE.Group } = {}
const roomMeshes: { mesh: THREE.Mesh; data: RoomData }[] = []
let raycaster: THREE.Raycaster
let mouse: THREE.Vector2

// Target floor Y offsets for animated elevation
const floorBaseHeights: { [key: number]: number } = {
  1: 0,
  2: 2.6,
  3: 5.2,
  4: 7.8,
}

// Current and target Y offsets for smooth tweening
const currentYOffsets: { [key: number]: number } = { 1: 0, 2: 2.6, 3: 5.2, 4: 7.8 }
const targetYOffsets: { [key: number]: number } = { 1: 0, 2: 2.6, 3: 5.2, 4: 7.8 }

function initThree() {
  if (!containerRef.value) return

  const width = containerRef.value.clientWidth
  const height = containerRef.value.clientHeight

  // Scene
  scene = new THREE.Scene()
  const bgCol = props.isDark ? 0x0b0f19 : 0xf8fafc
  scene.background = new THREE.Color(bgCol)
  scene.fog = new THREE.FogExp2(bgCol, 0.015)

  // Camera
  camera = new THREE.PerspectiveCamera(38, width / height, 0.1, 100)
  camera.position.set(22, 18, 26)

  // Renderer
  renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true })
  renderer.setSize(width, height)
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
  renderer.shadowMap.enabled = true
  renderer.shadowMap.type = THREE.PCFSoftShadowMap
  renderer.toneMapping = THREE.ACESFilmicToneMapping
  renderer.toneMappingExposure = 1.15
  containerRef.value.appendChild(renderer.domElement)

  // OrbitControls
  controls = new OrbitControls(camera, renderer.domElement)
  controls.enableDamping = true
  controls.dampingFactor = 0.05
  controls.maxPolarAngle = Math.PI / 2 - 0.05 // Don't look below ground
  controls.minDistance = 10
  controls.maxDistance = 50
  controls.target.set(0, 5, 1.5)

  // Lighting
  const ambientLight = new THREE.AmbientLight(0xffffff, 1.2)
  scene.add(ambientLight)

  const sunLight = new THREE.DirectionalLight(0xffffff, 1.6)
  sunLight.position.set(15, 25, 18)
  sunLight.castShadow = true
  sunLight.shadow.mapSize.width = 1024
  sunLight.shadow.mapSize.height = 1024
  sunLight.shadow.camera.near = 0.5
  sunLight.shadow.camera.far = 60
  sunLight.shadow.camera.left = -16
  sunLight.shadow.camera.right = 16
  sunLight.shadow.camera.top = 16
  sunLight.shadow.camera.bottom = -16
  sunLight.shadow.bias = -0.0005
  scene.add(sunLight)

  const blueFillLight = new THREE.DirectionalLight(0x3b82f6, 0.6)
  blueFillLight.position.set(-15, 12, -15)
  scene.add(blueFillLight)

  // Ground Grid & Base Plaza
  const gridHelper = new THREE.GridHelper(36, 36, 0x0f47ad, 0xe2e8f0)
  gridHelper.position.y = -0.02
  scene.add(gridHelper)

  const groundGeo = new THREE.CylinderGeometry(18, 19, 0.3, 32)
  const groundMat = new THREE.MeshStandardMaterial({ color: 0xffffff, roughness: 0.8 })
  const ground = new THREE.Mesh(groundGeo, groundMat)
  ground.position.y = -0.16
  ground.receiveShadow = true
  scene.add(ground)

  // Pathway / Road
  const pathGeo = new THREE.PlaneGeometry(6, 14)
  const pathMat = new THREE.MeshStandardMaterial({ color: 0xe2e8f0, roughness: 0.5 })
  const pathway = new THREE.Mesh(pathGeo, pathMat)
  pathway.rotation.x = -Math.PI / 2
  pathway.position.set(0, 0.01, 10.5)
  pathway.receiveShadow = true
  scene.add(pathway)

  // Build the 4-floor Building
  buildSchoolFloors()

  // Raycaster & Mouse
  raycaster = new THREE.Raycaster()
  mouse = new THREE.Vector2()

  renderer.domElement.addEventListener('pointerdown', onCanvasClick)
  window.addEventListener('resize', onWindowResize)

  // Start Animation Loop
  animate()
}

function buildSchoolFloors() {
  for (let fl = 1; fl <= 4; fl++) {
    const group = new THREE.Group()
    group.position.y = floorBaseHeights[fl]
    floorGroups[fl] = group
    scene.add(group)

    // Floor Plate (Concrete Slab)
    const slabGeo = new THREE.BoxGeometry(16, 0.28, 11)
    const slabMat = new THREE.MeshStandardMaterial({
      color: 0xf1f5f9,
      roughness: 0.4,
      metalness: 0.1,
    })
    const slab = new THREE.Mesh(slabGeo, slabMat)
    slab.position.set(0, 0.14, 1.5)
    slab.castShadow = true
    slab.receiveShadow = true
    group.add(slab)

    // Glass Railing / Exterior Framing
    const glassGeo = new THREE.BoxGeometry(15.8, 2.1, 10.8)
    const glassMat = new THREE.MeshStandardMaterial({
      color: 0x93c5fd,
      roughness: 0.1,
      metalness: 0.2,
      transparent: true,
      opacity: 0.18,
    })
    const glass = new THREE.Mesh(glassGeo, glassMat)
    glass.position.set(0, 1.25, 1.5)
    group.add(glass)

    // Floor Level Pillars
    const pillarPositions = [
      [-7.8, 1.25, -3.8],
      [7.8, 1.25, -3.8],
      [-7.8, 1.25, 6.8],
      [7.8, 1.25, 6.8],
      [0, 1.25, 6.8],
      [0, 1.25, -3.8],
    ]
    const pillarGeo = new THREE.CylinderGeometry(0.2, 0.2, 2.3, 12)
    const pillarMat = new THREE.MeshStandardMaterial({ color: 0x0f47ad, roughness: 0.3 })
    pillarPositions.forEach(pos => {
      const p = new THREE.Mesh(pillarGeo, pillarMat)
      p.position.set(pos[0], pos[1], pos[2])
      p.castShadow = true
      group.add(p)
    })
  }

  // Populate Rooms for Each Floor
  roomsCatalog.forEach(room => {
    const group = floorGroups[room.floor]
    if (!group) return

    const roomGeo = new THREE.BoxGeometry(room.box[0], room.box[1], room.box[2])
    const roomMat = new THREE.MeshStandardMaterial({
      color: new THREE.Color(room.color),
      roughness: 0.35,
      metalness: 0.15,
      transparent: true,
      opacity: 0.88,
    })
    const roomMesh = new THREE.Mesh(roomGeo, roomMat)
    // Relative pos inside the floor group
    roomMesh.position.set(room.pos[0], 1.15, room.pos[2])
    roomMesh.castShadow = true
    roomMesh.receiveShadow = true
    roomMesh.userData = { roomData: room }

    // White outline wireframe for CAD / architectural clarity
    const edgesGeo = new THREE.EdgesGeometry(roomGeo)
    const edgesMat = new THREE.LineBasicMaterial({ color: 0xffffff, transparent: true, opacity: 0.6 })
    const wireframe = new THREE.LineSegments(edgesGeo, edgesMat)
    roomMesh.add(wireframe)

    group.add(roomMesh)
    roomMeshes.push({ mesh: roomMesh, data: room })
  })

  // Rooftop Accessories (Lantai 4 Top)
  const roofGroup = floorGroups[4]
  if (roofGroup) {
    const roofSlabGeo = new THREE.BoxGeometry(16.4, 0.25, 11.4)
    const roofSlabMat = new THREE.MeshStandardMaterial({ color: 0x0f47ad, roughness: 0.3 })
    const roofSlab = new THREE.Mesh(roofSlabGeo, roofSlabMat)
    roofSlab.position.set(0, 2.45, 1.5)
    roofSlab.castShadow = true
    roofGroup.add(roofSlab)

    // Solar panels on roof
    const solarGeo = new THREE.BoxGeometry(3.5, 0.1, 2)
    const solarMat = new THREE.MeshStandardMaterial({ color: 0x1e3a8a, roughness: 0.1, metalness: 0.8 })
    for (let x = -5; x <= 5; x += 4.5) {
      const panel = new THREE.Mesh(solarGeo, solarMat)
      panel.position.set(x, 2.65, 1.5)
      panel.rotation.x = 0.15
      roofGroup.add(panel)
    }

    // Communication Tower / Antenna
    const towerGeo = new THREE.CylinderGeometry(0.06, 0.12, 3.2, 8)
    const towerMat = new THREE.MeshStandardMaterial({ color: 0x64748b, metalness: 0.8 })
    const tower = new THREE.Mesh(towerGeo, towerMat)
    tower.position.set(6.5, 4.1, -2.5)
    roofGroup.add(tower)
  }
}

function selectFloor(floor: number | 'all') {
  selectedFloor.value = floor
  isExploded.value = false

  if (floor === 'all') {
    targetYOffsets[1] = floorBaseHeights[1]
    targetYOffsets[2] = floorBaseHeights[2]
    targetYOffsets[3] = floorBaseHeights[3]
    targetYOffsets[4] = floorBaseHeights[4]

    // Reset floor opacity to full visible
    for (let fl = 1; fl <= 4; fl++) {
      setFloorVisibility(fl, 1.0)
    }
  } else {
    // Focus single floor
    for (let fl = 1; fl <= 4; fl++) {
      if (fl === floor) {
        targetYOffsets[fl] = floorBaseHeights[fl]
        setFloorVisibility(fl, 1.0)
      } else {
        // Dim other floors
        targetYOffsets[fl] = floorBaseHeights[fl]
        setFloorVisibility(fl, 0.15)
      }
    }
    // Select first room on this floor as default preview
    const firstRoom = roomsCatalog.find(r => r.floor === floor)
    if (firstRoom) selectedRoomInfo.value = firstRoom
  }
}

function toggleExplodedView() {
  isExploded.value = !isExploded.value
  selectedFloor.value = 'all'

  // Reset full opacity to all floors
  for (let fl = 1; fl <= 4; fl++) {
    setFloorVisibility(fl, 1.0)
  }

  if (isExploded.value) {
    // Separate floors vertically with spacious gap
    targetYOffsets[1] = 0
    targetYOffsets[2] = 4.2
    targetYOffsets[3] = 8.4
    targetYOffsets[4] = 12.6
  } else {
    // Close back together
    targetYOffsets[1] = floorBaseHeights[1]
    targetYOffsets[2] = floorBaseHeights[2]
    targetYOffsets[3] = floorBaseHeights[3]
    targetYOffsets[4] = floorBaseHeights[4]
  }
}

function setFloorVisibility(floor: number, opacity: number) {
  const group = floorGroups[floor]
  if (!group) return

  group.traverse(child => {
    if (child instanceof THREE.Mesh) {
      if (Array.isArray(child.material)) {
        child.material.forEach(m => {
          m.transparent = true
          m.opacity = Math.min(m.opacity, opacity)
        })
      } else if (child.material) {
        child.material.transparent = true
        child.material.opacity = opacity
      }
    }
  })
}

function resetCamera() {
  controls.reset()
  camera.position.set(22, 18, 26)
  controls.target.set(0, 5, 1.5)
  selectFloor('all')
  selectedRoomInfo.value = null
}

function onCanvasClick(event: MouseEvent) {
  if (!containerRef.value) return
  const rect = containerRef.value.getBoundingClientRect()
  mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1
  mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1

  raycaster.setFromCamera(mouse, camera)
  const intersects = raycaster.intersectObjects(
    roomMeshes.map(r => r.mesh),
    false
  )

  if (intersects.length > 0) {
    const hit = intersects[0].object
    const data = hit.userData?.roomData as RoomData | undefined
    if (data) {
      selectedRoomInfo.value = data
      // Highlight pulse
      const originalColor = new THREE.Color(data.color)
      const flashColor = new THREE.Color('#38bdf8')
      if (hit instanceof THREE.Mesh && hit.material instanceof THREE.MeshStandardMaterial) {
        hit.material.color.set(flashColor)
        setTimeout(() => {
          hit.material.color.set(originalColor)
        }, 300)
      }
    }
  }
}

function onWindowResize() {
  if (!containerRef.value || !renderer || !camera) return
  const width = containerRef.value.clientWidth
  const height = containerRef.value.clientHeight
  camera.aspect = width / height
  camera.updateProjectionMatrix()
  renderer.setSize(width, height)
}

function animate() {
  animationFrameId = requestAnimationFrame(animate)

  // Smooth lerp for floor elevation
  for (let fl = 1; fl <= 4; fl++) {
    currentYOffsets[fl] += (targetYOffsets[fl] - currentYOffsets[fl]) * 0.08
    if (floorGroups[fl]) {
      floorGroups[fl].position.y = currentYOffsets[fl]
    }
  }

  // Very gentle auto-rotation when idle on 'all'
  if (selectedFloor.value === 'all' && !isExploded.value && !selectedRoomInfo.value) {
    scene.rotation.y += 0.0012
  } else {
    // Return rotation to zero smoothly
    scene.rotation.y += (0 - scene.rotation.y) * 0.05
  }

  controls.update()
  renderer.render(scene, camera)
}

onMounted(() => {
  if (process.client) {
    initThree()
  }
})

watch(() => props.isDark, (val) => {
  if (scene) {
    const bgCol = val ? 0x0b0f19 : 0xf8fafc
    scene.background = new THREE.Color(bgCol)
    scene.fog = new THREE.FogExp2(bgCol, 0.015)
  }
})

onBeforeUnmount(() => {
  if (process.client) {
    cancelAnimationFrame(animationFrameId)
    window.removeEventListener('resize', onWindowResize)
    if (renderer) {
      renderer.domElement.removeEventListener('pointerdown', onCanvasClick)
      renderer.dispose()
    }
  }
})
</script>
