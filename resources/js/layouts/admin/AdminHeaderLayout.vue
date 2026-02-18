<script setup lang="ts">
import AdminHeader from '@/components/AdminHeader.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <div class="min-h-svh bg-background">
        <AdminHeader :breadcrumbs="breadcrumbs" />
        <main
            class="mx-auto flex h-full w-full max-w-7xl flex-1 flex-col gap-4 px-4 py-6"
        >
            <slot />
        </main>
    </div>
</template>
