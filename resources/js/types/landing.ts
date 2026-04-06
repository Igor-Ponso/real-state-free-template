export interface FeaturedProperty {
    id: number;
    title: string;
    slug: string;
    location: string;
    price: string;
    bedrooms: number;
    bathrooms: number;
    area_sqft: string;
    description?: string;
    listing_type?: string;
    property_type?: string;
    latitude?: string | null;
    longitude?: string | null;
    images: string[];
}

export interface Neighborhood {
    id: number;
    name: string;
    state: string;
    slug: string;
    image: string;
    properties_count: number;
}

export interface TeamMember {
    id: number;
    name: string;
    role: string;
    bio: string;
    image: string;
    email: string;
    social_links: Record<string, string> | null;
}

export interface LandingStats {
    properties_sold: number;
    clients: number;
    agents: number;
    cities: number;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedResponse<T> {
    data: T[];
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        links: PaginationLink[];
        path: string;
        per_page: number;
        to: number | null;
        total: number;
    };
}

export interface FilterOption {
    name: string;
    slug: string;
    latitude?: string;
    longitude?: string;
}

export interface PropertyFilters {
    propertyTypes: FilterOption[];
    cities: FilterOption[];
    listingTypes: FilterOption[];
    unitAmenities: FilterOption[];
    buildingAmenities: FilterOption[];
}

export interface PropertyAgent {
    id: number;
    name: string;
    bio: string | null;
    specializations: string[];
    avatar: string;
    social_links: Record<string, string> | null;
}

export interface PropertyCity {
    name: string;
    state: string;
    slug: string;
}

export interface PropertyDetail {
    id: number;
    title: string;
    slug: string;
    description: string;
    price: string;
    currency: string;
    listing_type: string | null;
    listing_type_slug: string | null;
    property_type: string | null;
    property_status: string | null;
    address: string;
    city: PropertyCity | null;
    neighborhood: string | null;
    state: string;
    zip_code: string;
    latitude: string | null;
    longitude: string | null;
    bedrooms: number;
    bathrooms: number;
    area_sqft: string;
    lot_size_sqft: string | null;
    year_built: number | null;
    parking_spaces: number;
    floor: number | null;
    total_floors: number | null;
    unit_amenities: string[];
    building_amenities: string[];
    features: Record<string, string>;
    deposit: string | null;
    lease_length_months: number | null;
    available_from: string | null;
    pets_allowed: boolean;
    is_rental: boolean;
    meta_title: string | null;
    meta_description: string | null;
    images: string[];
    floor_plans: string[];
    agent: PropertyAgent | null;
    published_at: string | null;
}

export interface InquiryFormData {
    property_id: number;
    name: string;
    email: string;
    phone: string;
    message: string;
    honeypot: string;
}

export interface PropertySearchResult {
    id: number;
    title: string;
    slug: string;
    location: string;
    price: string;
    bedrooms: number;
    bathrooms: number;
    image: string;
}

export interface AppliedFilters {
    type?: string | string[];
    city?: string | string[];
    listing?: string | string[];
    min_price?: string;
    max_price?: string;
    bedrooms?: string | string[];
    bedrooms_exact?: string;
    bathrooms?: string | string[];
    bathrooms_exact?: string;
    unit_amenities?: string | string[];
    building_amenities?: string | string[];
    search?: string;
    sort?: string;
}
