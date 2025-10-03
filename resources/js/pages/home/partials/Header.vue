<script setup lang="ts">
</script>

<template>
  <header class="bg-[#FAD0D0]"> <!-- nền hồng nhạt giống mock -->
    <div class="sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- LEFT: hamburger + logo -->
        <div class="flex items-center gap-3">
          <!-- Hamburger (tròn trắng, đổ bóng nhẹ) -->
          <button
            type="button"
            aria-label="Mở menu"
            class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white shadow-md hover:shadow transition"
          >
            <span class="sr-only">Menu</span>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </button>

          <!-- Logo + text -->
          <div class="flex items-center gap-2">
            <img
              src="/images/logo.png"
              alt="Sukientot"
              class="h-9 w-9 rounded-full object-contain ring-2 ring-white/40"
            />
            <span class="text-base sm:text-lg font-extrabold tracking-tight text-black uppercase">SUKIENTOT.COM</span>
          </div>
        </div>

        <div>
        <!-- Dành cho nhân sự (icon + text đậm) -->
          <div class="hidden lg:flex items-center gap-2 text-black font-semibold">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" class="opacity-90">
              <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z"
                    fill="currentColor"/>
            </svg>
            <span>Dành cho nhân sự</span>
          </div>
        </div>

        <!-- CENTER: “Dành cho nhân sự” + nav -->
        <div class="hidden md:flex items-center gap-8">
          

          <!-- Nav items (đậm, hover không đổi kích thước) -->
          <nav class="flex items-center gap-6">
            <a href="#" class="font-semibold text-black hover:text-black/80">Sự Kiện</a>
            <a href="#" class="font-semibold text-black hover:text-black/80">Vật Tư</a>
            <a href="#" class="font-semibold text-black hover:text-black/80">Tài Liệu</a>
            <a href="#" class="font-semibold text-black hover:text-black/80">Khách sạn</a>
          </nav>
        </div>

        <!-- RIGHT: actions -->
        <div class="flex items-center gap-3">
          <!-- Pill: Đặt show nhanh -->
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-full bg-[#ED3B50] px-4 sm:px-5 py-2 h-10 text-white font-semibold shadow-lg shadow-[#ED3B50]/30 hover:bg-[#d93a4a] active:translate-y-[0.5px] whitespace-nowrap flex-shrink-0 transition"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path d="M7 3v3M17 3v3M3.5 9h17M7 13h4m-4 4h10M5 6h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"
                    stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M15.5 10.5v3m-1.5-1.5h3" stroke="white" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span class="hidden sm:inline">Đặt show nhanh</span>
          </button>

          <!-- Account button group: tròn user + chevron nhỏ (giống ảnh) -->
          <div class="flex items-center gap-0 bg-[#ED3B50] rounded-full px-2.5 py-0.5 hover:bg-[#d93a4a]">
            <button
              type="button"
              aria-label="Tài khoản"
              class="h-9 w-9 rounded-full  text-white grid place-items-center shadow-lg shadow-[#ED3B50]/30 hover:bg-[#d93a4a] transition"
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z"
                      fill="currentColor"/>
              </svg>
            </button>
            <div>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            
          </div>
        </div>
      </div>
    </div>
  </header>
</template>
