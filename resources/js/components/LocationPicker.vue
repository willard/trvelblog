<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';

const latitude = defineModel<string>('latitude', { default: '' });
const longitude = defineModel<string>('longitude', { default: '' });

const props = withDefaults(
    defineProps<{
        height?: string;
    }>(),
    {
        height: '350px',
    },
);

const mapContainer = ref<HTMLElement | null>(null);

let mapInstance: any = null;

let marker: any = null;

let leaflet: any = null;

function updateCoordinates(lat: number, lng: number) {
    latitude.value = lat.toFixed(7);
    longitude.value = lng.toFixed(7);
}

function placeMarker(lat: number, lng: number) {
    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = leaflet
            .marker([lat, lng], { draggable: true })
            .addTo(mapInstance);

        marker.on('dragend', () => {
            const pos = marker.getLatLng();
            updateCoordinates(pos.lat, pos.lng);
        });
    }

    updateCoordinates(lat, lng);
}

async function initMap() {
    if (!mapContainer.value) return;

    leaflet = await import('leaflet');

    const hasCoords = latitude.value !== '' && longitude.value !== '';
    const initialLat = hasCoords ? Number(latitude.value) : 20;
    const initialLng = hasCoords ? Number(longitude.value) : 0;
    const initialZoom = hasCoords ? 13 : 2;

    mapInstance = leaflet
        .map(mapContainer.value)
        .setView([initialLat, initialLng], initialZoom);

    leaflet
        .tileLayer(
            'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
            {
                attribution:
                    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>',
                maxZoom: 20,
                subdomains: 'abcd',
            },
        )
        .addTo(mapInstance);

    if (hasCoords) {
        placeMarker(initialLat, initialLng);
    }

    mapInstance.on('click', (e: any) => {
        placeMarker(e.latlng.lat, e.latlng.lng);
    });
}

onMounted(() => {
    initMap();
});

onUnmounted(() => {
    mapInstance?.remove();
    mapInstance = null;
    marker = null;
});
</script>

<template>
    <div class="space-y-2">
        <div
            ref="mapContainer"
            :style="{ height: props.height }"
            class="z-0 w-full cursor-crosshair rounded-md border"
        />
        <p v-if="!latitude && !longitude" class="text-sm text-muted-foreground">
            Click on the map to set the location.
        </p>
        <div v-else class="grid grid-cols-2 gap-4">
            <p class="text-sm text-muted-foreground">
                Lat:
                <span class="font-mono text-foreground">{{ latitude }}</span>
            </p>
            <p class="text-sm text-muted-foreground">
                Lng:
                <span class="font-mono text-foreground">{{ longitude }}</span>
            </p>
        </div>
    </div>
</template>
