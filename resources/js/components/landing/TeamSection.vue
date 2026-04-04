<script setup lang="ts">
import { Linkedin, Mail } from 'lucide-vue-next';
import { Card, CardContent } from '@/components/ui/card';
import { useFadeInOnScroll } from '@/composables/useFadeInOnScroll';

const { target: sectionRef, isVisible } = useFadeInOnScroll();

interface TeamMember {
    name: string;
    role: string;
    image: string;
    email: string;
}

const team: TeamMember[] = [
    {
        name: 'James Anderson',
        role: 'Founder & CEO',
        image: '/images/team/agent-1.jpg',
        email: 'james@luxuryestate.com',
    },
    {
        name: 'Sofia Martinez',
        role: 'Head of Sales',
        image: '/images/team/agent-2.jpg',
        email: 'sofia@luxuryestate.com',
    },
    {
        name: 'Emily Chen',
        role: 'Senior Agent',
        image: '/images/team/agent-3.jpg',
        email: 'emily@luxuryestate.com',
    },
    {
        name: 'Michael Brooks',
        role: 'Property Consultant',
        image: '/images/team/agent-4.jpg',
        email: 'michael@luxuryestate.com',
    },
];
</script>

<template>
    <section ref="sectionRef" class="bg-landing-warm-beige px-6 py-28">
        <div class="mx-auto max-w-6xl">
            <div
                class="mb-16 text-center transition-all duration-700"
                :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
            >
                <p class="font-body text-sm font-medium uppercase tracking-[0.3em] text-landing-gold">
                    The People Behind the Brand
                </p>
                <h2 class="mt-3 font-serif text-3xl font-semibold tracking-tight md:text-4xl">
                    Meet Our Team
                </h2>
                <p class="mx-auto mt-4 max-w-xl font-body text-muted-foreground">
                    Dedicated professionals who turn your real estate dreams into reality
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="(member, index) in team"
                    :key="member.name"
                    class="transition-all duration-700"
                    :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-12 opacity-0'"
                    :style="{ transitionDelay: `${300 + index * 100}ms` }"
                >
                    <div class="group text-center">
                        <div class="mx-auto mb-5 size-40 overflow-hidden rounded-full shadow-lg ring-4 ring-white dark:ring-neutral-800">
                            <img
                                :src="member.image"
                                :alt="member.name"
                                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                                loading="lazy"
                            />
                        </div>
                        <h3 class="font-serif text-lg font-semibold">
                            {{ member.name }}
                        </h3>
                        <p class="mt-1 font-body text-sm text-landing-gold">
                            {{ member.role }}
                        </p>
                        <div class="mt-3 flex justify-center gap-3">
                            <a
                                :href="`mailto:${member.email}`"
                                class="text-muted-foreground transition-colors hover:text-landing-gold"
                                :aria-label="`Email ${member.name}`"
                            >
                                <Mail class="size-4" />
                            </a>
                            <a
                                href="#"
                                class="text-muted-foreground transition-colors hover:text-landing-gold"
                                :aria-label="`${member.name}'s LinkedIn`"
                            >
                                <Linkedin class="size-4" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
