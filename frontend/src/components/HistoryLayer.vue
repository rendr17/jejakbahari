<script setup lang="ts">
import { onMounted, onUnmounted, watch } from 'vue'
import type { Map as MapLibreMap } from 'maplibre-gl'
import type { PositionHistoryPoint } from '../api'

const props = defineProps<{
  map: MapLibreMap | unknown
  points: PositionHistoryPoint[]
}>()

let layerId = 'history-line'
let sourceId = 'history-source'

function getMap(): MapLibreMap | null {
  return (props.map as MapLibreMap) ?? null
}

function clearLayer(): void {
  const map = getMap()
  if (!map) return
  if (map.getLayer(layerId)) map.removeLayer(layerId)
  if (map.getSource(sourceId)) map.removeSource(sourceId)
}

function renderHistory(): void {
  const map = getMap()
  if (!map) return
  clearLayer()
  if (props.points.length < 2) return

  const coordinates = props.points
    .filter((p) => p.latitude != null && p.longitude != null)
    .map((p) => [p.longitude, p.latitude])

  if (coordinates.length < 2) return

  map.addSource(sourceId, {
    type: 'geojson',
    data: {
      type: 'Feature',
      geometry: {
        type: 'LineString',
        coordinates,
      },
      properties: {},
    },
  })

  map.addLayer({
    id: layerId,
    type: 'line',
    source: sourceId,
    layout: {
      'line-join': 'round',
      'line-cap': 'round',
    },
    paint: {
      'line-color': 'var(--color-primary)',
      'line-width': 2,
      'line-opacity': 0.7,
    },
  })
}

watch(() => [props.map, props.points], renderHistory, { deep: true })

onMounted(renderHistory)
onUnmounted(clearLayer)
</script>

<template>
  <div data-testid="history-layer" aria-hidden="true" />
</template>
