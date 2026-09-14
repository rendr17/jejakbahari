<script setup lang="ts">
import { onMounted, onUnmounted, watch } from 'vue'
import type { Map as MapLibreMap } from 'maplibre-gl'
import type { PositionHistoryPoint } from '../api'

const props = defineProps<{
  map: MapLibreMap | null
  points: PositionHistoryPoint[]
}>()

let layerId = 'history-line'
let sourceId = 'history-source'

function clearLayer(): void {
  if (!props.map) return
  if (props.map.getLayer(layerId)) props.map.removeLayer(layerId)
  if (props.map.getSource(sourceId)) props.map.removeSource(sourceId)
}

function renderHistory(): void {
  if (!props.map) return
  clearLayer()
  if (props.points.length < 2) return

  const coordinates = props.points
    .filter((p) => p.latitude != null && p.longitude != null)
    .map((p) => [p.longitude, p.latitude])

  if (coordinates.length < 2) return

  props.map.addSource(sourceId, {
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

  props.map.addLayer({
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
