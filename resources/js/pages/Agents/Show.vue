<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Award,
    BadgeCheck,
    Facebook,
    Instagram,
    Linkedin,
    Mail,
    Phone,
    Twitter,
} from 'lucide-vue-next';
import { computed } from 'vue';

import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';
import PropertyCard from '@/components/landing/PropertyCard.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { show as propertyShow } from '@/routes/properties';
import type { FeaturedProperty } from '@/types/landing';

interface AgentPayload {
    id: number;
    slug: string;
    name: string;
    email: string | null;
    phone: string | null;
    bio: string | null;
    license_number: string | null;
    specializations: string[];
    social_links: {
        linkedin?: string;
        instagram?: string;
        twitter?: string;
        facebook?: string;
    };
    photo_url: string;
    photo_is_placeholder: boolean;
}

const props = defineProps<{
    agent: AgentPayload;
    listings: FeaturedProperty[];
    canonicalUrl: string;
    canRegister?: boolean;
}>();

const pageTitle = computed(
    () => `${props.agent.name} — Luxury Real Estate Agent`,
);
const pageDescription = computed(
    () =>
        props.agent.bio?.slice(0, 160) ??
        `Get in touch with ${props.agent.name}, a licensed real estate agent.`,
);

const hasSocial = computed(() => {
    const links = props.agent.social_links;
    return !!(
        links.linkedin ||
        links.instagram ||
        links.twitter ||
        links.facebook
    );
});
</script>

<template>
    <Head :title="pageTitle">
        <meta name="description" :content="pageDescription" />
        <meta property="og:type" content="profile" />
        <meta property="og:url" :content="canonicalUrl" />
        <meta property="og:title" :content="pageTitle" />
        <meta property="og:description" :content="pageDescription" />
        <meta property="og:image" :content="agent.photo_url" />
        <meta property="og:image:alt" :content="`Photo of ${agent.name}`" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="pageTitle" />
        <meta name="twitter:description" :content="pageDescription" />
        <meta name="twitter:image" :content="agent.photo_url" />
        <link rel="canonical" :href="canonicalUrl" />
    </Head>

    <LandingHeader :can-register="canRegister ?? false" />

    <main>
        <!-- Hero -->
        <section
            class="relative bg-gradient-to-b from-landing-deep-teal to-landing-charcoal py-16 text-white"
        >
            <div
                class="mx-auto flex max-w-5xl flex-col items-center gap-8 px-6 md:flex-row md:items-start"
            >
                <div class="relative shrink-0">
                    <div
                        class="size-40 overflow-hidden rounded-full border-4 border-landing-gold/40 shadow-xl"
                    >
                        <img
                            :src="agent.photo_url"
                            :alt="`Photo of ${agent.name}`"
                            class="size-full object-cover"
                        />
                    </div>
                    <p
                        v-if="agent.photo_is_placeholder"
                        class="absolute -bottom-2 left-1/2 -translate-x-1/2 rounded-full bg-landing-charcoal/80 px-2 py-0.5 text-[10px] tracking-wider text-white/50 uppercase"
                    >
                        Sample
                    </p>
                </div>

                <div class="text-center md:text-left">
                    <h1
                        class="font-serif text-4xl font-semibold tracking-tight"
                    >
                        {{ agent.name }}
                    </h1>

                    <div
                        v-if="agent.license_number"
                        class="mt-2 inline-flex items-center gap-1 text-sm text-white/60"
                    >
                        <BadgeCheck class="size-4 text-landing-gold" />
                        Licensed · {{ agent.license_number }}
                    </div>

                    <p
                        v-if="agent.bio"
                        class="mt-5 max-w-2xl leading-relaxed text-white/80"
                    >
                        {{ agent.bio }}
                    </p>

                    <div
                        v-if="agent.specializations.length"
                        class="mt-5 flex flex-wrap justify-center gap-2 md:justify-start"
                    >
                        <Badge
                            v-for="tag in agent.specializations"
                            :key="tag"
                            class="border-landing-gold/30 bg-landing-gold/10 text-landing-gold"
                        >
                            <Award class="mr-1 size-3" /> {{ tag }}
                        </Badge>
                    </div>

                    <!-- Contact row -->
                    <div
                        class="mt-6 flex flex-wrap justify-center gap-3 md:justify-start"
                    >
                        <Button
                            v-if="agent.email"
                            as-child
                            class="bg-landing-gold text-black hover:bg-landing-gold/90"
                        >
                            <a :href="`mailto:${agent.email}`">
                                <Mail class="mr-1.5 size-4" /> Email
                            </a>
                        </Button>
                        <Button
                            v-if="agent.phone"
                            as-child
                            variant="outline"
                            class="border-white/20 bg-transparent text-white hover:bg-white/10 hover:text-white"
                        >
                            <a :href="`tel:${agent.phone.replace(/\s+/g, '')}`">
                                <Phone class="mr-1.5 size-4" />
                                {{ agent.phone }}
                            </a>
                        </Button>
                    </div>

                    <!-- Social -->
                    <div
                        v-if="hasSocial"
                        class="mt-5 flex justify-center gap-3 text-white/50 md:justify-start"
                    >
                        <a
                            v-if="agent.social_links.linkedin"
                            :href="agent.social_links.linkedin"
                            target="_blank"
                            rel="noopener"
                            aria-label="LinkedIn"
                            class="transition-colors hover:text-landing-gold"
                        >
                            <Linkedin class="size-5" />
                        </a>
                        <a
                            v-if="agent.social_links.instagram"
                            :href="agent.social_links.instagram"
                            target="_blank"
                            rel="noopener"
                            aria-label="Instagram"
                            class="transition-colors hover:text-landing-gold"
                        >
                            <Instagram class="size-5" />
                        </a>
                        <a
                            v-if="agent.social_links.twitter"
                            :href="agent.social_links.twitter"
                            target="_blank"
                            rel="noopener"
                            aria-label="Twitter"
                            class="transition-colors hover:text-landing-gold"
                        >
                            <Twitter class="size-5" />
                        </a>
                        <a
                            v-if="agent.social_links.facebook"
                            :href="agent.social_links.facebook"
                            target="_blank"
                            rel="noopener"
                            aria-label="Facebook"
                            class="transition-colors hover:text-landing-gold"
                        >
                            <Facebook class="size-5" />
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Listings -->
        <section class="bg-landing-warm-beige px-6 py-16 dark:bg-background">
            <div class="mx-auto max-w-6xl">
                <div class="mb-8 flex items-end justify-between">
                    <div>
                        <h2
                            class="font-serif text-3xl font-semibold tracking-tight"
                        >
                            Current listings
                        </h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ listings.length }}
                            {{
                                listings.length === 1
                                    ? 'property'
                                    : 'properties'
                            }}
                            currently on the market
                        </p>
                    </div>
                </div>

                <div
                    v-if="listings.length"
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <Link
                        v-for="property in listings"
                        :key="property.id"
                        :href="propertyShow.url({ property: property.slug })"
                        prefetch
                    >
                        <PropertyCard :property="property" variant="grid" />
                    </Link>
                </div>

                <div
                    v-else
                    class="rounded-lg border border-dashed bg-card py-12 text-center"
                >
                    <p class="font-medium">No current listings</p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ agent.name.split(' ')[0] }} has no public listings at
                        the moment. Reach out directly if you're looking for
                        something specific.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <LandingFooter />
</template>
