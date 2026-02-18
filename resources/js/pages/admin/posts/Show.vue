<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    MapPin,
    Calendar,
    Clock,
    Pencil,
    Trash2,
    ArrowLeft,
} from 'lucide-vue-next';
import PostCategoryBadge from '@/components/admin/PostCategoryBadge.vue';
import PostStatusBadge from '@/components/admin/PostStatusBadge.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { dashboard } from '@/routes/admin';
import {
    index as postsIndex,
    show as postsShow,
    edit as postsEdit,
    destroy as postsDestroy,
} from '@/routes/admin/posts';
import type { BreadcrumbItem, Post } from '@/types';

type Props = {
    post: Post;
};

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Posts',
        href: postsIndex().url,
    },
    {
        title: props.post.title,
        href: postsShow(props.post.slug).url,
    },
];

function deletePost() {
    router.delete(postsDestroy(props.post.slug).url);
}
</script>

<template>
    <Head :title="post.title" />

    <AdminLayout :breadcrumbs="breadcrumbItems">
        <div class="mx-auto max-w-3xl">
            <!-- Back & Actions -->
            <div class="mb-6 flex items-center justify-between">
                <Button variant="ghost" size="sm" as-child>
                    <Link :href="postsIndex().url">
                        <ArrowLeft class="mr-2 size-4" />
                        Back to Posts
                    </Link>
                </Button>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="postsEdit(post.slug).url">
                            <Pencil class="mr-2 size-4" />
                            Edit
                        </Link>
                    </Button>
                    <AlertDialog>
                        <AlertDialogTrigger as-child>
                            <Button variant="destructive" size="sm">
                                <Trash2 class="mr-2 size-4" />
                                Delete
                            </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>
                                    Delete post?
                                </AlertDialogTitle>
                                <AlertDialogDescription>
                                    This will permanently delete "{{
                                        post.title
                                    }}". This action cannot be undone.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                <AlertDialogAction
                                    class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                                    @click="deletePost"
                                >
                                    Delete
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            </div>

            <Card>
                <!-- Cover Photo -->
                <div
                    v-if="post.cover_photo"
                    class="overflow-hidden rounded-t-lg"
                >
                    <img
                        :src="`/storage/${post.cover_photo.path}`"
                        :alt="post.title"
                        class="h-64 w-full object-cover sm:h-80"
                    />
                </div>

                <CardContent class="p-6">
                    <!-- Header -->
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <PostCategoryBadge :category="post.category" />
                        <PostStatusBadge :status="post.status" />
                    </div>

                    <h1 class="mb-4 text-3xl font-bold tracking-tight">
                        {{ post.title }}
                    </h1>

                    <!-- Meta Info -->
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
                        <div
                            v-if="post.published_at"
                            class="flex items-center gap-1.5"
                        >
                            <Clock class="size-4" />
                            <span>
                                Published
                                {{
                                    new Date(
                                        post.published_at,
                                    ).toLocaleDateString()
                                }}
                            </span>
                        </div>
                    </div>

                    <Separator class="mb-6" />

                    <!-- Content -->
                    <div
                        class="prose prose-neutral dark:prose-invert max-w-none whitespace-pre-wrap"
                    >
                        {{ post.content }}
                    </div>

                    <!-- Photo Gallery -->
                    <template v-if="post.photos && post.photos.length > 1">
                        <Separator class="my-6" />
                        <h3 class="mb-3 text-sm font-medium">All Photos</h3>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <div
                                v-for="photo in post.photos"
                                :key="photo.id"
                                class="overflow-hidden rounded-lg"
                            >
                                <img
                                    :src="`/storage/${photo.path}`"
                                    alt="Post photo"
                                    class="aspect-square w-full object-cover"
                                />
                            </div>
                        </div>
                    </template>

                    <Separator class="my-6" />

                    <!-- Location Details -->
                    <div class="rounded-lg bg-muted p-4">
                        <h3 class="mb-2 text-sm font-medium">
                            Location Details
                        </h3>
                        <div class="grid gap-2 text-sm sm:grid-cols-3">
                            <div>
                                <span class="text-muted-foreground">
                                    Location:
                                </span>
                                <span class="ml-1 font-medium">
                                    {{ post.location_name }}
                                </span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">
                                    Latitude:
                                </span>
                                <span class="ml-1 font-mono font-medium">
                                    {{ post.latitude }}
                                </span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">
                                    Longitude:
                                </span>
                                <span class="ml-1 font-mono font-medium">
                                    {{ post.longitude }}
                                </span>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
