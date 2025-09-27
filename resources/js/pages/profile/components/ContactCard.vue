<script setup lang="ts">
const props = defineProps<{ user: { email:string; phone:string } }>()
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 p-4">
    <div class="flex flex-col gap-2">
      <button class="h-10 rounded-md bg-rose-600 text-white font-medium">Nhắn tin ngay</button>
      <div class="grid grid-cols-3 gap-2">
        <a :href="`tel:${user.phone}`" class="h-10 rounded-md ring-1 ring-gray-200 flex items-center justify-center text-sm hover:bg-gray-50">Gọi</a>
        <a :href="`mailto:${user.email}`" class="h-10 rounded-md ring-1 ring-gray-200 flex items-center justify-center text-sm hover:bg-gray-50">Email</a>
        <div class="h-10 rounded-md ring-1 ring-gray-200 flex items-center justify-center text-sm text-gray-500">Chia sẻ</div>
      </div>
    </div>

    <div class="mt-4 text-xs text-gray-600 space-y-1">
      <div>Thời gian phản hồi: <b>Trong 1 giờ</b></div>
      <div>Ngôn ngữ: Tiếng Việt</div>
      <div>Múi giờ: GMT+7</div>
    </div>
  </div>
</template>
