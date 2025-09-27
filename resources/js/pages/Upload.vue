<template>
    <div class="max-w-4xl mx-auto p-4">
        <h1 class="text-xl mb-4">Tạo Partner Category - test</h1>
        <!-- flash session error messages  -->
        <div v-if="flash.error" class="p-3 bg-red-100 text-red-800 rounded">
            {{ flash.error }}
        </div>
        <div v-if="flash.success" class="p-3 bg-green-100 text-green-800 rounded">
            {{ flash.success }}
        </div>
        <!-- form tạo category (giữ nguyên) -->
        <form @submit.prevent="submit" class="mb-8">
            <div class="mb-4">
                <label class="block text-sm mb-1">name</label>
                <input v-model="form.name" type="text" class="w-full border border-gray-400 rounded px-2 py-1" />
                <p v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm mb-1">image (single)</label>
                <input type="file" class="w-full border border-gray-400 rounded px-2 py-1" @change="onImageChange"
                    accept="image/*" />
                <p v-if="form.errors.image" class="text-red-600 text-sm mt-1">{{ form.errors.image }}</p>
                <div v-if="imagePreview" class="mt-2">
                    <img :src="imagePreview" alt="preview" class="w-32 h-20 object-cover rounded" />
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm mb-1">photos (multiple)</label>
                <input class="w-full border border-gray-400 rounded px-2 py-1" type="file" @change="onPhotosChange"
                    accept="image/*" multiple />
                <p v-if="form.errors['photos.0']" class="text-red-600 text-sm mt-1">{{ form.errors['photos.0'] }}</p>
                <div class="mt-2 flex gap-2 flex-wrap">
                    <img v-for="(p, i) in photosPreview" :key="i" :src="p" class="w-20 h-16 object-cover rounded" />
                </div>
            </div>

            <div>
                <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded">
                    tạo
                </button>
            </div>
        </form>

        <!-- table hiển thị categories + media -->
        <section>
            <h2 class="text-lg mb-3">Danh sách categories và media đã upload</h2>

            <div v-if="categories.length === 0" class="text-sm text-stone-600">
                chưa có category nào.
            </div>

            <table v-else class="w-full table-auto border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-2 py-1 text-left">#</th>
                        <th class="border px-2 py-1 text-left">category</th>
                        <th class="border px-2 py-1 text-left">media</th>
                        <th class="border px-2 py-1">actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(cat, idx) in categories" :key="cat.id">
                        <td class="border px-2 py-2 align-top">{{ idx + 1 }}</td>
                        <td class="border px-2 py-2 align-top">
                            <div class="font-medium">{{ cat.name }}</div>
                            <div class="text-xs text-gray-500">created: {{ cat.created_at }}</div>
                        </td>

                        <td class="border px-2 py-2">
                            <div v-if="cat.media.length === 0" class="text-sm text-gray-500">no media</div>
                            <div v-else class="flex gap-3 flex-wrap">
                                <div v-for="m in cat.media" :key="m.id" class="w-40 border rounded p-1">
                                    <a :href="m.url" target="_blank" class="block mb-1">
                                        <img :src="m.url" alt="image" class="w-full h-24 object-cover rounded" />

                                    </a>
                                    <div class="text-xs mt-1">
                                        <div class="truncate" :title="m.file_name">{{ m.file_name }}</div>
                                        <div class="text-gray-500 text-xs">{{ formatBytes(m.size) }}</div>
                                        <div class="text-gray-500 text-xs">uploaded: {{ m.created_at }}</div>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="border px-2 py-2 align-top text-center">
                            <div class="flex flex-col gap-2 items-center">
                                <!-- example: delete first media of category? but we provide per-media delete btn under each media -->
                                <div class="text-xs text-gray-500">xóa từng file bên dưới</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- separate list with delete buttons to avoid cramped table -->
        <section class="mt-6">
            <h3 class="text-md mb-2">xóa / quản lý media</h3>

            <div v-if="allMedia.length === 0" class="text-sm text-gray-600">không có media nào</div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div v-for="m in allMedia" :key="m.id" class="flex items-center gap-3 border rounded p-2">
                    <img :src="m.url" class="w-20 h-16 object-cover rounded" />
                    <div class="flex-1">
                        <div class="font-medium truncate" :title="m.file_name">{{ m.file_name }}</div>
                        <div class="text-xs text-gray-500">{{ formatBytes(m.size) }} • uploaded {{ m.created_at }}</div>
                    </div>
                    <div class="flex gap-2">
                        <a :href="m.url" target="_blank" class="text-sm underline">view</a>
                        <button @click="confirmDelete(m.id)"
                            class="px-2 py-1 text-sm bg-red-500 text-white rounded">delete</button>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';


// form upload (same as trước)
const form = useForm({
    name: '',
    image: null as File | null,
    photos: [] as File[],
});

const imagePreview = ref<string | null>(null);
const photosPreview = ref<string[]>([]);

function onImageChange(e: Event) {
    const files = (e.target as HTMLInputElement).files;
    if (files && files.length > 0) {
        form.image = files[0];
        imagePreview.value = URL.createObjectURL(files[0]);
    } else {
        form.image = null;
        imagePreview.value = null;
    }
}

function onPhotosChange(e: Event) {
    const files = (e.target as HTMLInputElement).files;
    if (!files) {
        form.photos = [];
        photosPreview.value = [];
        return;
    }
    const arr = Array.from(files);
    form.photos = arr;
    photosPreview.value = arr.map(f => URL.createObjectURL(f));
}

function submit() {
    form.post('/partner-categories', {
        onSuccess: () => {
            form.reset();
            imagePreview.value = null;
            photosPreview.value = [];
            // note: server trả lại trang create với updated props, in case of success Inertia will reload props
        },
    });
}

// lấy props từ server (controller). usePage().props contains server props
const page = usePage();
const categories = computed(() => (page.props as any).categories ?? []);

const flash = page.props.flash as {
    success?: string
    error?: string
}

// flat list of all media để hiển thị delete list
const allMedia = computed(() => {
    const cats = categories.value as Array<any>;
    return cats.flatMap(c => (c.media ?? []).map((m: any) => ({ ...m, category_name: c.name })));
});

// format bytes helper
function formatBytes(bytes: number) {
    if (!bytes) return '0 B';
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return `${(bytes / Math.pow(1024, i)).toFixed(2)} ${sizes[i]}`;
}

// delete media bằng fetch + csrf token
async function confirmDelete(mediaId: number) {
    if (!confirm('Bạn có chắc muốn xóa file này?')) return;

    const tokenMeta = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;
    const token = tokenMeta?.getAttribute('content') ?? '';

    try {
        const res = await fetch(`/media/${mediaId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!res.ok) {
            const data = await res.json().catch(() => ({}));
            alert('xóa thất bại: ' + (data.message || res.statusText));
            return;
        }

        // on success: reload page props để cập nhật danh sách media
        // simple approach: reload current page via location (Inertia would preserve SPA)
        window.location.reload();
    } catch (err) {
        console.error(err);
        alert('xóa thất bại, kiểm tra console');
    }
}
</script>

<style scoped>
/* optional: keep styles minimal */
</style>
