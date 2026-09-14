<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { Map as MapLibreMap, Marker, Popup } from 'maplibre-gl'
import 'maplibre-gl/dist/maplibre-gl.css'
import type { LatestPosition } from '../api'
import { FRESHNESS_COLORS, getHeading, type Freshness } from '../freshness'

const props = defineProps<{
  positions: LatestPosition[]
  selectedId?: string | null
}>()

const emit = defineEmits<{
  select: [vesselId: string]
  tileError: []
  ready: [map: MapLibreMap]
}>()

const mapContainer = ref<HTMLDivElement>()
let map: MapLibreMap | null = null
const markers = new globalThis.Map<string, Marker>()
const popups = new globalThis.Map<string, Popup>()

function createMarker(position: LatestPosition): Marker {
  const color =
    FRESHNESS_COLORS[position.freshness as Freshness] ??
    FRESHNESS_COLORS.OFFLINE
  const heading = getHeading(position)

  const el = document.createElement('div')
  el.setAttribute('data-testid', 'vessel-marker')
  el.setAttribute('data-vessel-id', position.vessel_id)
  el.setAttribute('role', 'button')
  el.setAttribute('tabindex', '0')
  el.setAttribute(
    'aria-label',
    `Kapal ${position.name ?? position.mmsi ?? 'tidak diketahui'}, status ${position.freshness}`,
  )
  el.style.cssText = `
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: ${color};
    border: 2px solid rgba(255,255,255,0.8);
    cursor: pointer;
  `
  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    el.style.transition = 'transform 0.15s'
  }
  el.style.transform = `rotate(${heading}deg)`

  const arrow = document.createElement('div')
  arrow.style.cssText = `
    position: absolute;
    top: -6px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    border-bottom: 6px solid ${color};
  `
  el.appendChild(arrow)

  const popupContent = document.createElement('div')
  popupContent.style.cssText = 'font-family: monospace; font-size: 12px;'

  const strong = document.createElement('strong')
  strong.textContent = position.name ?? position.mmsi ?? 'Unknown'
  popupContent.appendChild(strong)

  const lines: [string, string][] = [
    ['MMSI', position.mmsi ?? '-'],
    ['Status', position.freshness],
    ['SOG', position.sog_knots != null ? `${position.sog_knots} kn` : '-'],
  ]
  if (position.destination_text) {
    lines.push(['Tujuan', position.destination_text])
  }

  for (const [label, value] of lines) {
    popupContent.appendChild(document.createElement('br'))
    const span = document.createElement('span')
    span.textContent = `${label}: ${value}`
    if (label === 'Status') {
      span.style.color = color
    }
    popupContent.appendChild(span)
  }

  const popup = new Popup({
    offset: 20,
    closeButton: false,
  }).setDOMContent(popupContent)
  popups.set(position.vessel_id, popup)

  const marker = new Marker({ element: el, anchor: 'center' })
    .setLngLat([position.longitude, position.latitude])
    .setPopup(popup)
    .addTo(map!)

  el.addEventListener('click', () => {
    emit('select', position.vessel_id)
  })
  el.addEventListener('keydown', (e: KeyboardEvent) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault()
      emit('select', position.vessel_id)
    }
  })

  return marker
}

function updateMarkers(): void {
  if (!map) return

  const currentIds = new Set(props.positions.map((p) => p.vessel_id))

  for (const [id, marker] of markers) {
    if (!currentIds.has(id)) {
      marker.remove()
      markers.delete(id)
      popups.delete(id)
    }
  }

  for (const position of props.positions) {
    const existing = markers.get(position.vessel_id)
    if (existing) {
      existing.setLngLat([position.longitude, position.latitude])
    } else {
      markers.set(position.vessel_id, createMarker(position))
    }
  }
}

onMounted(() => {
  if (!mapContainer.value) return

  map = new MapLibreMap({
    container: mapContainer.value,
    style: {
      version: 8,
      sources: {
        'osm-tiles': {
          type: 'raster',
          tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
          tileSize: 256,
          attribution: '© OpenStreetMap contributors',
        },
      },
      layers: [
        {
          id: 'background',
          type: 'background',
          paint: { 'background-color': '#080b12' },
        },
        {
          id: 'osm-layer',
          type: 'raster',
          source: 'osm-tiles',
          paint: {
            'raster-opacity': 0.4,
          },
        },
      ],
    },
    center: [112, -2],
    zoom: 5,
  })

  map.on('load', () => {
    updateMarkers()
    emit('ready', map!)
  })

  map.on('error', (e) => {
    const source = (e as { source?: string }).source
    if (source === 'osm-tiles') {
      emit('tileError')
    }
  })
})

onUnmounted(() => {
  markers.forEach((m) => m.remove())
  markers.clear()
  popups.clear()
  map?.remove()
  map = null
})

watch(() => props.positions, updateMarkers, { deep: true })
</script>

<template>
  <div ref="mapContainer" data-testid="map-container" class="h-full w-full" />
</template>
