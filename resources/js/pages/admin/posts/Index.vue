<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { MapPin, Plus, Eye, Pencil, Trash2 } from 'lucide-vue-next';
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
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { dashboard } from '@/routes/admin';
import {
    index as postsIndex,
    create as postsCreate,
    show as postsShow,
    edit as postsEdit,
    destroy as postsDestroy,
} from '@/routes/admin/posts';
import type { BreadcrumbItem, Post } from '@/types';

type PaginatedPosts = {
    data: Post[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

type Props = {
    posts: PaginatedPosts;
};

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Posts',
        href: postsIndex().url,
    },
];

function deletePost(slug: string) {
    router.delete(postsDestroy(slug).url);
}
</script>

<template>
    <Head title="Posts" />

    <AdminLayout :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6">
            <!-- Page header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Posts</h1>
                    <p class="text-muted-foreground">
                        Manage your travel blog posts.
                    </p>
                </div>
                <Button as-child>
                    <Link :href="postsCreate().url">
                        <Plus class="mr-2 size-4" />
                        New Post
                    </Link>
                </Button>
            </div>

            <!-- Posts table -->
            <Card>
                <CardHeader v-if="posts.total === 0">
                    <CardTitle class="text-center text-muted-foreground">
                        No posts yet
                    </CardTitle>
                </CardHeader>
                <CardContent
                    v-if="posts.total === 0"
                    class="flex flex-col items-center gap-4 py-8"
                >
                    <p class="text-sm text-muted-foreground">
                        Get started by creating your first travel post.
                    </p>
                    <Button as-child>
                        <Link :href="postsCreate().url">
                            <Plus class="mr-2 size-4" />
                            Create Post
                        </Link>
                    </Button>
                </CardContent>

                <template v-else>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Title</TableHead>
                                <TableHead>Category</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Location</TableHead>
                                <TableHead>Travel Date</TableHead>
                                <TableHead class="text-right">
                                    Actions
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="post in posts.data" :key="post.id">
                                <TableCell class="max-w-[250px] font-medium">
                                    <Link
                                        :href="postsShow(post.slug).url"
                                        class="line-clamp-1 hover:underline"
                                    >
                                        {{ post.title }}
                                    </Link>
                                </TableCell>
                                <TableCell>
                                    <PostCategoryBadge
                                        :category="post.category"
                                    />
                                </TableCell>
                                <TableCell>
                                    <PostStatusBadge :status="post.status" />
                                </TableCell>
                                <TableCell class="max-w-[180px]">
                                    <div
                                        class="flex items-center gap-1 text-muted-foreground"
                                    >
                                        <MapPin class="size-3.5 shrink-0" />
                                        <span class="line-clamp-1 text-sm">
                                            {{ post.location_name }}
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell class="text-muted-foreground">
                                    {{
                                        new Date(
                                            post.travel_date,
                                        ).toLocaleDateString()
                                    }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <div
                                        class="flex items-center justify-end gap-1"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            as-child
                                        >
                                            <Link
                                                :href="postsShow(post.slug).url"
                                            >
                                                <Eye class="size-4" />
                                                <span class="sr-only">
                                                    View
                                                </span>
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            as-child
                                        >
                                            <Link
                                                :href="postsEdit(post.slug).url"
                                            >
                                                <Pencil class="size-4" />
                                                <span class="sr-only">
                                                    Edit
                                                </span>
                                            </Link>
                                        </Button>
                                        <AlertDialog>
                                            <AlertDialogTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="text-destructive hover:text-destructive"
                                                >
                                                    <Trash2 class="size-4" />
                                                    <span class="sr-only">
                                                        Delete
                                                    </span>
                                                </Button>
                                            </AlertDialogTrigger>
                                            <AlertDialogContent>
                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>
                                                        Delete post?
                                                    </AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        This will permanently
                                                        delete "{{
                                                            post.title
                                                        }}". This action cannot
                                                        be undone.
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel>
                                                        Cancel
                                                    </AlertDialogCancel>
                                                    <AlertDialogAction
                                                        class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                                                        @click="
                                                            deletePost(
                                                                post.slug,
                                                            )
                                                        "
                                                    >
                                                        Delete
                                                    </AlertDialogAction>
                                                </AlertDialogFooter>
                                            </AlertDialogContent>
                                        </AlertDialog>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div
                        v-if="posts.last_page > 1"
                        class="flex items-center justify-between border-t px-4 py-4"
                    >
                        <p class="text-sm text-muted-foreground">
                            Page {{ posts.current_page }} of
                            {{ posts.last_page }} ({{ posts.total }} posts)
                        </p>
                        <div class="flex gap-1">
                            <template
                                v-for="link in posts.links"
                                :key="link.label"
                            >
                                <Button
                                    v-if="link.url"
                                    variant="outline"
                                    size="sm"
                                    :class="
                                        link.active
                                            ? 'bg-primary text-primary-foreground hover:bg-primary/90 hover:text-primary-foreground'
                                            : ''
                                    "
                                    as-child
                                >
                                    <Link :href="link.url" preserve-scroll>
                                        <span v-html="link.label" />
                                    </Link>
                                </Button>
                                <Button
                                    v-else
                                    variant="outline"
                                    size="sm"
                                    disabled
                                >
                                    <span v-html="link.label" />
                                </Button>
                            </template>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </AdminLayout>
</template>
