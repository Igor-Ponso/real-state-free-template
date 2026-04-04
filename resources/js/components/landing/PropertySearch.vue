<script setup lang="ts">
import { Search } from 'lucide-vue-next';
import { motion } from 'motion-v';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
</script>

<template>
    <section class="bg-landing-charcoal px-6 py-24 text-white">
        <div class="mx-auto max-w-6xl">
            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :while-in-view="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.6 }"
                :viewport="{ once: true }"
                class="mb-12 text-center"
            >
                <h2 class="font-serif text-3xl font-semibold tracking-tight md:text-4xl">
                    Find Your Dream Property
                </h2>
                <p class="mx-auto mt-4 max-w-xl font-body text-white/60">
                    Search through our exclusive listings to find your perfect home
                </p>
            </motion.div>

            <motion.div
                :initial="{ opacity: 0, y: 20 }"
                :while-in-view="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.6, delay: 0.2 }"
                :viewport="{ once: true }"
                class="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 lg:grid-cols-6"
            >
                <div>
                    <label class="mb-2 block font-body text-xs font-medium text-white/70">Type</label>
                    <Select>
                        <SelectTrigger class="border-white/20 bg-white/10 text-white">
                            <SelectValue placeholder="All Types" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Types</SelectItem>
                            <SelectItem value="house">House</SelectItem>
                            <SelectItem value="apartment">Apartment</SelectItem>
                            <SelectItem value="penthouse">Penthouse</SelectItem>
                            <SelectItem value="villa">Villa</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div>
                    <label class="mb-2 block font-body text-xs font-medium text-white/70">Location</label>
                    <Input
                        placeholder="City or neighborhood..."
                        class="border-white/20 bg-white/10 text-white placeholder:text-white/40"
                    />
                </div>

                <div>
                    <label class="mb-2 block font-body text-xs font-medium text-white/70">Min Price</label>
                    <Input
                        type="number"
                        placeholder="$Min"
                        class="border-white/20 bg-white/10 text-white placeholder:text-white/40"
                    />
                </div>

                <div>
                    <label class="mb-2 block font-body text-xs font-medium text-white/70">Max Price</label>
                    <Input
                        type="number"
                        placeholder="$Max"
                        class="border-white/20 bg-white/10 text-white placeholder:text-white/40"
                    />
                </div>

                <div>
                    <label class="mb-2 block font-body text-xs font-medium text-white/70">Bedrooms</label>
                    <Select>
                        <SelectTrigger class="border-white/20 bg-white/10 text-white">
                            <SelectValue placeholder="Any" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="any">Any</SelectItem>
                            <SelectItem value="1">1+</SelectItem>
                            <SelectItem value="2">2+</SelectItem>
                            <SelectItem value="3">3+</SelectItem>
                            <SelectItem value="4">4+</SelectItem>
                            <SelectItem value="5">5+</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div>
                    <Button
                        size="default"
                        class="w-full bg-landing-gold font-body text-landing-gold-foreground hover:bg-landing-gold/90"
                    >
                        <Search class="mr-2 size-4" />
                        Search
                    </Button>
                </div>
            </motion.div>
        </div>
    </section>
</template>
