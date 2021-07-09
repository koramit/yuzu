module.exports = {
    purge: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    darkMode: false, // or 'media' or 'class'
    theme: {
        extend: {
            colors: {
                'soft-theme-light': '#e1f5fe',
                'alt-theme-light': '#b3e5fc',
                'bitter-theme-light': '#81d4fa',
                'thick-theme-light': '#afc2cb',
                'dark-theme-light': '#82b3c9',
            }
        },
    },
    variants: {
        extend: {
            backgroundColor: ['active', 'hover', 'disabled'],
            borderColor: ['hover', 'focus', 'disabled'],
            cursor: ['hover', 'focus', 'disabled'],
            opacity: ['disabled'],
        },
    },
    plugins: [],
}
