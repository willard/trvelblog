<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { MapPin } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';
import { home } from '@/routes';
import type { Auth } from '@/types';

const { appearance, updateAppearance } = useAppearance();

const page = usePage<{ auth?: Auth }>();
const user = page.props.auth?.user;
</script>

<template>
    <div class="min-h-svh bg-background text-foreground">
        <header
            class="sticky top-0 z-50 border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60"
        >
            <div
                class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <Link
                    :href="home()"
                    class="flex items-center gap-2 font-semibold"
                >
                    <div class="flex items-center gap-1.5">
                        <MapPin class="size-5 text-primary" />
                        <span class="text-base">TrvelApp</span>
                    </div>
                </Link>

                <nav class="flex items-center gap-1">
                    <Link
                        href="/map"
                        class="rounded-md px-3 py-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                    >
                        Map
                    </Link>

                    <slot name="nav" />

                    <template v-if="user">
                        <Link
                            href="/dashboard"
                            class="rounded-md px-3 py-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            href="/login"
                            class="rounded-md px-3 py-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Log in
                        </Link>
                        <Link
                            href="/register"
                            class="rounded-md bg-primary px-3 py-1.5 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main>
            <slot />
        </main>

        <footer class="border-t border-border">
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-4 py-6 text-sm text-muted-foreground sm:px-6 lg:px-8"
            >
                <p>&copy; {{ new Date().getFullYear() }} TrvelApp</p>
                <div class="flex items-center gap-4">
                    <button
                        v-if="appearance === 'light' || appearance === 'system'"
                        class="transition-colors hover:text-foreground"
                        @click="updateAppearance('dark')"
                    >
                        Dark mode
                    </button>
                    <button
                        v-else
                        class="transition-colors hover:text-foreground"
                        @click="updateAppearance('light')"
                    >
                        Light mode
                    </button>
                </div>
            </div>
        </footer>
    </div>
</template>
