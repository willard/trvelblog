<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import LeafletMap from '@/components/LeafletMap.vue';
import BlogLayout from '@/layouts/BlogLayout.vue';
import { show as postsShow } from '@/routes/posts';
import type { Post } from '@/types';

defineProps<{
    posts: Post[];
}>();

function handleMarkerClick(slug: string): void {
    router.visit(postsShow(slug).url);
}
</script>

<template>
    <Head title="Map" />

    <BlogLayout>
        <div class="h-[calc(100svh-3.5rem)]">
            <LeafletMap
                :posts="posts"
                height="100%"
                @marker-click="handleMarkerClick"
            />
        </div>
    </BlogLayout>
</template>
