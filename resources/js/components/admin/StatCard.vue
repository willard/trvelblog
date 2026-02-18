<script setup lang="ts">
import type { LucideIcon } from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
    title: string;
    value: number | string;
    icon: LucideIcon;
    iconClass?: string;
}>();
</script>

<template>
    <Card>
        <CardHeader
            class="flex flex-row items-center justify-between space-y-0 pb-2"
        >
            <CardTitle class="text-sm font-medium text-muted-foreground">
                {{ title }}
            </CardTitle>
            <component
                :is="icon"
                class="size-5 text-muted-foreground"
                :class="iconClass"
            />
        </CardHeader>
        <CardContent>
            <div class="text-3xl font-bold">{{ value }}</div>
        </CardContent>
    </Card>
</template>
