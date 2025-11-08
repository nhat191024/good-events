<template>
    <section class="bg-white">
        <div class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Đội ngũ</p>
                <h2 class="mt-3 text-3xl font-bold text-slate-900">Những thành viên tại Sukientot</h2>
                <p class="mx-auto mt-4 max-w-2xl text-base text-slate-600">
                    Kết hợp chuyên môn về sự kiện, công nghệ và vận hành để mang tới trải nghiệm tốt cho khách hàng.
                </p>
            </div>

            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <article
                    v-for="member in members"
                    :key="member.name"
                    class="rounded-2xl border border-slate-100 bg-slate-50/70 p-6 text-center shadow-sm shadow-black/[0.02]">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-indigo-100 text-xl font-semibold text-indigo-700">
                        {{ getInitials(member.name) }}
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ member.name }}</h3>
                    <p class="text-sm font-medium text-indigo-600">{{ member.role }}</p>
                    <p class="mt-3 text-sm text-slate-600">
                        {{ member.description }}
                    </p>
                </article>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
interface TeamMember {
    name: string;
    role: string;
    description: string;
}

defineProps<{
    members: TeamMember[];
}>();

function getInitials(name: string): string {
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word[0]?.toUpperCase() ?? '')
        .join('');
}
</script>
