<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Map, MapPin, Newspaper } from 'lucide-vue-next';
import LeafletMap from '@/components/LeafletMap.vue';
import PostCard from '@/components/PostCard.vue';
import BlogLayout from '@/layouts/BlogLayout.vue';
import { show as postsShow } from '@/routes/posts';
import type { Post } from '@/types';

defineProps<{
    posts: Post[];
}>();

function handleMarkerClick(slug: string) {
    router.visit(postsShow(slug).url);
}
</script>

<template>
    <Head title="Home" />

    <BlogLayout>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Hero -->
            <section
                class="flex flex-col items-center gap-4 py-16 text-center sm:py-20"
            >
                <div
                    class="flex items-center gap-2 rounded-full border border-border bg-muted px-4 py-1.5 text-sm text-muted-foreground"
                >
                    <MapPin class="size-4" />
                    <span>Personal Travel Blog</span>
                </div>

                <h1
                    class="max-w-2xl text-4xl font-bold tracking-tight sm:text-5xl"
                >
                    Explore the world, one story at a time
                </h1>

                <p class="max-w-lg text-lg text-muted-foreground">
                    A collection of travel stories pinned on an interactive map.
                    Discover destinations, read experiences, and see the journey
                    unfold.
                </p>
            </section>

            <!-- Map Section -->
            <section v-if="posts.length > 0" class="pb-12">
                <div class="mb-4 flex items-center gap-2">
                    <Map class="size-5 text-muted-foreground" />
                    <h2 class="text-xl font-semibold">Destinations</h2>
                </div>
                <div class="overflow-hidden rounded-xl border border-border">
                    <LeafletMap
                        :posts="posts"
                        height="400px"
                        @marker-click="handleMarkerClick"
                    />
                </div>
            </section>

            <!-- Feed Section -->
            <section class="pb-20">
                <div class="mb-6 flex items-center gap-2">
                    <Newspaper class="size-5 text-muted-foreground" />
                    <h2 class="text-xl font-semibold">Latest Stories</h2>
                </div>

                <div
                    v-if="posts.length > 0"
                    class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <PostCard
                        v-for="post in posts"
                        :key="post.id"
                        :post="post"
                    />
                </div>

                <!-- Empty state -->
                <div
                    v-else
                    class="flex flex-col items-center gap-3 rounded-xl border border-dashed border-border py-16 text-center"
                >
                    <MapPin class="size-10 text-muted-foreground/40" />
                    <h3 class="text-lg font-medium">No stories yet</h3>
                    <p class="text-sm text-muted-foreground">
                        Travel stories will appear here once published.
                    </p>
                </div>
            </section>
        </div>
    </BlogLayout>
</template>
