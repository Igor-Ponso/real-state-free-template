export interface FeaturedProperty {
    id: number;
    title: string;
    slug: string;
    location: string;
    price: string;
    bedrooms: number;
    bathrooms: number;
    area_sqft: string;
    listing_type?: string;
    property_type?: string;
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
}

export interface PropertyFilters {
    propertyTypes: FilterOption[];
    cities: FilterOption[];
    listingTypes: FilterOption[];
}

export interface AppliedFilters {
    type?: string;
    city?: string;
    listing?: string;
    min_price?: string;
    max_price?: string;
    bedrooms?: string;
    search?: string;
    sort?: string;
}
