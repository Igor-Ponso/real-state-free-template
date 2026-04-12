<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Github, Mail, MapPin, Phone } from 'lucide-vue-next';
import { computed } from 'vue';

import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';

const page = usePage();
const name = computed(() => page.props.name as string);

const navLinks = [
    { label: 'Home', href: '/' },
    { label: 'Properties', href: '/properties' },
    { label: 'About', href: '/#about' },
    { label: 'Contact', href: '/#contact' },
];
</script>

<template>
    <footer
        id="contact"
        class="bg-linear-to-b from-landing-deep-teal to-landing-charcoal px-6 pt-16 pb-8 text-white"
    >
        <div class="mx-auto max-w-6xl">
            <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <div class="flex items-center gap-2">
                        <AppLogoIcon
                            class="size-6 fill-current text-landing-gold"
                        />
                        <span class="font-serif text-lg font-semibold">{{
                            name
                        }}</span>
                    </div>
                    <p
                        class="mt-4 font-body text-sm leading-relaxed text-white/60"
                    >
                        Luxury real estate redefined. We connect discerning
                        buyers with extraordinary properties across Canada.
                    </p>
                </div>

                <div>
                    <h4
                        class="font-serif text-sm font-semibold tracking-wider uppercase"
                    >
                        Quick Links
                    </h4>
                    <nav class="mt-4 flex flex-col gap-3">
                        <Link
                            v-for="link in navLinks"
                            :key="link.href"
                            :href="link.href"
                            class="text-left font-body text-sm text-white/60 transition-colors hover:text-white"
                        >
                            {{ link.label }}
                        </Link>
                    </nav>
                </div>

                <div>
                    <h4
                        class="font-serif text-sm font-semibold tracking-wider uppercase"
                    >
                        Contact
                    </h4>
                    <div
                        class="mt-4 flex flex-col gap-3 font-body text-sm text-white/60"
                    >
                        <div class="flex items-center gap-2">
                            <MapPin class="size-4 shrink-0" />
                            <span>1055 W Georgia St, Vancouver, BC</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Phone class="size-4 shrink-0" />
                            <span>+1 (604) 555-0192</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Mail class="size-4 shrink-0" />
                            <span>info@sovereignestates.com</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h4
                        class="font-serif text-sm font-semibold tracking-wider uppercase"
                    >
                        Newsletter
                    </h4>
                    <p class="mt-4 font-body text-sm text-white/60">
                        Stay updated with new listings and market insights.
                    </p>
                    <div class="mt-4 flex gap-2">
                        <Input
                            type="email"
                            placeholder="Your email"
                            class="border-white/20 bg-white/10 text-white placeholder:text-white/40"
                        />
                        <Button
                            size="default"
                            class="shrink-0 bg-landing-gold text-landing-gold-foreground hover:bg-landing-gold/90"
                        >
                            Subscribe
                        </Button>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex justify-center gap-4">
                <!-- Facebook -->
                <button
                    class="flex size-10 items-center justify-center rounded-full text-white/40 transition-colors hover:bg-white/10 hover:text-white"
                    aria-label="Facebook"
                >
                    <svg
                        class="size-5"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"
                        />
                    </svg>
                </button>
                <!-- Twitter / X -->
                <button
                    class="flex size-10 items-center justify-center rounded-full text-white/40 transition-colors hover:bg-white/10 hover:text-white"
                    aria-label="Twitter"
                >
                    <svg
                        class="size-5"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"
                        />
                    </svg>
                </button>
                <!-- Instagram -->
                <button
                    class="flex size-10 items-center justify-center rounded-full text-white/40 transition-colors hover:bg-white/10 hover:text-white"
                    aria-label="Instagram"
                >
                    <svg
                        class="size-5"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"
                        />
                    </svg>
                </button>
                <!-- LinkedIn -->
                <button
                    class="flex size-10 items-center justify-center rounded-full text-white/40 transition-colors hover:bg-white/10 hover:text-white"
                    aria-label="LinkedIn"
                >
                    <svg
                        class="size-5"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"
                        />
                    </svg>
                </button>
            </div>

            <Separator class="my-8 bg-white/10" />

            <!-- Template credit + dedication -->
            <div class="mb-6 text-center">
                <p
                    class="font-body text-sm leading-relaxed text-white/30 italic"
                >
                    "Unless the Lord builds the house, the builders labor in
                    vain."
                    <span class="not-italic">— Psalm 127:1</span>
                </p>
            </div>

            <!-- Remove this block when using this template for your own project -->
            <div
                class="rounded-lg border border-landing-gold/20 bg-landing-deep-teal/60 px-6 py-4 text-center"
            >
                <p class="font-body text-sm text-white/50">
                    A free, open-source template crafted with care for the
                    Laravel & Vue community.
                    <br class="hidden sm:inline" />
                    Use it freely, build something amazing.
                </p>
                <div
                    class="mt-4 flex flex-wrap items-center justify-center gap-3"
                >
                    <a
                        href="https://github.com/Igor-Ponso/real-state-free-template"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 rounded-md border border-white/10 px-4 py-2 font-body text-xs text-white/50 transition-colors hover:border-white/20 hover:text-white"
                    >
                        <Github class="size-3.5" />
                        View on GitHub
                    </a>
                    <a
                        href="https://buymeacoffee.com/igorponso"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 rounded-md bg-landing-gold px-4 py-2 font-body text-xs font-semibold text-landing-gold-foreground transition-colors hover:bg-landing-gold/90"
                    >
                        Buy Me A Coffee
                    </a>
                </div>
            </div>

            <div class="mt-6 text-center font-body text-xs text-white/30">
                <p>
                    &copy; {{ new Date().getFullYear() }} {{ name }}. All rights
                    reserved.
                </p>
            </div>
        </div>
    </footer>
</template>
