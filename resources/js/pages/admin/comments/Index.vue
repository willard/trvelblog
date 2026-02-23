<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Check, MessageSquare, Trash2, X } from 'lucide-vue-next';
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
import { Badge } from '@/components/ui/badge';
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
import { index as commentsIndex } from '@/routes/admin/comments';
import {
    approve as approveComment,
    reject as rejectComment,
    destroy as destroyComment,
} from '@/actions/App/Http/Controllers/Admin/CommentController';
import { show as postsShow } from '@/routes/posts';
import type { BreadcrumbItem } from '@/types';

type AdminComment = {
    id: number;
    post_id: number;
    parent_id: number | null;
    guest_name: string;
    guest_email: string;
    content: string;
    is_approved: boolean;
    created_at: string;
    post: { id: number; title: string; slug: string } | null;
};

type PaginatedComments = {
    data: AdminComment[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

type Props = {
    comments: PaginatedComments;
    filter: string;
};

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Comments',
        href: commentsIndex().url,
    },
];

const filters = [
    { label: 'All', value: 'all' },
    { label: 'Pending', value: 'pending' },
    { label: 'Approved', value: 'approved' },
];

function applyFilter(value: string): void {
    router.get(commentsIndex().url, { filter: value }, { preserveState: true });
}

function approve(id: number): void {
    router.patch(approveComment(id).url, {}, { preserveScroll: true });
}

function reject(id: number): void {
    router.patch(rejectComment(id).url, {}, { preserveScroll: true });
}

function deleteComment(id: number): void {
    router.delete(destroyComment(id).url, { preserveScroll: true });
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}
</script>

<template>
    <Head title="Comments" />

    <AdminLayout :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6">
            <!-- Page header -->
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Comments</h1>
                <p class="text-muted-foreground">
                    Moderate guest comments on your posts.
                </p>
            </div>

            <!-- Filter buttons -->
            <div class="flex gap-2">
                <Button
                    v-for="f in filters"
                    :key="f.value"
                    :variant="filter === f.value ? 'default' : 'outline'"
                    size="sm"
                    @click="applyFilter(f.value)"
                >
                    {{ f.label }}
                </Button>
            </div>

            <!-- Comments table -->
            <Card>
                <CardHeader v-if="comments.total === 0">
                    <CardTitle class="text-center text-muted-foreground">
                        No comments found
                    </CardTitle>
                </CardHeader>
                <CardContent
                    v-if="comments.total === 0"
                    class="flex flex-col items-center gap-4 py-8"
                >
                    <MessageSquare class="size-10 text-muted-foreground/50" />
                    <p class="text-sm text-muted-foreground">
                        No comments match the current filter.
                    </p>
                </CardContent>

                <template v-else>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Author</TableHead>
                                <TableHead>Comment</TableHead>
                                <TableHead>Post</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Date</TableHead>
                                <TableHead class="text-right">
                                    Actions
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="comment in comments.data" :key="comment.id">
                                <TableCell class="min-w-[140px]">
                                    <div class="text-sm font-medium">{{ comment.guest_name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ comment.guest_email }}</div>
                                </TableCell>
                                <TableCell class="max-w-[250px]">
                                    <p class="line-clamp-2 text-sm">{{ comment.content }}</p>
                                    <Badge v-if="comment.parent_id" variant="outline" class="mt-1 text-xs">
                                        Reply
                                    </Badge>
                                </TableCell>
                                <TableCell class="max-w-[180px]">
                                    <Link
                                        v-if="comment.post"
                                        :href="postsShow(comment.post.slug).url"
                                        class="line-clamp-1 text-sm hover:underline"
                                    >
                                        {{ comment.post.title }}
                                    </Link>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="comment.is_approved ? 'default' : 'secondary'">
                                        {{ comment.is_approved ? 'Approved' : 'Pending' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ formatDate(comment.created_at) }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button
                                            v-if="!comment.is_approved"
                                            variant="ghost"
                                            size="icon"
                                            title="Approve"
                                            @click="approve(comment.id)"
                                        >
                                            <Check class="size-4 text-green-600" />
                                            <span class="sr-only">Approve</span>
                                        </Button>
                                        <Button
                                            v-if="comment.is_approved"
                                            variant="ghost"
                                            size="icon"
                                            title="Reject"
                                            @click="reject(comment.id)"
                                        >
                                            <X class="size-4 text-orange-500" />
                                            <span class="sr-only">Reject</span>
                                        </Button>
                                        <AlertDialog>
                                            <AlertDialogTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="text-destructive hover:text-destructive"
                                                >
                                                    <Trash2 class="size-4" />
                                                    <span class="sr-only">Delete</span>
                                                </Button>
                                            </AlertDialogTrigger>
                                            <AlertDialogContent>
                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>
                                                        Delete comment?
                                                    </AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        This will permanently delete this comment
                                                        by "{{ comment.guest_name }}". This action
                                                        cannot be undone.
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                    <AlertDialogAction
                                                        class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                                                        @click="deleteComment(comment.id)"
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
                        v-if="comments.last_page > 1"
                        class="flex items-center justify-between border-t px-4 py-4"
                    >
                        <p class="text-sm text-muted-foreground">
                            Page {{ comments.current_page }} of
                            {{ comments.last_page }} ({{ comments.total }} comments)
                        </p>
                        <div class="flex gap-1">
                            <template
                                v-for="link in comments.links"
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
