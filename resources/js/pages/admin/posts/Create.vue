<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Loader2, Star, X } from 'lucide-vue-next';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import LocationPicker from '@/components/LocationPicker.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { dashboard } from '@/routes/admin';
import {
    index as postsIndex,
    create as postsCreate,
    store as postsStore,
} from '@/routes/admin/posts';
import type { BreadcrumbItem } from '@/types';

type EnumOption = {
    value: string;
    label: string;
};

type Props = {
    categories: EnumOption[];
    statuses: EnumOption[];
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
    {
        title: 'Create',
        href: postsCreate().url,
    },
];

const form = useForm({
    title: '',
    content: '',
    photos: [] as File[],
    cover_index: 0,
    location_name: '',
    latitude: '',
    longitude: '',
    travel_date: '',
    category: '',
    tags: [] as string[],
    is_featured: false,
    status: 'draft',
});

const tagInput = ref('');

function onTagKeydown(event: KeyboardEvent): void {
    if (event.key === 'Enter' || event.key === ',') {
        event.preventDefault();
        const value = tagInput.value.trim().replace(/,$/, '');
        if (value && !form.tags.includes(value)) {
            form.tags.push(value);
        }
        tagInput.value = '';
    }
}

function removeTag(index: number): void {
    form.tags.splice(index, 1);
}

const photoPreviews = ref<string[]>([]);

function handlePhotosChange(event: Event) {
    const target = event.target as HTMLInputElement;
    const files = Array.from(target.files || []);

    files.forEach((file) => {
        form.photos.push(file);
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreviews.value.push(e.target?.result as string);
        };
        reader.readAsDataURL(file);
    });

    target.value = '';
}

function removePhoto(index: number) {
    form.photos.splice(index, 1);
    photoPreviews.value.splice(index, 1);

    if (form.cover_index === index) {
        form.cover_index = 0;
    } else if (form.cover_index > index) {
        form.cover_index--;
    }
}

function setCover(index: number) {
    form.cover_index = index;
}

function submit() {
    form.post(postsStore().url, {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Create Post" />

    <AdminLayout :breadcrumbs="breadcrumbItems">
        <div class="mx-auto max-w-3xl">
            <div class="mb-6">
                <h1 class="text-2xl font-bold tracking-tight">Create Post</h1>
                <p class="text-muted-foreground">
                    Share a new travel story with the world.
                </p>
            </div>

            <form @submit.prevent="submit" class="flex flex-col gap-6">
                <!-- Basic Info -->
                <Card>
                    <CardHeader>
                        <CardTitle>Basic Information</CardTitle>
                        <CardDescription>
                            Title and content of your post.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="title">Title</Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                placeholder="My Amazing Trip to..."
                                required
                            />
                            <InputError :message="form.errors.title" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="content">Content</Label>
                            <Textarea
                                id="content"
                                v-model="form.content"
                                placeholder="Write about your travel experience..."
                                class="min-h-[200px]"
                                required
                            />
                            <InputError :message="form.errors.content" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Photos -->
                <Card>
                    <CardHeader>
                        <CardTitle>Photos</CardTitle>
                        <CardDescription>
                            Upload photos for your post (max 10, 5MB each).
                            Click the star to set the cover photo.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div
                            v-if="photoPreviews.length > 0"
                            class="grid grid-cols-2 gap-3 sm:grid-cols-3"
                        >
                            <div
                                v-for="(preview, index) in photoPreviews"
                                :key="index"
                                class="group relative overflow-hidden rounded-lg border-2"
                                :class="
                                    form.cover_index === index
                                        ? 'border-primary'
                                        : 'border-transparent'
                                "
                            >
                                <img
                                    :src="preview"
                                    alt="Photo preview"
                                    class="aspect-square w-full object-cover"
                                />
                                <div
                                    class="absolute inset-0 flex items-start justify-between p-1.5 transition-colors group-hover:bg-black/30"
                                >
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="size-7 text-white opacity-0 group-hover:opacity-100"
                                        :class="
                                            form.cover_index === index
                                                ? 'opacity-100'
                                                : ''
                                        "
                                        @click="setCover(index)"
                                    >
                                        <Star
                                            class="size-4"
                                            :class="
                                                form.cover_index === index
                                                    ? 'fill-yellow-400 text-yellow-400'
                                                    : ''
                                            "
                                        />
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        size="icon"
                                        class="size-7 opacity-0 group-hover:opacity-100"
                                        @click="removePhoto(index)"
                                    >
                                        <X class="size-4" />
                                    </Button>
                                </div>
                                <div
                                    v-if="form.cover_index === index"
                                    class="absolute right-0 bottom-0 left-0 bg-primary/80 px-2 py-0.5 text-center text-xs font-medium text-primary-foreground"
                                >
                                    Cover
                                </div>
                            </div>
                        </div>
                        <div class="grid gap-2">
                            <Input
                                id="photos"
                                type="file"
                                accept="image/*"
                                multiple
                                :disabled="form.photos.length >= 10"
                                @change="handlePhotosChange"
                            />
                            <InputError :message="form.errors.photos" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Location -->
                <Card>
                    <CardHeader>
                        <CardTitle>Location</CardTitle>
                        <CardDescription>
                            Where did this adventure take place?
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="location_name">Location Name</Label>
                            <Input
                                id="location_name"
                                v-model="form.location_name"
                                placeholder="Tokyo, Japan"
                                required
                            />
                            <InputError :message="form.errors.location_name" />
                        </div>

                        <div class="grid gap-2">
                            <Label>Pin Location</Label>
                            <LocationPicker
                                v-model:latitude="form.latitude"
                                v-model:longitude="form.longitude"
                            />
                            <InputError :message="form.errors.latitude" />
                            <InputError :message="form.errors.longitude" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Details -->
                <Card>
                    <CardHeader>
                        <CardTitle>Details</CardTitle>
                        <CardDescription>
                            Categorize and set the status of your post.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="travel_date">Travel Date</Label>
                            <Input
                                id="travel_date"
                                v-model="form.travel_date"
                                type="date"
                                required
                            />
                            <InputError :message="form.errors.travel_date" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="category">Category</Label>
                                <Select v-model="form.category" required>
                                    <SelectTrigger id="category">
                                        <SelectValue
                                            placeholder="Select a category"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="cat in categories"
                                            :key="cat.value"
                                            :value="cat.value"
                                        >
                                            {{ cat.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.category" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="status">Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger id="status">
                                        <SelectValue
                                            placeholder="Select status"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="s in statuses"
                                            :key="s.value"
                                            :value="s.value"
                                        >
                                            {{ s.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.status" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="tag-input">Tags</Label>
                            <div v-if="form.tags.length > 0" class="flex flex-wrap gap-1.5">
                                <span
                                    v-for="(tag, index) in form.tags"
                                    :key="index"
                                    class="inline-flex items-center gap-1 rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium"
                                >
                                    {{ tag }}
                                    <button
                                        type="button"
                                        class="text-muted-foreground hover:text-foreground"
                                        @click="removeTag(index)"
                                    >
                                        <X class="size-3" />
                                    </button>
                                </span>
                            </div>
                            <Input
                                id="tag-input"
                                v-model="tagInput"
                                placeholder="Add a tag and press Enter or comma"
                                @keydown="onTagKeydown"
                            />
                            <InputError :message="form.errors.tags" />
                        </div>

                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="is_featured"
                                :checked="form.is_featured"
                                @update:checked="(val) => (form.is_featured = Boolean(val))"
                            />
                            <Label for="is_featured" class="cursor-pointer font-normal">
                                Feature this post on the homepage
                            </Label>
                        </div>
                    </CardContent>
                </Card>

                <!-- Submit -->
                <div class="flex items-center justify-end gap-4">
                    <Button variant="outline" type="button" as-child>
                        <a :href="postsIndex().url">Cancel</a>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2
                            v-if="form.processing"
                            class="mr-2 size-4 animate-spin"
                        />
                        Create Post
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
