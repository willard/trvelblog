<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { categoryLabels, type PostCategory } from '@/types';

defineProps<{
    category: PostCategory;
}>();
</script>

<template>
    <Badge variant="outline">
        {{ categoryLabels[category] }}
    </Badge>
</template>
