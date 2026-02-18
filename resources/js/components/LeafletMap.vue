<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue';
import type { Post } from '@/types';

type MapPost = Pick<
    Post,
    | 'id'
    | 'title'
    | 'slug'
    | 'latitude'
    | 'longitude'
    | 'location_name'
    | 'category'
>;

const props = withDefaults(
    defineProps<{
        posts: MapPost[];
        height?: string;
        zoom?: number;
    }>(),
    {
        height: '400px',
        zoom: undefined,
    },
);

const emit = defineEmits<{
    markerClick: [slug: string];
}>();

const mapContainer = ref<HTMLElement | null>(null);

let mapInstance: any = null;

let markersLayer: any = null;

let leaflet: any = null;

async function initMap() {
    if (!mapContainer.value) return;

    leaflet = await import('leaflet');
    const { MarkerClusterGroup } = await import('leaflet.markercluster');

    mapInstance = leaflet.map(mapContainer.value).setView([20, 0], 2);

    leaflet
        .tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution:
                '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxNativeZoom: 19,
            maxZoom: 20,
        })
        .addTo(mapInstance);

    markersLayer = new MarkerClusterGroup();
    mapInstance.addLayer(markersLayer);

    renderMarkers();
}

function renderMarkers() {
    if (!leaflet || !mapInstance || !markersLayer) return;

    markersLayer.clearLayers();

    props.posts.forEach((post) => {
        const lat = Number(post.latitude);
        const lng = Number(post.longitude);

        if (isNaN(lat) || isNaN(lng)) return;

        const marker = leaflet
            .marker([lat, lng])
            .bindPopup(
                `<strong>${post.title}</strong><br>${post.location_name}`,
            );

        marker.on('click', () => emit('markerClick', post.slug));
        markersLayer.addLayer(marker);
    });

    const layers = markersLayer.getLayers();

    if (layers.length === 1 && props.zoom !== undefined) {
        const { lat, lng } = layers[0].getLatLng();
        mapInstance.setView([lat, lng], props.zoom);
    } else if (layers.length > 0) {
        mapInstance.fitBounds(markersLayer.getBounds().pad(0.1));
    }
}

watch(
    () => props.posts,
    () => renderMarkers(),
    { deep: true },
);

onMounted(() => {
    initMap();
});

onUnmounted(() => {
    mapInstance?.remove();
    mapInstance = null;
});
</script>

<template>
    <div ref="mapContainer" :style="{ height }" class="z-0 w-full" />
</template>
