@props([
    'stops' => [],
    'currentLat' => null,
    'currentLng' => null,
    'height' => '320px',
])

@php
    $hasCoords = collect($stops)->contains(fn($stop) => isset($stop['lat']) && isset($stop['lng']));
    $mapId = 'route-map-' . uniqid();
@endphp

@if (!$hasCoords && !$currentLat)
    <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-center text-sm text-gray-500" style="min-height: {{ $height }}">
        <p class="mb-2">📍 Route planning map will appear when stops have coordinates.</p>
        <p class="text-xs">Add location coordinates to work orders to enable route visualization.</p>
    </div>
@else
    <div
        id="{{ $mapId }}"
        class="rounded-lg overflow-hidden border border-gray-200"
        style="height: {{ $height }}; min-height: 280px;"
        x-data="{
            map: null,
            markers: [],
            routeLine: null,

            init() {
                // Load Leaflet CSS if not already loaded
                if (!document.querySelector('link[href*=\"leaflet\"]')) {
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    document.head.appendChild(link);
                }

                // Load Leaflet JS if not already loaded
                if (typeof L === 'undefined') {
                    const script = document.createElement('script');
                    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    script.onload = () => this.initMap();
                    document.head.appendChild(script);
                } else {
                    this.$nextTick(() => this.initMap());
                }
            },

            initMap() {
                const stops = @js($stops);
                const currentLat = @js($currentLat);
                const currentLng = @js($currentLng);

                // Find center point
                let centerLat = currentLat || 54.6872;
                let centerLng = currentLng || 25.2797;

                const validStops = stops.filter(s => s.lat && s.lng);
                if (validStops.length > 0) {
                    centerLat = validStops[0].lat;
                    centerLng = validStops[0].lng;
                }

                // Initialize map
                this.map = L.map(this.$el.id).setView([centerLat, centerLng], 12);

                // Add OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a>',
                    maxZoom: 19
                }).addTo(this.map);

                // Add current location marker (technician location)
                if (currentLat && currentLng) {
                    const techIcon = L.divIcon({
                        className: 'tech-marker',
                        html: '<div style=\"background: #4F46E5; width: 14px; height: 14px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);\"></div>',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    });
                    L.marker([currentLat, currentLng], { icon: techIcon })
                        .bindPopup('<strong>Your Location</strong>')
                        .addTo(this.map);
                }

                // Add stop markers
                const coords = [];
                validStops.forEach((stop, index) => {
                    const priorityColors = {
                        urgent: '#DC2626',
                        high: '#F97316',
                        standard: '#3B82F6',
                        routine: '#22C55E'
                    };
                    const color = priorityColors[stop.priority] || '#3B82F6';

                    const stopIcon = L.divIcon({
                        className: 'stop-marker',
                        html: `<div style=\"background: ${color}; width: 28px; height: 28px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 12px;\">${stop.sequence || index + 1}</div>`,
                        iconSize: [34, 34],
                        iconAnchor: [17, 17]
                    });

                    const marker = L.marker([stop.lat, stop.lng], { icon: stopIcon })
                        .bindPopup(`
                            <div style=\"min-width: 180px;\">
                                <strong>Stop ${stop.sequence || index + 1}: ${stop.label || 'Service'}</strong>
                                <p style=\"margin: 4px 0 0; font-size: 12px; color: #666;\">${stop.address || 'No address'}</p>
                                <p style=\"margin: 2px 0 0; font-size: 11px; color: #888;\">${stop.time ? 'ETA: ' + stop.time : ''} ${stop.travel_minutes ? '• ' + stop.travel_minutes + ' min travel' : ''}</p>
                            </div>
                        `)
                        .addTo(this.map);

                    this.markers.push(marker);
                    coords.push([stop.lat, stop.lng]);
                });

                // Draw route line
                if (currentLat && currentLng && coords.length > 0) {
                    coords.unshift([currentLat, currentLng]);
                }
                if (coords.length >= 2) {
                    this.routeLine = L.polyline(coords, {
                        color: '#4F46E5',
                        weight: 3,
                        opacity: 0.7,
                        dashArray: '8, 8'
                    }).addTo(this.map);
                }

                // Fit map to bounds
                if (coords.length > 0) {
                    const bounds = L.latLngBounds(coords);
                    this.map.fitBounds(bounds, { padding: [40, 40] });
                }
            }
        }"
    ></div>
@endif
