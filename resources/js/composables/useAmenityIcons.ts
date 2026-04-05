import {
    Baby,
    Bike,
    Building2,
    Car,
    CloudSun,
    ConciergeBell,
    DoorOpen,
    Dumbbell,
    Fence,
    Flame,
    Leaf,
    Music,
    Package,
    Plug,
    Shield,
    Smartphone,
    Snowflake,
    Sofa,
    Sun,
    Thermometer,
    Theater,
    UtensilsCrossed,
    WashingMachine,
    Waves,
    Wind,
} from 'lucide-vue-next';
import type { Component } from 'vue';

const unitIcons: Record<string, Component> = {
    balcony: Fence,
    den: Sofa,
    solarium: Sun,
    fireplace: Flame,
    in_suite_laundry: WashingMachine,
    walk_in_closet: DoorOpen,
    hardwood_floors: Leaf,
    air_conditioning: Snowflake,
    dishwasher: UtensilsCrossed,
    storage_locker: Package,
    ev_charger: Plug,
    smart_home: Smartphone,
    heated_floors: Thermometer,
};

const buildingIcons: Record<string, Component> = {
    pool: Waves,
    gym: Dumbbell,
    concierge: ConciergeBell,
    doorman: DoorOpen,
    elevator: Building2,
    rooftop_deck: CloudSun,
    party_room: Music,
    bbq_area: Flame,
    sauna: Thermometer,
    steam_room: Wind,
    private_theater: Theater,
    shared_laundry: WashingMachine,
    bike_storage: Bike,
    guest_suite: Sofa,
    playground: Baby,
    tennis_court: Dumbbell,
    security: Shield,
    underground_parking: Car,
    garden: Leaf,
};

export function useAmenityIcons() {
    function getIcon(slug: string): Component | null {
        return unitIcons[slug] ?? buildingIcons[slug] ?? null;
    }

    return { unitIcons, buildingIcons, getIcon };
}
