<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Calendar, MapPin } from 'lucide-vue-next';
import PostCategoryBadge from '@/components/admin/PostCategoryBadge.vue';
import { Card } from '@/components/ui/card';
import { show as postsShow } from '@/routes/posts';
import type { Post } from '@/types';

defineProps<{
    post: Post;
}>();
</script>

<template>
    <Link :href="postsShow(post.slug).url" class="group block">
        <Card class="overflow-hidden p-0 transition-shadow hover:shadow-md">
            <div class="aspect-video w-full overflow-hidden bg-muted">
                <img
                    v-if="post.cover_photo"
                    :src="`/storage/${post.cover_photo.path}`"
                    :alt="post.title"
                    class="h-full w-full object-cover transition-transform group-hover:scale-105"
                />
                <div v-else class="flex h-full items-center justify-center">
                    <MapPin class="size-10 text-muted-foreground/40" />
                </div>
            </div>

            <div class="flex flex-col gap-2 p-4">
                <PostCategoryBadge :category="post.category" />

                <h3
                    class="line-clamp-2 text-lg leading-tight font-semibold group-hover:underline"
                >
                    {{ post.title }}
                </h3>

                <p class="line-clamp-2 text-sm text-muted-foreground">
                    {{ post.content.substring(0, 150) }}
                </p>

                <div
                    class="mt-auto flex items-center gap-3 pt-2 text-xs text-muted-foreground"
                >
                    <div class="flex items-center gap-1">
                        <MapPin class="size-3.5" />
                        <span class="line-clamp-1">
                            {{ post.location_name }}
                        </span>
                    </div>
                    <div class="flex items-center gap-1">
                        <Calendar class="size-3.5" />
                        <span>
                            {{
                                new Date(post.travel_date).toLocaleDateString(
                                    'en-US',
                                    {
                                        year: 'numeric',
                                        month: 'short',
                                        day: 'numeric',
                                    },
                                )
                            }}
                        </span>
                    </div>
                </div>
            </div>
        </Card>
    </Link>
</template>
