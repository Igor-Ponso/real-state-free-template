<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import type { PropertyAgent } from '@/types/landing';

defineProps<{
    agent: PropertyAgent;
}>();

const initials = (name: string): string =>
    name
        .split(' ')
        .map((w) => w[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
</script>

<template>
    <Card class="border-white/10 bg-white/5 backdrop-blur-sm">
        <CardContent class="p-5">
            <h3 class="mb-4 font-serif text-lg font-semibold text-white">
                Listing Agent
            </h3>
            <div class="flex items-center gap-4">
                <Avatar class="size-14">
                    <AvatarImage :src="agent.avatar" :alt="agent.name" />
                    <AvatarFallback
                        class="bg-landing-deep-teal font-serif text-white"
                    >
                        {{ initials(agent.name) }}
                    </AvatarFallback>
                </Avatar>
                <div>
                    <p class="font-serif font-semibold text-white">
                        {{ agent.name }}
                    </p>
                    <div
                        v-if="agent.specializations.length"
                        class="mt-1 flex flex-wrap gap-1"
                    >
                        <Badge
                            v-for="spec in agent.specializations.slice(0, 2)"
                            :key="spec"
                            class="border-0 bg-landing-gold/20 px-1.5 py-0 font-body text-2xs text-landing-gold"
                        >
                            {{ spec }}
                        </Badge>
                    </div>
                </div>
            </div>
            <p
                v-if="agent.bio"
                class="mt-3 line-clamp-3 font-body text-sm leading-relaxed text-white/50"
            >
                {{ agent.bio }}
            </p>
        </CardContent>
    </Card>
</template>
