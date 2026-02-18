<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    FileText,
    CheckCircle,
    PenLine,
    Plus,
    ArrowRight,
    TrendingUp,
} from 'lucide-vue-next';
import PostCategoryBadge from '@/components/admin/PostCategoryBadge.vue';
import PostStatusBadge from '@/components/admin/PostStatusBadge.vue';
import StatCard from '@/components/admin/StatCard.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
} from '@/routes/admin/posts';
import type { BreadcrumbItem, Post, PostStats, PostCategory } from '@/types';

type Props = {
    stats: PostStats;
    postsByCategory: Record<string, number>;
    latestPosts: Pick<
        Post,
        | 'id'
        | 'title'
        | 'slug'
        | 'status'
        | 'category'
        | 'travel_date'
        | 'created_at'
    >[];
};

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout :breadcrumbs="breadcrumbItems">
        <div class="flex flex-col gap-6">
            <!-- Page header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Dashboard</h1>
                    <p class="text-muted-foreground">
                        Overview of your travel blog content.
                    </p>
                </div>
                <Button as-child>
                    <Link :href="postsCreate().url">
                        <Plus class="mr-2 size-4" />
                        New Post
                    </Link>
                </Button>
            </div>

            <!-- Stats cards -->
            <div class="grid gap-4 sm:grid-cols-3">
                <StatCard
                    title="Total Posts"
                    :value="stats.totalPosts"
                    :icon="FileText"
                    icon-class="text-blue-500"
                />
                <StatCard
                    title="Published"
                    :value="stats.publishedCount"
                    :icon="CheckCircle"
                    icon-class="text-green-500"
                />
                <StatCard
                    title="Drafts"
                    :value="stats.draftCount"
                    :icon="PenLine"
                    icon-class="text-amber-500"
                />
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Posts by Category -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <TrendingUp class="size-5" />
                            Posts by Category
                        </CardTitle>
                        <CardDescription>
                            Content distribution across categories
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="Object.keys(postsByCategory).length === 0"
                            class="py-8 text-center text-sm text-muted-foreground"
                        >
                            No posts yet. Create your first post!
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="(count, category) in postsByCategory"
                                :key="category"
                                class="flex items-center justify-between"
                            >
                                <div class="flex items-center gap-2">
                                    <PostCategoryBadge
                                        :category="category as PostCategory"
                                    />
                                </div>
                                <span class="text-sm font-medium tabular-nums">
                                    {{ count }}
                                </span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Latest Posts -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Latest Posts</CardTitle>
                                <CardDescription>
                                    Your most recent travel stories
                                </CardDescription>
                            </div>
                            <Button variant="ghost" size="sm" as-child>
                                <Link :href="postsIndex().url">
                                    View all
                                    <ArrowRight class="ml-1 size-4" />
                                </Link>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="latestPosts.length === 0"
                            class="py-8 text-center text-sm text-muted-foreground"
                        >
                            No posts yet. Start by creating one!
                        </div>
                        <Table v-else>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Title</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">
                                        Date
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="post in latestPosts"
                                    :key="post.id"
                                >
                                    <TableCell class="font-medium">
                                        <Link
                                            :href="postsShow(post.slug).url"
                                            class="hover:underline"
                                        >
                                            {{ post.title }}
                                        </Link>
                                    </TableCell>
                                    <TableCell>
                                        <PostStatusBadge
                                            :status="post.status"
                                        />
                                    </TableCell>
                                    <TableCell
                                        class="text-right text-muted-foreground"
                                    >
                                        {{
                                            new Date(
                                                post.travel_date,
                                            ).toLocaleDateString()
                                        }}
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
