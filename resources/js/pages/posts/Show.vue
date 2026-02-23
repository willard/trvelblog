<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, Check, Clock, Copy, MapPin, MessageSquare, Reply, Share2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { store as storeComment } from '@/actions/App/Http/Controllers/CommentController';
import PostCategoryBadge from '@/components/admin/PostCategoryBadge.vue';
import LeafletMap from '@/components/LeafletMap.vue';
import PhotoGallery from '@/components/PhotoGallery.vue';
import PostCard from '@/components/PostCard.vue';
import SeoHead from '@/components/SeoHead.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import BlogLayout from '@/layouts/BlogLayout.vue';
import { home } from '@/routes';
import { categoryLabels, type Comment, type Post, type Seo } from '@/types';

const props = defineProps<{
    post: Post;
    comments: Comment[];
    relatedPosts: Post[];
    seo: Seo;
}>();

const page = usePage<{ flash: { success: string | null } }>();
const flash = computed(() => page.props.flash);

const readingTime = computed(() => Math.ceil(props.post.content.trim().split(/\s+/).length / 200));

const copied = ref(false);

function shareOnX(): void {
    const text = encodeURIComponent(props.post.title);
    const url = encodeURIComponent(props.seo.canonical);
    window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
}

function copyLink(): void {
    navigator.clipboard.writeText(props.seo.canonical).then(() => {
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    });
}

const jsonLd = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'Article',
    headline: props.post.title,
    description: props.seo.description,
    datePublished: props.post.published_at,
    dateModified: props.post.updated_at,
    ...(props.seo.og_image ? { image: props.seo.og_image } : {}),
    url: props.seo.canonical,
    author: { '@type': 'Person', name: 'Travel Blog' },
}));

const replyingTo = ref<Comment | null>(null);

const form = useForm({
    post_id: props.post.id,
    parent_id: null as number | null,
    guest_name: '',
    guest_email: '',
    content: '',
});

function submitComment(): void {
    form.post(storeComment().url, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('guest_name', 'guest_email', 'content', 'parent_id');
            replyingTo.value = null;
        },
    });
}

function setReply(comment: Comment): void {
    replyingTo.value = comment;
    form.parent_id = comment.id;
}

function cancelReply(): void {
    replyingTo.value = null;
    form.parent_id = null;
}

function getInitials(name: string): string {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
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
    <SeoHead :seo="seo" />
    <Head>
        <component :is="'script'" type="application/ld+json">{{ JSON.stringify(jsonLd) }}</component>
    </Head>

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
                        <div class="flex items-center gap-1.5">
                            <Clock class="size-4" />
                            <span>{{ readingTime }} min read</span>
                        </div>
                    </div>

                    <div v-if="post.tags && post.tags.length > 0" class="mb-6 flex flex-wrap gap-1.5">
                        <span
                            v-for="tag in post.tags"
                            :key="tag"
                            class="rounded-full bg-muted px-2.5 py-0.5 text-xs text-muted-foreground"
                        >
                            {{ tag }}
                        </span>
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

                    <Separator class="my-6" />

                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-muted-foreground">Share</span>
                        <Button variant="outline" size="sm" @click="shareOnX">
                            <Share2 class="mr-2 size-4" />
                            Twitter / X
                        </Button>
                        <Button variant="outline" size="sm" @click="copyLink">
                            <Check v-if="copied" class="mr-2 size-4 text-green-500" />
                            <Copy v-else class="mr-2 size-4" />
                            {{ copied ? 'Copied!' : 'Copy Link' }}
                        </Button>
                    </div>

                    <!-- Comments Section -->
                    <Separator class="my-6" />

                    <div>
                        <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold">
                            <MessageSquare class="size-5" />
                            Comments ({{ comments.length }})
                        </h3>

                        <!-- Flash success message -->
                        <div
                            v-if="flash.success"
                            class="mb-4 rounded-md bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400"
                        >
                            {{ flash.success }}
                        </div>

                        <!-- Comment Form -->
                        <Card class="mb-6 p-0">
                            <CardContent class="p-4">
                                <h4 class="mb-3 text-sm font-medium">
                                    {{ replyingTo ? `Replying to ${replyingTo.guest_name}` : 'Leave a comment' }}
                                </h4>

                                <div
                                    v-if="replyingTo"
                                    class="mb-3 flex items-center gap-2"
                                >
                                    <span class="text-xs text-muted-foreground">
                                        Replying to {{ replyingTo.guest_name }}
                                    </span>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-auto px-2 py-0.5 text-xs"
                                        @click="cancelReply"
                                    >
                                        Cancel
                                    </Button>
                                </div>

                                <form class="space-y-3" @submit.prevent="submitComment">
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <Label for="guest_name">Name</Label>
                                            <Input
                                                id="guest_name"
                                                v-model="form.guest_name"
                                                placeholder="Your name"
                                                class="mt-1"
                                            />
                                            <p v-if="form.errors.guest_name" class="mt-1 text-xs text-destructive">
                                                {{ form.errors.guest_name }}
                                            </p>
                                        </div>
                                        <div>
                                            <Label for="guest_email">Email</Label>
                                            <Input
                                                id="guest_email"
                                                v-model="form.guest_email"
                                                type="email"
                                                placeholder="your@email.com"
                                                class="mt-1"
                                            />
                                            <p v-if="form.errors.guest_email" class="mt-1 text-xs text-destructive">
                                                {{ form.errors.guest_email }}
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <Label for="content">Comment</Label>
                                        <Textarea
                                            id="content"
                                            v-model="form.content"
                                            placeholder="Write your comment..."
                                            rows="3"
                                            class="mt-1"
                                        />
                                        <p v-if="form.errors.content" class="mt-1 text-xs text-destructive">
                                            {{ form.errors.content }}
                                        </p>
                                    </div>
                                    <Button type="submit" size="sm" :disabled="form.processing">
                                        {{ form.processing ? 'Submitting...' : 'Submit Comment' }}
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>

                        <!-- Comments List -->
                        <div v-if="comments.length === 0" class="py-4 text-center text-sm text-muted-foreground">
                            No comments yet. Be the first to share your thoughts!
                        </div>

                        <div v-else class="space-y-4">
                            <div v-for="comment in comments" :key="comment.id">
                                <!-- Top-level comment -->
                                <div class="flex gap-3">
                                    <Avatar class="size-8 shrink-0">
                                        <AvatarFallback class="bg-muted text-xs">
                                            {{ getInitials(comment.guest_name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium">{{ comment.guest_name }}</span>
                                            <span class="text-xs text-muted-foreground">{{ formatDate(comment.created_at) }}</span>
                                        </div>
                                        <p class="mt-1 text-sm text-foreground/90">{{ comment.content }}</p>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="mt-1 h-auto px-2 py-0.5 text-xs text-muted-foreground"
                                            @click="setReply(comment)"
                                        >
                                            <Reply class="mr-1 size-3" />
                                            Reply
                                        </Button>
                                    </div>
                                </div>

                                <!-- Replies -->
                                <div
                                    v-if="comment.replies && comment.replies.length > 0"
                                    class="ml-11 mt-2 space-y-3 border-l-2 border-muted pl-4"
                                >
                                    <div v-for="reply in comment.replies" :key="reply.id" class="flex gap-3">
                                        <Avatar class="size-7 shrink-0">
                                            <AvatarFallback class="bg-muted text-xs">
                                                {{ getInitials(reply.guest_name) }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium">{{ reply.guest_name }}</span>
                                                <span class="text-xs text-muted-foreground">{{ formatDate(reply.created_at) }}</span>
                                            </div>
                                            <p class="mt-1 text-sm text-foreground/90">{{ reply.content }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <template v-if="relatedPosts.length > 0">
                        <Separator class="my-6" />
                        <h3 class="mb-4 text-lg font-semibold">
                            More {{ categoryLabels[post.category] }} Stories
                        </h3>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <PostCard
                                v-for="related in relatedPosts"
                                :key="related.id"
                                :post="related"
                            />
                        </div>
                    </template>
                </CardContent>
            </Card>
        </div>
    </BlogLayout>
</template>
