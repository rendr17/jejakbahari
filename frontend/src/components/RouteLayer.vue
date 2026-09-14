<script setup lang="ts">
import { onMounted, onUnmounted, watch } from 'vue'
import type { Map as MapLibreMap } from 'maplibre-gl'
import type { Port, RouteSummary } from '../api'

const props = defineProps<{
  map: MapLibreMap | unknown
  routes: RouteSummary[]
  ports: Port[]
}>()

const emit = defineEmits<{
  (e: 'select', route: RouteSummary): void
}>()

let layerId = 'routes-line'
let sourceId = 'routes-source'

function getMap(): MapLibreMap | null {
  return (props.map as MapLibreMap) ?? null
}

function clearLayer(): void {
  const map = getMap()
  if (!map) return
  if (map.getLayer(layerId)) map.removeLayer(layerId)
  if (map.getSource(sourceId)) map.removeSource(sourceId)
}

function renderRoutes(): void {
  const map = getMap()
  if (!map) return
  clearLayer()

  const portMap = new globalThis.Map(props.ports.map((p) => [p.id, p]))

  const features = props.routes
    .map((r) => {
      const origin = r.origin_port ? portMap.get(r.origin_port.id) : null
      const dest = r.destination_port
        ? portMap.get(r.destination_port.id)
        : null
      if (
        !origin ||
        !dest ||
        origin.latitude == null ||
        origin.longitude == null ||
        dest.latitude == null ||
        dest.longitude == null
      ) {
        return null
      }
      return {
        type: 'Feature' as const,
        geometry: {
          type: 'LineString' as const,
          coordinates: [
            [origin.longitude, origin.latitude],
            [dest.longitude, dest.latitude],
          ],
        },
        properties: {
          id: r.id,
          name: r.name,
          route_type: r.route_type,
        },
      }
    })
    .filter((f): f is NonNullable<typeof f> => f !== null)

  if (features.length === 0) return

  map.addSource(sourceId, {
    type: 'geojson',
    data: { type: 'FeatureCollection', features },
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
      'line-color': 'var(--color-map-route)',
      'line-width': 2,
      'line-opacity': 0.6,
      'line-dasharray': [2, 2],
    },
  })

  map.on('click', layerId, (e) => {
    const feature = e.features?.[0]
    if (!feature) return
    const id = feature.properties?.id as string | undefined
    const route = props.routes.find((r) => r.id === id)
    if (route) emit('select', route)
  })
}

watch(() => [props.map, props.routes, props.ports], renderRoutes, {
  deep: true,
})

onMounted(renderRoutes)
onUnmounted(clearLayer)
</script>

<template>
  <div data-testid="route-layer" aria-hidden="true" />
</template>
