<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, MapPin } from 'lucide-vue-next';
import PostCategoryBadge from '@/components/admin/PostCategoryBadge.vue';
import LeafletMap from '@/components/LeafletMap.vue';
import PhotoGallery from '@/components/PhotoGallery.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import BlogLayout from '@/layouts/BlogLayout.vue';
import { home } from '@/routes';
import type { Post } from '@/types';

defineProps<{
    post: Post;
}>();
</script>

<template>
    <Head :title="post.title" />

    <BlogLayout>
        <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <Button variant="ghost" size="sm" as-child class="mb-6">
                <Link :href="home().url">
                    <ArrowLeft class="mr-2 size-4" />
                    Back to Home
                </Link>
            </Button>

            <Card class="overflow-hidden p-0">
                <div v-if="post.cover_photo" class="overflow-hidden">
                    <img
                        :src="`/storage/${post.cover_photo.path}`"
                        :alt="post.title"
                        class="h-64 w-full object-cover sm:h-80"
                    />
                </div>

                <CardContent class="p-6">
                    <div class="mb-4">
                        <PostCategoryBadge :category="post.category" />
                    </div>

                    <h1 class="mb-4 text-3xl font-bold tracking-tight">
                        {{ post.title }}
                    </h1>

                    <div
                        class="mb-6 flex flex-wrap items-center gap-4 text-sm text-muted-foreground"
                    >
                        <div class="flex items-center gap-1.5">
                            <MapPin class="size-4" />
                            <span>{{ post.location_name }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <Calendar class="size-4" />
                            <span>
                                {{
                                    new Date(
                                        post.travel_date,
                                    ).toLocaleDateString('en-US', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                    })
                                }}
                            </span>
                        </div>
                    </div>

                    <Separator class="mb-6" />

                    <div
                        class="prose prose-neutral dark:prose-invert max-w-none whitespace-pre-wrap"
                    >
                        {{ post.content }}
                    </div>

                    <!-- Photo Gallery -->
                    <template v-if="post.photos && post.photos.length > 1">
                        <Separator class="my-6" />
                        <h3 class="mb-3 text-sm font-medium">Photos</h3>
                        <PhotoGallery :photos="post.photos" />
                    </template>

                    <Separator class="my-6" />

                    <div class="overflow-hidden rounded-lg">
                        <LeafletMap :posts="[post]" height="250px" :zoom="5" />
                    </div>
                </CardContent>
            </Card>
        </div>
    </BlogLayout>
</template>
