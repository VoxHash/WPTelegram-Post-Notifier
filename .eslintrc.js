module.exports = {
    env: {
        browser: true,
        es2021: true,
        node: true,
    },
    extends: [
        '@wordpress/eslint-plugin/recommended',
        'prettier',
    ],
    parser: '@typescript-eslint/parser',
    parserOptions: {
        ecmaVersion: 'latest',
        sourceType: 'module',
    },
    plugins: ['@typescript-eslint'],
    rules: {
        '@wordpress/no-global-event-listener': 'off',
        '@wordpress/no-global-get-selection': 'off',
        '@typescript-eslint/no-unused-vars': 'error',
        'prettier/prettier': 'error',
    },
    settings: {
        'import/resolver': {
            typescript: {},
        },
    },
};
