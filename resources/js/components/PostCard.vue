<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Calendar, Clock, MapPin } from 'lucide-vue-next';
import { computed } from 'vue';
import PostCategoryBadge from '@/components/admin/PostCategoryBadge.vue';
import { Card } from '@/components/ui/card';
import { show as postsShow } from '@/routes/posts';
import type { Post } from '@/types';

const props = defineProps<{
    post: Post;
}>();

const readingTime = computed(() => Math.ceil(props.post.content.trim().split(/\s+/).length / 200));
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

                <div v-if="post.tags && post.tags.length > 0" class="flex flex-wrap gap-1">
                    <span
                        v-for="tag in post.tags.slice(0, 3)"
                        :key="tag"
                        class="rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground"
                    >
                        {{ tag }}
                    </span>
                </div>

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
                    <div class="flex items-center gap-1">
                        <Clock class="size-3.5" />
                        <span>{{ readingTime }} min read</span>
                    </div>
                </div>
            </div>
        </Card>
    </Link>
</template>
