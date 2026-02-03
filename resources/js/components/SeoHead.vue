<script setup lang="ts">
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';

type OpenGraph = Record<string, string | null | undefined>;
type Twitter = Record<string, string | null | undefined>;

interface SeoPayload {
    title?: string | null;
    description?: string | null;
    canonical?: string | null;
    robots?: string | null;
    keywords?: string[] | null;
    open_graph?: OpenGraph | null;
    twitter?: Twitter | null;
    json_ld?: Record<string, unknown> | Record<string, unknown>[] | null;
}

const page = usePage();
const seo = computed(() => (page.props as any).seo as SeoPayload | undefined);
const jsonStringify = (value: unknown) => JSON.stringify(value);
</script>

<template>
    <Head v-if="seo">
        <title>{{ seo.title }}</title>
        <meta v-if="seo.description" name="description" :content="seo.description" />
        <meta v-if="seo.robots" name="robots" :content="seo.robots" />
        <meta v-if="seo.keywords?.length" name="keywords" :content="seo.keywords.join(', ')" />
        <link v-if="seo.canonical" rel="canonical" :href="seo.canonical" />

        <meta v-if="seo.open_graph?.title" property="og:title" :content="seo.open_graph.title" />
        <meta v-if="seo.open_graph?.description" property="og:description" :content="seo.open_graph.description" />
        <meta v-if="seo.open_graph?.url" property="og:url" :content="seo.open_graph.url" />
        <meta v-if="seo.open_graph?.type" property="og:type" :content="seo.open_graph.type" />
        <meta v-if="seo.open_graph?.site_name" property="og:site_name" :content="seo.open_graph.site_name" />
        <meta v-if="seo.open_graph?.image" property="og:image" :content="seo.open_graph.image as string" />

        <meta v-if="seo.twitter?.card" name="twitter:card" :content="seo.twitter.card" />
        <meta v-if="seo.twitter?.title" name="twitter:title" :content="seo.twitter.title" />
        <meta v-if="seo.twitter?.description" name="twitter:description" :content="seo.twitter.description" />
        <meta v-if="seo.twitter?.image" name="twitter:image" :content="seo.twitter.image as string" />

        <template v-if="seo.json_ld">
            <component
                :is="'script'"
                type="application/ld+json"
                v-if="!Array.isArray(seo.json_ld)"
                v-html="jsonStringify(seo.json_ld)"
            />
            <component
                :is="'script'"
                type="application/ld+json"
                v-else
                v-for="(block, idx) in seo.json_ld"
                :key="idx"
                v-html="jsonStringify(block)"
            />
        </template>
    </Head>
</template>
