<script setup lang="ts">
import { Calendar, DollarSign, PawPrint } from 'lucide-vue-next';

defineProps<{
    deposit?: string | null;
    leaseLength?: number | null;
    availableFrom?: string | null;
    petsAllowed: boolean;
}>();
</script>

<template>
    <div>
        <h2 class="mb-4 font-serif text-xl font-semibold text-white">
            Rental Details
        </h2>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div v-if="deposit" class="rounded-lg border border-white/10 bg-white/5 p-4">
                <DollarSign class="mb-1 size-4 text-landing-gold/70" />
                <p class="font-body text-xs text-white/40">Deposit</p>
                <p class="font-body text-sm font-semibold text-white">{{ deposit }}</p>
            </div>
            <div v-if="leaseLength" class="rounded-lg border border-white/10 bg-white/5 p-4">
                <Calendar class="mb-1 size-4 text-landing-gold/70" />
                <p class="font-body text-xs text-white/40">Lease Length</p>
                <p class="font-body text-sm font-semibold text-white">{{ leaseLength }} months</p>
            </div>
            <div v-if="availableFrom" class="rounded-lg border border-white/10 bg-white/5 p-4">
                <Calendar class="mb-1 size-4 text-landing-gold/70" />
                <p class="font-body text-xs text-white/40">Available From</p>
                <p class="font-body text-sm font-semibold text-white">{{ availableFrom }}</p>
            </div>
            <div class="rounded-lg border border-white/10 bg-white/5 p-4">
                <PawPrint class="mb-1 size-4 text-landing-gold/70" />
                <p class="font-body text-xs text-white/40">Pets</p>
                <p class="font-body text-sm font-semibold text-white">{{ petsAllowed ? 'Allowed' : 'Not Allowed' }}</p>
            </div>
        </div>
    </div>
</template>
