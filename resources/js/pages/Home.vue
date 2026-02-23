<script setup lang="ts">
import { InfiniteScroll, router } from '@inertiajs/vue3';
import { Map, MapPin, Newspaper, Search, Star } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import LeafletMap from '@/components/LeafletMap.vue';
import PostCard from '@/components/PostCard.vue';
import SeoHead from '@/components/SeoHead.vue';
import { Skeleton } from '@/components/ui/skeleton';
import BlogLayout from '@/layouts/BlogLayout.vue';
import { home } from '@/routes';
import { show as postsShow } from '@/routes/posts';
import { categoryLabels, type PaginatedPosts, type Post, type PostCategory, type Seo } from '@/types';

const props = defineProps<{
    featuredPosts: Post[];
    posts: PaginatedPosts;
    filters: {
        category: PostCategory | null;
        search: string | null;
    };
    seo: Seo;
}>();

const categories: Array<{ value: PostCategory | null; label: string }> = [
    { value: null, label: 'All' },
    { value: 'adventure', label: categoryLabels.adventure },
    { value: 'beach', label: categoryLabels.beach },
    { value: 'city', label: categoryLabels.city },
    { value: 'cultural', label: categoryLabels.cultural },
    { value: 'food', label: categoryLabels.food },
    { value: 'mountain', label: categoryLabels.mountain },
    { value: 'nature', label: categoryLabels.nature },
    { value: 'road_trip', label: categoryLabels.road_trip },
];

const searchInput = ref<string>(props.filters.search ?? '');
let searchTimer: ReturnType<typeof setTimeout> | null = null;

function selectCategory(category: PostCategory | null): void {
    router.visit(home().url, {
        data: {
            category: category ?? undefined,
            search: searchInput.value || undefined,
        },
        preserveState: true,
        preserveScroll: true,
        replace: true,
        reset: ['posts'],
    });
}

function handleMarkerClick(slug: string): void {
    router.visit(postsShow(slug).url);
}

watch(searchInput, (value) => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }
    searchTimer = setTimeout(() => {
        router.visit(home().url, {
            data: {
                category: props.filters.category ?? undefined,
                search: value || undefined,
            },
            preserveState: true,
            preserveScroll: true,
            replace: true,
            reset: ['posts'],
        });
    }, 300);
});
</script>

<template>
    <SeoHead :seo="seo" />

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
            <section v-if="posts.data.length > 0" class="pb-12">
                <div class="mb-4 flex items-center gap-2">
                    <Map class="size-5 text-muted-foreground" />
                    <h2 class="text-xl font-semibold">Destinations</h2>
                </div>
                <div class="overflow-hidden rounded-xl border border-border">
                    <LeafletMap
                        :posts="posts.data"
                        height="400px"
                        @marker-click="handleMarkerClick"
                    />
                </div>
            </section>

            <!-- Featured Posts -->
            <section v-if="featuredPosts.length > 0" class="pb-10">
                <div class="mb-4 flex items-center gap-2">
                    <Star class="size-5 text-yellow-500" />
                    <h2 class="text-xl font-semibold">Featured</h2>
                </div>
                <div class="flex gap-4 overflow-x-auto pb-2 snap-x">
                    <div
                        v-for="post in featuredPosts"
                        :key="post.id"
                        class="w-72 shrink-0 snap-start"
                    >
                        <PostCard :post="post" />
                    </div>
                </div>
            </section>

            <!-- Filter Bar -->
            <section class="pb-6">
                <div class="relative mb-4">
                    <Search
                        class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="searchInput"
                        type="search"
                        placeholder="Search destinations, stories..."
                        class="pl-9"
                    />
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="cat in categories"
                        :key="cat.value ?? 'all'"
                        type="button"
                        :class="[
                            'rounded-full border px-3 py-1 text-sm font-medium transition-colors',
                            filters.category === cat.value
                                ? 'border-primary bg-primary text-primary-foreground'
                                : 'border-border bg-background text-muted-foreground hover:border-foreground hover:text-foreground',
                        ]"
                        @click="selectCategory(cat.value)"
                    >
                        {{ cat.label }}
                    </button>
                </div>
            </section>

            <!-- Feed Section -->
            <section class="pb-20">
                <div class="mb-6 flex items-center gap-2">
                    <Newspaper class="size-5 text-muted-foreground" />
                    <h2 class="text-xl font-semibold">Latest Stories</h2>
                </div>

                <InfiniteScroll data="posts">
                    <template #loading>
                        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            <div
                                v-for="i in 3"
                                :key="i"
                                class="overflow-hidden rounded-xl border border-border"
                            >
                                <Skeleton class="aspect-video w-full" />
                                <div class="flex flex-col gap-2 p-4">
                                    <Skeleton class="h-4 w-20 rounded-full" />
                                    <Skeleton class="h-5 w-3/4" />
                                    <Skeleton class="h-4 w-full" />
                                    <Skeleton class="h-4 w-2/3" />
                                </div>
                            </div>
                        </div>
                    </template>

                    <div
                        v-if="posts.data.length > 0"
                        class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <PostCard
                            v-for="post in posts.data"
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
                        <h3 class="text-lg font-medium">No stories found</h3>
                        <p class="text-sm text-muted-foreground">
                            Try adjusting your search or category filter.
                        </p>
                    </div>
                </InfiniteScroll>
            </section>
        </div>
    </BlogLayout>
</template>
