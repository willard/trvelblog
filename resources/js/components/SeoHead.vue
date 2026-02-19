<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { Seo } from '@/types';

defineProps<{
    seo: Seo;
}>();
</script>

<template>
    <Head>
        <title>{{ seo.title }}</title>
        <meta name="description" :content="seo.description" />
        <link rel="canonical" :href="seo.canonical" />
        <meta property="og:title" :content="seo.title" />
        <meta property="og:description" :content="seo.description" />
        <meta property="og:url" :content="seo.canonical" />
        <meta property="og:type" content="website" />
        <meta v-if="seo.og_image" property="og:image" :content="seo.og_image" />
        <meta name="twitter:card" :content="seo.og_image ? 'summary_large_image' : 'summary'" />
        <meta name="twitter:title" :content="seo.title" />
        <meta name="twitter:description" :content="seo.description" />
        <meta v-if="seo.og_image" name="twitter:image" :content="seo.og_image" />
    </Head>
</template>
