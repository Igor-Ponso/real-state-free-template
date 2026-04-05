export interface AdminDashboardStats {
    total_properties: number;
    active_properties: number;
    draft_properties: number;
    sold_properties: number;
    total_inquiries: number;
    unread_inquiries: number;
    total_agents: number;
    total_clients: number;
}

export interface AdminProperty {
    id: number;
    title: string;
    slug: string;
    description: string;
    price: string;
    price_raw: number;
    currency: string;
    address: string;
    city: string | null;
    city_id: number;
    neighborhood: string | null;
    state: string;
    zip_code: string;
    latitude: string | null;
    longitude: string | null;
    bedrooms: number;
    bathrooms: number;
    area_sqft: number;
    lot_size_sqft: number | null;
    year_built: number | null;
    parking_spaces: number;
    floor: number | null;
    total_floors: number | null;
    unit_amenities: string[];
    building_amenities: string[];
    deposit: number | null;
    lease_length_months: number | null;
    available_from: string | null;
    pets_allowed: boolean;
    meta_title: string | null;
    meta_description: string | null;
    property_type: string | null;
    property_type_id: number;
    listing_type: string | null;
    listing_type_id: number;
    status: string | null;
    status_slug: string | null;
    property_status_id: number;
    is_published: boolean;
    is_featured: boolean;
    inquiries_count: number;
    images: string[];
    created_at: string;
}

export interface AdminInquiry {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    message: string;
    status: string | null;
    status_slug: string | null;
    inquiry_status_id: number;
    property_title: string | null;
    property_slug: string | null;
    replied_at: string | null;
    created_at: string;
}

export interface AdminMediaItem {
    id: number;
    url: string;
    name: string;
    size: number;
}

export interface LookupOption {
    id: number;
    name: string;
    slug: string;
}
