<script setup lang="ts">
import {
    CategoryScale,
    Chart as ChartJS,
    Filler,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Title,
    Tooltip,
} from 'chart.js';
import { computed } from 'vue';
import { Line } from 'vue-chartjs';

import type { DashboardChartData } from '@/types/admin';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
);

const props = defineProps<{
    chart: DashboardChartData;
    label?: string;
}>();

const chartData = computed(() => ({
    labels: props.chart.labels,
    datasets: [
        {
            label: props.label ?? 'Inquiries',
            data: props.chart.data,
            borderColor: 'hsl(38, 60%, 58%)',
            backgroundColor: 'hsl(38, 60%, 58%, 0.15)',
            tension: 0.35,
            fill: true,
            pointRadius: 0,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: 'hsl(38, 60%, 58%)',
            borderWidth: 2,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'hsl(220, 10%, 12%)',
            titleColor: 'hsl(38, 60%, 58%)',
            bodyColor: '#fff',
            borderColor: 'hsl(38, 60%, 58%, 0.3)',
            borderWidth: 1,
            padding: 10,
            displayColors: false,
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                precision: 0,
                color: 'hsl(220, 5%, 50%)',
                font: { size: 11 },
            },
            grid: { color: 'hsl(220, 5%, 90%, 0.5)' },
        },
        x: {
            ticks: {
                color: 'hsl(220, 5%, 50%)',
                font: { size: 10 },
                maxRotation: 0,
                autoSkip: true,
                maxTicksLimit: 8,
            },
            grid: { display: false },
        },
    },
    interaction: { mode: 'index' as const, intersect: false },
} as const;
</script>

<template>
    <div class="h-[240px] w-full">
        <Line :data="chartData" :options="chartOptions" />
    </div>
</template>
