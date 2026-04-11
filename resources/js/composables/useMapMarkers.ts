import * as L from 'leaflet';
import type { Ref } from 'vue';

type StateChecker = ((id: number) => boolean) | undefined;

/**
 * Leaflet's own MarkerOptions accepts `Icon | DivIcon`, but vue-leaflet's
 * LMarker prop is typed as `Icon<IconOptions>` which excludes DivIcon.
 * DivIcon extends Icon<DivIconOptions> where DivIconOptions extends
 * BaseIconOptions (not IconOptions), so the generic parameter doesn't match.
 * This is a known type narrowing issue in @vue-leaflet/vue-leaflet.
 * The cast is safe — Leaflet handles DivIcon at runtime without issue.
 */
type MarkerIcon = L.Icon<L.IconOptions>;

const pinSvg = (color: string, symbol: string): string => `
    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="40" viewBox="0 0 32 40">
        <defs><filter id="s"><feDropShadow dx="0" dy="1" stdDeviation="1.5" flood-opacity="0.3"/></filter></defs>
        <path d="M16 2C8.8 2 3 7.8 3 15c0 10 13 22 13 22s13-12 13-22C29 7.8 23.2 2 16 2z" fill="${color}" stroke="white" stroke-width="2" filter="url(#s)"/>
        <text x="16" y="19" text-anchor="middle" font-size="13" font-weight="bold" fill="white">${symbol}</text>
    </svg>`;

const createIcon = (color: string, symbol: string): MarkerIcon =>
    L.divIcon({
        html: pinSvg(color, symbol),
        className: '',
        iconSize: [32, 40],
        iconAnchor: [16, 40],
        popupAnchor: [0, -40],
    }) as MarkerIcon;

const icons = {
    default: createIcon('#c5944a', ''),
    favorite: createIcon('#ef4444', '♥'),
    dismissed: createIcon('#9ca3af', '—'),
};

export function useMapMarkers(
    isFavorite: StateChecker,
    isDismissed: StateChecker,
    hoveredId: Ref<number | null>,
) {
    const markerIcon = (propertyId: number): MarkerIcon => {
        if (isFavorite?.(propertyId)) {
            return icons.favorite;
        }

        if (isDismissed?.(propertyId)) {
            return icons.dismissed;
        }

        return icons.default;
    };

    const markerState = (propertyId: number): string => {
        if (isFavorite?.(propertyId)) {
            return 'fav';
        }

        if (isDismissed?.(propertyId)) {
            return 'dis';
        }

        return 'def';
    };

    const markerOpacity = (propertyId: number): number => {
        if (isDismissed?.(propertyId)) {
            return 0.6;
        }

        if (hoveredId.value !== null && hoveredId.value !== propertyId) {
            return 0.5;
        }

        return 1;
    };

    return { markerIcon, markerState, markerOpacity };
}
