<template>
    <div>
        <div class="flex fixed w-full top-0">
            <div
                class="w-1/4"
                id="header-left"
            >
                <slot name="header-left" />
            </div>
            <div
                class="w-3/4 text-right"
                id="header-right"
            >
                <slot name="header-right" />
            </div>
        </div>
        <slot />
        <div class="flex fixed w-full bottom-0">
            <div
                class="w-1/4"
                id="footer-left"
            >
                <slot name="footer-left" />
            </div>
            <div
                class="w-3/4 text-right"
                id="footer-right"
            >
                <slot name="footer-right" />
            </div>
        </div>
    </div>
</template>

<style>
    @media print {
        @page {
            /* margin: 0.5cm 1cm 0.5cm 1cm; */
            margin: 1cm;
            padding: 0;
        }

        body {
            margin: 0;
            width: 210mm;
            height: 296mm;
        }

        .print-p-0 {
            padding: 0;
        }

        .text-print-size {
            font-size: 10pt!important;
        }

        .avoid-page-break {
            display: block;
            page-break-inside: avoid;
        }

        .new-page {
            display: block;
            page-break-before: always;
        }

        label.form-label {
            font-size: 10pt!important;
            padding-bottom: .25rem;
            font-weight: 400;
        }
    }
</style>