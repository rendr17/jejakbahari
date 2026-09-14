<script setup lang="ts">
import { onMounted, onUnmounted, watch } from 'vue'
import { Marker, Popup, type Map as MapLibreMap } from 'maplibre-gl'
import type { Port } from '../api'

const props = defineProps<{
  map: MapLibreMap | unknown
  ports: Port[]
}>()

const emit = defineEmits<{
  (e: 'select', port: Port): void
}>()

const markers = new globalThis.Map<string, Marker>()
const popups = new globalThis.Map<string, Popup>()

function getMap(): MapLibreMap | null {
  return (props.map as MapLibreMap) ?? null
}

function clearMarkers(): void {
  markers.forEach((m) => m.remove())
  popups.forEach((p) => p.remove())
  markers.clear()
  popups.clear()
}

function renderMarkers(): void {
  const map = getMap()
  if (!map) return
  clearMarkers()
  for (const port of props.ports) {
    if (port.latitude == null || port.longitude == null) continue
    const el = document.createElement('div')
    el.setAttribute('data-testid', `port-marker-${port.code}`)
    el.setAttribute('role', 'button')
    el.setAttribute('aria-label', `Pelabuhan ${port.name}`)
    el.setAttribute('tabindex', '0')
    el.className = 'port-marker'
    el.style.width = '12px'
    el.style.height = '12px'
    el.style.borderRadius = '50%'
    el.style.background = 'var(--color-map-port)'
    el.style.border = '2px solid var(--color-surface-elevated)'
    el.style.cursor = 'pointer'

    const marker = new Marker({ element: el })
      .setLngLat([port.longitude, port.latitude])
      .addTo(map)

    const popupContent = document.createElement('div')
    popupContent.className = 'port-popup'
    const name = document.createElement('p')
    name.textContent = port.name
    name.style.fontWeight = '600'
    name.style.margin = '0'
    const code = document.createElement('p')
    code.textContent = `Kode: ${port.code}`
    code.style.fontSize = '0.75rem'
    code.style.color = 'var(--color-text-secondary)'
    code.style.margin = '0'
    popupContent.appendChild(name)
    popupContent.appendChild(code)

    const popup = new Popup({ offset: 16, closeButton: true }).setDOMContent(
      popupContent,
    )
    marker.setPopup(popup)

    el.addEventListener('click', () => emit('select', port))
    el.addEventListener('keydown', (e: KeyboardEvent) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault()
        emit('select', port)
      }
    })

    markers.set(port.id, marker)
    popups.set(port.id, popup)
  }
}

watch(() => [props.map, props.ports], renderMarkers, { deep: true })

onMounted(renderMarkers)
onUnmounted(clearMarkers)
</script>

<template>
  <div data-testid="port-layer" aria-hidden="true" />
</template>
