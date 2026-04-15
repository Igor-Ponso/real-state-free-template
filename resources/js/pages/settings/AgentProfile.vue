<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Camera, ExternalLink, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { edit } from '@/routes/agent-profile';

interface AgentProfileProp {
    id: number;
    slug: string | null;
    bio: string | null;
    phone: string | null;
    license_number: string | null;
    specializations: string[];
    social_links: {
        linkedin?: string;
        instagram?: string;
        twitter?: string;
        facebook?: string;
    };
    photo_url: string | null;
    public_url: string | null;
}

const props = defineProps<{
    profile: AgentProfileProp;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Agent profile', href: edit() }],
    },
});

const form = useForm({
    bio: props.profile.bio ?? '',
    phone: props.profile.phone ?? '',
    license_number: props.profile.license_number ?? '',
    specializations: [...props.profile.specializations],
    social_links: {
        linkedin: props.profile.social_links?.linkedin ?? '',
        instagram: props.profile.social_links?.instagram ?? '',
        twitter: props.profile.social_links?.twitter ?? '',
        facebook: props.profile.social_links?.facebook ?? '',
    },
});

const specializationInput = ref('');

const addSpecialization = () => {
    const raw = specializationInput.value.trim();

    if (!raw || form.specializations.includes(raw)) {
        specializationInput.value = '';
        return;
    }

    if (form.specializations.length >= 10) {
        toast.error('Max 10 specializations.');
        return;
    }

    form.specializations.push(raw);
    specializationInput.value = '';
};

const removeSpecialization = (tag: string) => {
    form.specializations = form.specializations.filter((s) => s !== tag);
};

const submit = () => {
    form.patch('/settings/agent-profile', {
        preserveScroll: true,
        onSuccess: () => toast.success('Profile saved.'),
    });
};

// Photo upload — separate multipart request so form state isn't coupled to the file input.
const photoInput = ref<HTMLInputElement | null>(null);
const uploadingPhoto = ref(false);

const uploadPhoto = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    const data = new FormData();
    data.append('photo', file);

    uploadingPhoto.value = true;
    router.post('/settings/agent-profile/photo', data, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => toast.success('Photo updated.'),
        onError: (errors) => {
            const first = Object.values(errors)[0];
            toast.error(
                typeof first === 'string'
                    ? first
                    : 'Upload failed. Check file size and dimensions.',
            );
        },
        onFinish: () => {
            uploadingPhoto.value = false;

            if (photoInput.value) {
                photoInput.value.value = '';
            }
        },
    });
};

const removePhoto = () => {
    router.delete('/settings/agent-profile/photo', {
        preserveScroll: true,
        onSuccess: () => toast.success('Photo removed.'),
    });
};
</script>

<template>
    <Head title="Agent profile" />

    <h1 class="sr-only">Agent profile</h1>

    <div class="space-y-10">
        <Heading
            variant="small"
            title="Public agent profile"
            description="Shown on the Our Team section of the landing page and on your public /agents page."
        />

        <!-- Photo -->
        <section class="space-y-3">
            <Label>Profile photo</Label>
            <div class="flex items-center gap-6">
                <div
                    class="flex size-24 shrink-0 items-center justify-center overflow-hidden rounded-full border bg-muted"
                >
                    <img
                        v-if="profile.photo_url"
                        :src="profile.photo_url"
                        :alt="`Photo`"
                        class="size-full object-cover"
                    />
                    <Camera v-else class="size-8 text-muted-foreground/50" />
                </div>
                <div class="flex flex-col gap-2">
                    <div class="flex gap-2">
                        <Button
                            type="button"
                            size="sm"
                            :disabled="uploadingPhoto"
                            @click="photoInput?.click()"
                        >
                            {{
                                uploadingPhoto
                                    ? 'Uploading...'
                                    : profile.photo_url
                                      ? 'Change photo'
                                      : 'Upload photo'
                            }}
                        </Button>
                        <Button
                            v-if="profile.photo_url"
                            type="button"
                            size="sm"
                            variant="ghost"
                            class="text-destructive hover:text-destructive"
                            @click="removePhoto"
                        >
                            <Trash2 class="mr-1 size-4" /> Remove
                        </Button>
                    </div>
                    <p class="text-xs text-muted-foreground">
                        JPEG, PNG, or WebP · 200x200 min · 4MB max
                    </p>
                    <input
                        ref="photoInput"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="hidden"
                        @change="uploadPhoto"
                    />
                </div>
            </div>
        </section>

        <!-- Public URL -->
        <section
            v-if="profile.public_url"
            class="rounded-lg border bg-muted/30 p-4 text-sm"
        >
            <p class="flex items-center gap-2 text-muted-foreground">
                Public URL:
                <a
                    :href="profile.public_url"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-1 text-landing-gold hover:underline"
                >
                    {{ profile.public_url }}
                    <ExternalLink class="size-3.5" />
                </a>
            </p>
        </section>

        <!-- Main form -->
        <form class="space-y-6" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="bio">Bio</Label>
                <Textarea
                    id="bio"
                    v-model="form.bio"
                    rows="4"
                    maxlength="2000"
                    placeholder="Tell prospective clients about your experience and approach."
                />
                <p class="text-xs text-muted-foreground">
                    {{ form.bio.length }} / 2000
                </p>
                <InputError :message="form.errors.bio" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="phone">Phone</Label>
                    <Input
                        id="phone"
                        v-model="form.phone"
                        placeholder="+1 (604) 555-0123"
                    />
                    <InputError :message="form.errors.phone" />
                </div>
                <div class="grid gap-2">
                    <Label for="license">License number</Label>
                    <Input
                        id="license"
                        v-model="form.license_number"
                        placeholder="RE-1234-AB"
                    />
                    <InputError :message="form.errors.license_number" />
                </div>
            </div>

            <!-- Specializations -->
            <div class="grid gap-2">
                <Label for="specialization">Specializations</Label>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="tag in form.specializations"
                        :key="tag"
                        class="inline-flex items-center gap-1 rounded-full bg-landing-gold/10 px-3 py-1 text-xs font-medium text-landing-gold"
                    >
                        {{ tag }}
                        <button
                            type="button"
                            class="hover:text-landing-gold/70"
                            @click="removeSpecialization(tag)"
                        >
                            <X class="size-3" />
                        </button>
                    </span>
                </div>
                <div class="flex gap-2">
                    <Input
                        id="specialization"
                        v-model="specializationInput"
                        placeholder="e.g. Luxury waterfront"
                        maxlength="64"
                        @keydown.enter.prevent="addSpecialization"
                    />
                    <Button
                        type="button"
                        variant="secondary"
                        @click="addSpecialization"
                    >
                        Add
                    </Button>
                </div>
                <p class="text-xs text-muted-foreground">
                    Press Enter to add. Up to 10 tags.
                </p>
            </div>

            <!-- Social links -->
            <div class="space-y-3">
                <Label>Social links</Label>
                <div class="grid gap-3 sm:grid-cols-2">
                    <Input
                        v-model="form.social_links.linkedin"
                        placeholder="LinkedIn URL"
                    />
                    <Input
                        v-model="form.social_links.instagram"
                        placeholder="Instagram URL"
                    />
                    <Input
                        v-model="form.social_links.twitter"
                        placeholder="Twitter / X URL"
                    />
                    <Input
                        v-model="form.social_links.facebook"
                        placeholder="Facebook URL"
                    />
                </div>
                <InputError :message="form.errors['social_links.linkedin']" />
                <InputError :message="form.errors['social_links.instagram']" />
                <InputError :message="form.errors['social_links.twitter']" />
                <InputError :message="form.errors['social_links.facebook']" />
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="form.processing">
                    {{ form.processing ? 'Saving...' : 'Save changes' }}
                </Button>
                <Button
                    v-if="profile.public_url"
                    as-child
                    variant="outline"
                    type="button"
                >
                    <Link :href="profile.public_url" target="_blank">
                        View public profile
                    </Link>
                </Button>
            </div>
        </form>
    </div>
</template>
