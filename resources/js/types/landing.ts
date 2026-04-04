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
