export type PostCategory =
    | 'adventure'
    | 'beach'
    | 'city'
    | 'cultural'
    | 'food'
    | 'mountain'
    | 'nature'
    | 'road_trip';

export type PostStatus = 'draft' | 'published';

export type PostPhoto = {
    id: number;
    post_id: number;
    path: string;
    is_cover: boolean;
    order: number;
};

export type Post = {
    id: number;
    user_id: number;
    title: string;
    slug: string;
    content: string;
    photos: PostPhoto[];
    cover_photo: PostPhoto | null;
    location_name: string;
    latitude: number;
    longitude: number;
    travel_date: string;
    category: PostCategory;
    tags: string[] | null;
    is_featured: boolean;
    status: PostStatus;
    published_at: string | null;
    created_at: string;
    updated_at: string;
};

export type Comment = {
    id: number;
    post_id: number;
    parent_id: number | null;
    guest_name: string;
    content: string;
    created_at: string;
    replies?: Comment[];
};

export type Seo = {
    title: string;
    description: string;
    canonical: string;
    og_image: string | null;
};

export type PaginatedPosts = {
    data: Post[];
};

export type PostStats = {
    totalPosts: number;
    publishedCount: number;
    draftCount: number;
};

export const categoryLabels: Record<PostCategory, string> = {
    adventure: 'Adventure',
    beach: 'Beach',
    city: 'City',
    cultural: 'Cultural',
    food: 'Food',
    mountain: 'Mountain',
    nature: 'Nature',
    road_trip: 'Road Trip',
};
