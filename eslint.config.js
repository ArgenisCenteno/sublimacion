import stylistic from '@stylistic/eslint-plugin';
import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript';
import prettier from 'eslint-config-prettier/flat';
import importPlugin from 'eslint-plugin-import';
import vue from 'eslint-plugin-vue';

const controlStatements = [
    'if',
    'return',
    'for',
    'while',
    'do',
    'switch',
    'try',
    'throw',
];
const paddingAroundControl = [
    ...controlStatements.flatMap((stmt) => [
        { blankLine: 'always', prev: '*', next: stmt },
        { blankLine: 'always', prev: stmt, next: '*' },
    ]),
];

export default defineConfigWithVueTs(
    vue.configs['flat/essential'],
    vueTsConfigs.recommended,
    {
        plugins: {
            import: importPlugin,
        },
        settings: {
            'import/resolver': {
                typescript: {
                    alwaysTryTypes: true,
                    project: './tsconfig.json',
                },
                node: true,
            },
        },
        rules: {
            'vue/multi-word-component-names': 'off',
            '@typescript-eslint/no-explicit-any': 'off',
            '@typescript-eslint/consistent-type-imports': [
                'error',
                {
                    prefer: 'type-imports',
                    fixStyle: 'separate-type-imports',
                },
            ],
            'import/order': [
                'error',
                {
                    groups: ['builtin', 'external', 'internal', 'parent', 'sibling', 'index'],
                    alphabetize: {
                        order: 'asc',
                        caseInsensitive: true,
                    },
                },
            ],
            'import/consistent-type-specifier-style': [
                'error',
                'prefer-top-level',
            ],
        },
    },
    {
        plugins: {
            '@stylistic': stylistic,
        },
        rules: {
            '@stylistic/padding-line-between-statements': [
                'error',
                ...paddingAroundControl,
            ],
        },
    },
    {
        ignores: [
            'vendor',
            'node_modules',
            'public',
            'bootstrap/ssr',
            '.vite/**',
            'tailwind.config.js',
            'vite.config.ts',
            'resources/js/actions/**',
            'resources/js/components/ui/*',
            'resources/js/routes/**',
            'resources/js/wayfinder/**',
            // Boilerplate del tema
            'resources/js/components/charts/**',
            'resources/js/components/ecommerce/**',
            'resources/js/components/forms/FormElements/Dropzone.vue',
            'resources/js/components/forms/FormElements/MultipleSelect.vue',
            'resources/js/components/forms/FormElements/SelectInput.vue',
            'resources/js/components/common/CountDown.vue',
            'resources/js/components/common/DropdownMenu.vue',
            'resources/js/components/common/ThemeToggler.vue',
            'resources/js/components/common/v-click-outside.vue',
            'resources/js/components/layout/AdminLayout.vue',
            'resources/js/components/layout/header/HeaderLogo.vue',
            'resources/js/components/layout/header/NotificationMenu.vue',
            'resources/js/components/layout/header/UserMenu.vue',
            'resources/js/components/profile/**',
            'resources/js/components/tables/**',
            'resources/js/pages/Chart/**',
            'resources/js/pages/Ecommerce.vue',
            'resources/js/pages/Errors/**',
            'resources/js/pages/Forms/**',
            'resources/js/pages/Others/**',
            'resources/js/pages/Pages/**',
            'resources/js/pages/Tables/**',
            'resources/js/pages/UiElements/**',
        ],
    },
    prettier, // Turn off all rules that might conflict with Prettier
    {
        plugins: {
            '@stylistic': stylistic,
        },
        rules: {
            curly: ['error', 'all'],
            '@stylistic/brace-style': ['error', '1tbs', { allowSingleLine: false }],
        },
    },
);
