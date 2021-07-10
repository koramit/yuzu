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
                'soft-theme-light': '#FCECDD',
                'alt-theme-light': '#FFC288',
                'bitter-theme-light': '#9EDE73',
                'dark-theme-light': '#FEA82F',
                // 'thick-theme-light': '#C5D7BD', x
                'thick-theme-light': '#9FB8AD',
                // 'thick-theme-light': '#BECA5C',
                // 'thick-theme-light': '#00AF91',
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
};
