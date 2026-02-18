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
    edit as postsEdit,
    update as postsUpdate,
} from '@/routes/admin/posts';
import type { BreadcrumbItem, Post, PostPhoto } from '@/types';

type EnumOption = {
    value: string;
    label: string;
};

type Props = {
    post: Post;
    categories: EnumOption[];
    statuses: EnumOption[];
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
        title: 'Edit',
        href: postsEdit(props.post.slug).url,
    },
];

const existingPhotos = ref<PostPhoto[]>([...props.post.photos]);
const coverPhotoId = ref<number | null>(
    props.post.photos.find((p) => p.is_cover)?.id ?? null,
);
const newPhotoPreviews = ref<string[]>([]);
const coverNewIndex = ref<number | null>(null);

const form = useForm({
    _method: 'put' as const,
    title: props.post.title,
    content: props.post.content,
    photos: [] as File[],
    existing_photos: props.post.photos.map((p) => p.id),
    cover_photo_id: coverPhotoId.value,
    cover_index: null as number | null,
    location_name: props.post.location_name,
    latitude: String(props.post.latitude),
    longitude: String(props.post.longitude),
    travel_date: props.post.travel_date
        ? props.post.travel_date.substring(0, 10)
        : '',
    category: props.post.category,
    status: props.post.status,
});

const totalPhotos = () => existingPhotos.value.length + form.photos.length;

function handlePhotosChange(event: Event) {
    const target = event.target as HTMLInputElement;
    const files = Array.from(target.files || []);

    files.forEach((file) => {
        if (totalPhotos() >= 10) return;

        form.photos.push(file);
        const reader = new FileReader();
        reader.onload = (e) => {
            newPhotoPreviews.value.push(e.target?.result as string);
        };
        reader.readAsDataURL(file);
    });

    target.value = '';
}

function removeExistingPhoto(index: number) {
    const photo = existingPhotos.value[index];
    existingPhotos.value.splice(index, 1);
    form.existing_photos = existingPhotos.value.map((p) => p.id);

    if (coverPhotoId.value === photo.id) {
        coverPhotoId.value = null;
        form.cover_photo_id = null;

        if (existingPhotos.value.length > 0) {
            setCoverExisting(0);
        } else if (form.photos.length > 0) {
            setCoverNew(0);
        }
    }
}

function removeNewPhoto(index: number) {
    form.photos.splice(index, 1);
    newPhotoPreviews.value.splice(index, 1);

    if (coverNewIndex.value === index) {
        coverNewIndex.value = null;
        form.cover_index = null;

        if (existingPhotos.value.length > 0) {
            setCoverExisting(0);
        } else if (form.photos.length > 0) {
            setCoverNew(0);
        }
    } else if (coverNewIndex.value !== null && coverNewIndex.value > index) {
        coverNewIndex.value--;
        form.cover_index = coverNewIndex.value;
    }
}

function setCoverExisting(index: number) {
    const photo = existingPhotos.value[index];
    coverPhotoId.value = photo.id;
    coverNewIndex.value = null;
    form.cover_photo_id = photo.id;
    form.cover_index = null;
}

function setCoverNew(index: number) {
    coverNewIndex.value = index;
    coverPhotoId.value = null;
    form.cover_index = index;
    form.cover_photo_id = null;
}

function submit() {
    form.post(postsUpdate(props.post.slug).url, {
        forceFormData: true,
    });
}
</script>

<template>
    <Head :title="`Edit: ${post.title}`" />

    <AdminLayout :breadcrumbs="breadcrumbItems">
        <div class="mx-auto max-w-3xl">
            <div class="mb-6">
                <h1 class="text-2xl font-bold tracking-tight">Edit Post</h1>
                <p class="text-muted-foreground">Update your travel story.</p>
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
                            Manage your post photos (max 10, 5MB each). Click
                            the star to set the cover photo.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div
                            v-if="
                                existingPhotos.length > 0 ||
                                newPhotoPreviews.length > 0
                            "
                            class="grid grid-cols-2 gap-3 sm:grid-cols-3"
                        >
                            <!-- Existing photos -->
                            <div
                                v-for="(photo, index) in existingPhotos"
                                :key="`existing-${photo.id}`"
                                class="group relative overflow-hidden rounded-lg border-2"
                                :class="
                                    coverPhotoId === photo.id
                                        ? 'border-primary'
                                        : 'border-transparent'
                                "
                            >
                                <img
                                    :src="`/storage/${photo.path}`"
                                    alt="Post photo"
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
                                            coverPhotoId === photo.id
                                                ? 'opacity-100'
                                                : ''
                                        "
                                        @click="setCoverExisting(index)"
                                    >
                                        <Star
                                            class="size-4"
                                            :class="
                                                coverPhotoId === photo.id
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
                                        @click="removeExistingPhoto(index)"
                                    >
                                        <X class="size-4" />
                                    </Button>
                                </div>
                                <div
                                    v-if="coverPhotoId === photo.id"
                                    class="absolute right-0 bottom-0 left-0 bg-primary/80 px-2 py-0.5 text-center text-xs font-medium text-primary-foreground"
                                >
                                    Cover
                                </div>
                            </div>

                            <!-- New photo previews -->
                            <div
                                v-for="(preview, index) in newPhotoPreviews"
                                :key="`new-${index}`"
                                class="group relative overflow-hidden rounded-lg border-2"
                                :class="
                                    coverNewIndex === index
                                        ? 'border-primary'
                                        : 'border-transparent'
                                "
                            >
                                <img
                                    :src="preview"
                                    alt="New photo preview"
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
                                            coverNewIndex === index
                                                ? 'opacity-100'
                                                : ''
                                        "
                                        @click="setCoverNew(index)"
                                    >
                                        <Star
                                            class="size-4"
                                            :class="
                                                coverNewIndex === index
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
                                        @click="removeNewPhoto(index)"
                                    >
                                        <X class="size-4" />
                                    </Button>
                                </div>
                                <div
                                    v-if="coverNewIndex === index"
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
                                :disabled="totalPhotos() >= 10"
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
                        Update Post
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
