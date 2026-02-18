<script setup lang="ts">
import BlogLayout from '@/layouts/blog/BlogPublicLayout.vue';
</script>

<template>
    <BlogLayout>
        <template #nav>
            <slot name="nav" />
        </template>
        <slot />
    </BlogLayout>
</template>
