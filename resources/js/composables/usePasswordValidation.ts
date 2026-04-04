import { computed   } from 'vue';
import type {ComputedRef, Ref} from 'vue';

type PasswordRule = {
    key: string;
    label: string;
    passed: boolean;
};

type UsePasswordValidationOptions = {
    password: Ref<string> | ComputedRef<string>;
    minLength?: number;
};

type UsePasswordValidationReturn = {
    rules: ComputedRef<PasswordRule[]>;
    allPassed: ComputedRef<boolean>;
    passedCount: ComputedRef<number>;
    totalCount: ComputedRef<number>;
    hasInput: ComputedRef<boolean>;
};

export function usePasswordValidation({
    password,
    minLength = 12,
}: UsePasswordValidationOptions): UsePasswordValidationReturn {
    const rules = computed<PasswordRule[]>(() => {
        const value = password.value;

        return [
            {
                key: 'minLength',
                label: `At least ${minLength} characters`,
                passed: value.length >= minLength,
            },
            {
                key: 'uppercase',
                label: 'Contains uppercase letter',
                passed: /[A-Z]/.test(value),
            },
            {
                key: 'lowercase',
                label: 'Contains lowercase letter',
                passed: /[a-z]/.test(value),
            },
            {
                key: 'number',
                label: 'Contains a number',
                passed: /\d/.test(value),
            },
            {
                key: 'symbol',
                label: 'Contains a symbol',
                passed: /[^A-Za-z0-9]/.test(value),
            },
        ];
    });

    const allPassed = computed(() => rules.value.every((rule) => rule.passed));
    const passedCount = computed(
        () => rules.value.filter((rule) => rule.passed).length,
    );
    const totalCount = computed(() => rules.value.length);
    const hasInput = computed(() => password.value.length > 0);

    return {
        rules,
        allPassed,
        passedCount,
        totalCount,
        hasInput,
    };
}
