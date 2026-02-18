<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { ChevronLeft, ChevronRight, X } from 'lucide-vue-next';
import { DialogClose, DialogContent, DialogDescription, DialogPortal, DialogTitle, VisuallyHidden } from 'reka-ui';
import { Dialog, DialogOverlay } from '@/components/ui/dialog';
import type { PostPhoto } from '@/types';

const props = defineProps<{
    photos: PostPhoto[];
}>();

const isOpen = ref(false);
const activeIndex = ref(0);
const direction = ref<'next' | 'prev'>('next');

const totalPhotos = computed(() => props.photos.length);
const activePhoto = computed(() => props.photos[activeIndex.value]);
const transitionName = computed(() => `slide-${direction.value}`);

function openAt(index: number): void {
    activeIndex.value = index;
    isOpen.value = true;
}

function goTo(index: number): void {
    direction.value = index >= activeIndex.value ? 'next' : 'prev';
    activeIndex.value = index;
}

function prev(): void {
    direction.value = 'prev';
    activeIndex.value = activeIndex.value === 0 ? totalPhotos.value - 1 : activeIndex.value - 1;
}

function next(): void {
    direction.value = 'next';
    activeIndex.value = activeIndex.value === totalPhotos.value - 1 ? 0 : activeIndex.value + 1;
}

function handleKeydown(event: KeyboardEvent): void {
    if (!isOpen.value) {
        return;
    }

    if (event.key === 'ArrowLeft') {
        prev();
    } else if (event.key === 'ArrowRight') {
        next();
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <!-- Photo Grid -->
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
        <button
            v-for="(photo, index) in photos"
            :key="photo.id"
            type="button"
            class="group overflow-hidden rounded-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring cursor-pointer"
            @click="openAt(index)"
        >
            <img
                :src="`/storage/${photo.path}`"
                alt="Post photo"
                class="aspect-square w-full object-cover transition-transform duration-200 group-hover:scale-105"
            />
        </button>
    </div>

    <!-- Lightbox Modal -->
    <Dialog v-model:open="isOpen">
        <DialogPortal>
            <DialogOverlay class="fixed inset-0 z-50 bg-black/90 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0" />
            <DialogContent
                class="fixed left-1/2 top-1/2 z-50 flex w-full max-w-4xl -translate-x-1/2 -translate-y-1/2 flex-col overflow-hidden rounded-lg bg-black shadow-2xl duration-200 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 max-h-[90dvh] focus:outline-none"
            >
                <VisuallyHidden>
                    <DialogTitle>Photo gallery</DialogTitle>
                    <DialogDescription>Browse photos using the arrow buttons or keyboard arrow keys.</DialogDescription>
                </VisuallyHidden>

                <!-- Header: counter + close -->
                <div class="flex shrink-0 items-center justify-between px-4 py-3">
                    <span class="text-sm font-medium text-white/60">
                        {{ activeIndex + 1 }} / {{ totalPhotos }}
                    </span>
                    <DialogClose
                        class="rounded-md p-1.5 text-white/60 transition-colors hover:bg-white/10 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                    >
                        <X class="size-5" />
                        <span class="sr-only">Close</span>
                    </DialogClose>
                </div>

                <!-- Main image with prev/next -->
                <div class="relative flex shrink-0 items-center justify-center px-14">
                    <button
                        type="button"
                        class="absolute left-2 z-10 rounded-full bg-white/10 p-2 text-white transition-colors hover:bg-white/25 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white disabled:pointer-events-none disabled:opacity-30"
                        :disabled="totalPhotos <= 1"
                        aria-label="Previous photo"
                        @click="prev"
                    >
                        <ChevronLeft class="size-6" />
                    </button>

                    <div class="relative h-[60dvh] w-full overflow-hidden">
                        <Transition :name="transitionName">
                            <img
                                v-if="activePhoto"
                                :key="activePhoto.id"
                                :src="`/storage/${activePhoto.path}`"
                                alt="Post photo"
                                class="absolute inset-0 h-full w-full object-contain"
                            />
                        </Transition>
                    </div>

                    <button
                        type="button"
                        class="absolute right-2 z-10 rounded-full bg-white/10 p-2 text-white transition-colors hover:bg-white/25 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white disabled:pointer-events-none disabled:opacity-30"
                        :disabled="totalPhotos <= 1"
                        aria-label="Next photo"
                        @click="next"
                    >
                        <ChevronRight class="size-6" />
                    </button>
                </div>

                <!-- Thumbnail strip -->
                <div class="flex shrink-0 gap-2 overflow-x-auto px-4 py-3">
                    <button
                        v-for="(photo, index) in photos"
                        :key="photo.id"
                        type="button"
                        class="size-14 shrink-0 overflow-hidden rounded transition-all focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                        :class="index === activeIndex
                            ? 'opacity-100 ring-2 ring-white ring-offset-1 ring-offset-black'
                            : 'opacity-40 hover:opacity-70'"
                        :aria-label="`View photo ${index + 1}`"
                        @click="goTo(index)"
                    >
                        <img
                            :src="`/storage/${photo.path}`"
                            :alt="`Thumbnail ${index + 1}`"
                            class="size-full object-cover"
                        />
                    </button>
                </div>
            </DialogContent>
        </DialogPortal>
    </Dialog>
</template>

<style scoped>
.slide-next-enter-active,
.slide-next-leave-active,
.slide-prev-enter-active,
.slide-prev-leave-active {
    transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.35s ease;
    position: absolute;
    inset: 0;
}

.slide-next-enter-from {
    transform: translateX(100%);
    opacity: 0;
}
.slide-next-leave-to {
    transform: translateX(-100%);
    opacity: 0;
}

.slide-prev-enter-from {
    transform: translateX(-100%);
    opacity: 0;
}
.slide-prev-leave-to {
    transform: translateX(100%);
    opacity: 0;
}
</style>
