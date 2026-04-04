<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Menu } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import ModeToggle from '@/components/ModeToggle.vue';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { useScrollHeader } from '@/composables/useScrollHeader';
import { dashboard, home, login, register } from '@/routes';

defineProps<{
    canRegister: boolean;
}>();

const page = usePage();
const name = computed(() => page.props.name as string);
const auth = computed(() => page.props.auth as { user: unknown });

const { isScrolled } = useScrollHeader();

const navItems = [
    { label: 'Home', href: '#hero' },
    { label: 'Properties', href: '#properties' },
    { label: 'About', href: '#about' },
    { label: 'Contact', href: '#contact' },
];

function scrollTo(href: string) {
    document.querySelector(href)?.scrollIntoView({ behavior: 'smooth' });
}
</script>

<template>
    <header
        class="fixed left-0 right-0 top-0 z-50 transition-all duration-300"
        :class="
            isScrolled
                ? 'border-b border-border/50 bg-background/80 shadow-sm backdrop-blur-md'
                : 'bg-transparent'
        "
    >
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6">
            <Link
                :href="home()"
                class="flex items-center gap-2 text-lg font-semibold"
                :class="isScrolled ? 'text-foreground' : 'text-white'"
            >
                <AppLogoIcon
                    class="size-7 fill-current"
                    :class="isScrolled ? 'text-foreground' : 'text-white'"
                />
                <span class="hidden font-serif sm:inline">{{ name }}</span>
            </Link>

            <nav class="hidden items-center gap-8 lg:flex">
                <button
                    v-for="item in navItems"
                    :key="item.href"
                    @click="scrollTo(item.href)"
                    class="font-body text-sm font-medium tracking-wide transition-colors hover:opacity-80"
                    :class="isScrolled ? 'text-foreground' : 'text-white/90'"
                >
                    {{ item.label }}
                </button>
            </nav>

            <div class="flex items-center gap-2">
                <ModeToggle />

                <template v-if="auth.user">
                    <Button
                        as-child
                        :variant="isScrolled ? 'default' : 'secondary'"
                        size="sm"
                    >
                        <Link :href="dashboard()">Dashboard</Link>
                    </Button>
                </template>
                <template v-else>
                    <Button
                        as-child
                        variant="ghost"
                        size="sm"
                        :class="isScrolled ? '' : 'text-white hover:bg-white/10'"
                    >
                        <Link :href="login()">Log in</Link>
                    </Button>
                    <Button
                        v-if="canRegister"
                        as-child
                        size="sm"
                        class="bg-landing-gold text-landing-gold-foreground hover:bg-landing-gold/90"
                    >
                        <Link :href="register()">Register</Link>
                    </Button>
                </template>

                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger as-child>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="h-9 w-9"
                                :class="isScrolled ? '' : 'text-white hover:bg-white/10'"
                            >
                                <Menu class="size-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="right" class="w-[280px]">
                            <SheetTitle class="sr-only">Navigation</SheetTitle>
                            <SheetHeader class="text-left">
                                <AppLogoIcon class="size-6 fill-current" />
                            </SheetHeader>
                            <nav class="mt-8 flex flex-col gap-4">
                                <button
                                    v-for="item in navItems"
                                    :key="item.href"
                                    @click="scrollTo(item.href)"
                                    class="font-body text-left text-sm font-medium"
                                >
                                    {{ item.label }}
                                </button>
                            </nav>
                        </SheetContent>
                    </Sheet>
                </div>
            </div>
        </div>
    </header>
</template>
