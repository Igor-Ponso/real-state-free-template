<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Facebook, Instagram, Linkedin, Mail, MapPin, Phone, Twitter } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';

const page = usePage();
const name = computed(() => page.props.name as string);

const navLinks = [
    { label: 'Home', href: '#hero' },
    { label: 'Properties', href: '#properties' },
    { label: 'About', href: '#about' },
    { label: 'Contact', href: '#contact' },
];

const socialLinks = [
    { icon: Facebook, label: 'Facebook' },
    { icon: Twitter, label: 'Twitter' },
    { icon: Instagram, label: 'Instagram' },
    { icon: Linkedin, label: 'LinkedIn' },
];

function scrollTo(href: string) {
    document.querySelector(href)?.scrollIntoView({ behavior: 'smooth' });
}
</script>

<template>
    <footer id="contact" class="bg-landing-charcoal px-6 pb-8 pt-16 text-white">
        <div class="mx-auto max-w-6xl">
            <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <div class="flex items-center gap-2">
                        <AppLogoIcon class="size-6 fill-current text-white" />
                        <span class="font-serif text-lg font-semibold">{{ name }}</span>
                    </div>
                    <p class="mt-4 font-body text-sm leading-relaxed text-white/60">
                        Luxury real estate redefined. We connect discerning buyers
                        with extraordinary properties.
                    </p>
                </div>

                <div>
                    <h4 class="font-serif text-sm font-semibold uppercase tracking-wider">
                        Quick Links
                    </h4>
                    <nav class="mt-4 flex flex-col gap-3">
                        <button
                            v-for="link in navLinks"
                            :key="link.href"
                            @click="scrollTo(link.href)"
                            class="text-left font-body text-sm text-white/60 transition-colors hover:text-white"
                        >
                            {{ link.label }}
                        </button>
                    </nav>
                </div>

                <div>
                    <h4 class="font-serif text-sm font-semibold uppercase tracking-wider">
                        Contact
                    </h4>
                    <div class="mt-4 flex flex-col gap-3 font-body text-sm text-white/60">
                        <div class="flex items-center gap-2">
                            <MapPin class="size-4 shrink-0" />
                            <span>123 Luxury Ave, New York, NY</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Phone class="size-4 shrink-0" />
                            <span>+1 (555) 123-4567</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Mail class="size-4 shrink-0" />
                            <span>hello@luxuryestate.com</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="font-serif text-sm font-semibold uppercase tracking-wider">
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
                <button
                    v-for="social in socialLinks"
                    :key="social.label"
                    class="flex size-10 items-center justify-center rounded-full text-white/40 transition-colors hover:bg-white/10 hover:text-white"
                    :aria-label="social.label"
                >
                    <component :is="social.icon" class="size-5" />
                </button>
            </div>

            <Separator class="my-8 bg-white/10" />

            <div class="flex flex-col items-center justify-between gap-4 font-body text-sm text-white/40 sm:flex-row">
                <p>&copy; {{ new Date().getFullYear() }} {{ name }}. All rights reserved.</p>
                <a
                    href="https://buymeacoffee.com/igorponso"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="transition-colors hover:text-landing-gold"
                >
                    ☕ Buy Me A Coffee
                </a>
            </div>
        </div>
    </footer>
</template>
